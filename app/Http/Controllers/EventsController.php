<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class EventsController extends Controller
{
    /**
     * Opciones de recordatorio, en minutos de antelacion.
     * La clave vacia significa "sin recordatorio".
     */
    public const REMINDERS = [
        ''     => 'Sin recordatorio',
        '15'   => '15 minutos antes',
        '60'   => '1 hora antes',
        '1440' => '1 día antes',
        '10080'=> '1 semana antes',
    ];

    public function index(Request $request) {

        // Mes que se esta viendo. Por defecto el actual.
        $month = $request->input('month', date('Y-m'));

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = date('Y-m');
        }

        $firstDay = Carbon::createFromFormat('Y-m-d', "$month-01")->startOfDay();

        // Todos los eventos del mes, agrupados por dia para pintarlos en la rejilla
        $events = Event::whereBetween('starts_at', [
                            $firstDay->copy()->startOfMonth(),
                            $firstDay->copy()->endOfMonth(),
                        ])
                       ->orderBy('starts_at')
                       ->get();

        $eventsByDay = $events->groupBy(fn ($event) => $event->starts_at->format('Y-m-d'));

        // Proximos eventos: los que aun no ocurren, con su recordatorio
        $upcoming = Event::where('starts_at', '>=', now())
                         ->orderBy('starts_at')
                         ->limit(5)
                         ->get();

        // Si viene ?edit=5 el formulario se abre con ese evento cargado
        $editing = $request->filled('edit')
                 ? Event::find($request->input('edit'))
                 : null;

        return view('events/index', [
            'month'       => $month,
            'firstDay'    => $firstDay,
            'weeks'       => $this->buildCalendar($firstDay),
            'eventsByDay' => $eventsByDay,
            'upcoming'    => $upcoming,
            'editing'     => $editing,
            'reminders'   => self::REMINDERS,
            // El dia que se preselecciona al crear: el que venga por la URL u hoy
            'pickedDay'   => $request->input('day', date('Y-m-d')),
        ]);
    }

    public function store(Request $request) {

        $this->validateEvent($request);

        try {

            $event = new Event();
            $this->fill($event, $request);
            $event->save();

            Session::flash('message', [ 'content' => "Evento agregado con éxito", 'type' => 'success' ]);
            return redirect()->route('events.index', [ 'month' => substr($request->starts_at, 0, 7) ]);

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function update(Request $request, $id) {

        $this->validateEvent($request);

        try {

            $event = Event::find($id);

            if (empty($event)) {

                Session::flash('message', [ 'content' => "El evento con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            $this->fill($event, $request);
            $event->save();

            Session::flash('message', [ 'content' => "Evento actualizado con éxito", 'type' => 'success' ]);
            return redirect()->route('events.index', [ 'month' => substr($request->starts_at, 0, 7) ]);

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function delete($id) {

        try {

            $event = Event::find($id);

            if (empty($event)) {

                Session::flash('message', [ 'content' => "El evento con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            $month = $event->starts_at->format('Y-m');
            $event->delete();

            Session::flash('message', [ 'content' => "Evento eliminado con éxito", 'type' => 'success' ]);
            return redirect()->route('events.index', [ 'month' => $month ]);

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    // ---------------------------------------------------------------
    // Ayudas privadas
    // ---------------------------------------------------------------

    /**
     * Arma la rejilla del mes: semanas completas de lunes a domingo,
     * rellenando con los dias vecinos para que no queden huecos.
     */
    private function buildCalendar(Carbon $firstDay) {

        $start = $firstDay->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end   = $firstDay->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $weeks  = collect();
        $week   = collect();
        $cursor = $start->copy();

        while ($cursor <= $end) {

            $week->push($cursor->copy());

            if ($week->count() === 7) {
                $weeks->push($week);
                $week = collect();
            }

            $cursor->addDay();
        }

        return $weeks;
    }

    private function fill(Event $event, Request $request): void {

        $event->title       = $request->title;
        $event->description = $request->description;
        $event->starts_at   = Carbon::createFromFormat('Y-m-d H:i', "$request->starts_at $request->starts_time");

        // El select manda '' cuando no se quiere recordatorio
        $event->reminder_minutes = $request->filled('reminder_minutes')
                                 ? (int) $request->reminder_minutes
                                 : null;
    }

    private function validateEvent(Request $request): void {

        Validator::make($request->all(),
        [
            'title'            => 'required|max:120',
            'description'      => 'nullable|max:255',
            'starts_at'        => 'required|date_format:Y-m-d',
            'starts_time'      => 'required|date_format:H:i',
            'reminder_minutes' => 'nullable|in:' . implode(',', array_keys(self::REMINDERS)),
        ],
        [
            'title.required'          => 'El título del evento es requerido',
            'title.max'               => 'El título no puede ser mayor a :max caracteres',
            'description.max'         => 'La descripción no puede ser mayor a :max caracteres',
            'starts_at.required'      => 'La fecha del evento es requerida',
            'starts_at.date_format'   => 'La fecha del evento no es válida',
            'starts_time.required'    => 'La hora del evento es requerida',
            'starts_time.date_format' => 'La hora del evento no es válida',
            'reminder_minutes.in'     => 'El recordatorio seleccionado no es válido',
        ])->validate();
    }
}
