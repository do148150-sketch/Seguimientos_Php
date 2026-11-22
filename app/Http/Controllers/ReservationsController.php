<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Service;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ReservationsController extends Controller
{
    /** Primera franja del dia (8:00 a.m.) */
    public const OPENING_HOUR = 8;

    /** La ultima franja empieza a las 16:00 y termina a las 17:00 */
    public const CLOSING_HOUR = 17;

    /**
     * Pantalla principal: elegir servicio y dia, ver la disponibilidad
     * y la lista de reservas hechas.
     */
    public function index(Request $request) {

        $services = Service::orderBy('name')->get();

        if ($services->isEmpty()) {

            // Sin catalogo no hay nada que reservar
            return view('reservations/index', [
                'services'     => $services,
                'service'      => null,
                'date'         => date('Y-m-d'),
                'slots'        => collect(),
                'closed'       => false,
                'reservations' => collect(),
            ]);
        }

        // Servicio seleccionado: el que venga por la URL o el primero del catalogo
        $service = $services->firstWhere('id', (int) $request->input('service_id')) ?? $services->first();

        // Dia seleccionado: el que venga por la URL o hoy
        $date = $request->input('date', date('Y-m-d'));

        if (!$this->isValidDate($date) || $date < date('Y-m-d')) {
            $date = date('Y-m-d');
        }

        $day = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();

        // Los domingos no se atiende
        $closed = $day->isSunday();

        $slots = $closed ? collect() : $this->buildSlots($service, $day);

        // Historial de reservas, lo mas reciente arriba
        $reservations = Reservation::with('service')
                                   ->orderByDesc('reserved_at')
                                   ->limit(30)
                                   ->get();

        return view('reservations/index', [
            'services'     => $services,
            'service'      => $service,
            'date'         => $date,
            'slots'        => $slots,
            'closed'       => $closed,
            'reservations' => $reservations,
        ]);
    }

    public function store(Request $request) {

        Validator::make($request->all(),
        [
            'service_id'     => 'required|exists:services,id',
            'date'           => 'required|date_format:Y-m-d|after_or_equal:today',
            'time'           => 'required|date_format:H:i',
            'customer_name'  => 'required|max:80',
            'customer_email' => 'required|email|max:120',
        ],
        [
            'service_id.required'     => 'El servicio es requerido',
            'service_id.exists'       => 'El servicio seleccionado no existe',
            'date.required'           => 'La fecha es requerida',
            'date.date_format'        => 'La fecha no es válida',
            'date.after_or_equal'     => 'No se pueden reservar días que ya pasaron',
            'time.required'           => 'Debes seleccionar una hora disponible',
            'time.date_format'        => 'La hora no es válida',
            'customer_name.required'  => 'El nombre es requerido',
            'customer_name.max'       => 'El nombre no puede ser mayor a :max caracteres',
            'customer_email.required' => 'El correo es requerido',
            'customer_email.email'    => 'El correo no tiene un formato válido',
        ])->validate();

        $volver = [ 'service_id' => $request->service_id, 'date' => $request->date ];

        try {

            $reservedAt = Carbon::createFromFormat('Y-m-d H:i', "$request->date $request->time");

            // --- Reglas de negocio del ejercicio ---

            if ($reservedAt->isSunday()) {

                Session::flash('message', [ 'content' => "Los domingos no hay atención.", 'type' => 'error' ]);
                return redirect()->route('reservations.index', $volver);
            }

            if ($reservedAt->hour < self::OPENING_HOUR || $reservedAt->hour >= self::CLOSING_HOUR) {

                Session::flash('message', [ 'content' => "La hora está fuera del horario de atención.", 'type' => 'error' ]);
                return redirect()->route('reservations.index', $volver);
            }

            if ($reservedAt->isPast()) {

                Session::flash('message', [ 'content' => "Esa hora ya pasó.", 'type' => 'error' ]);
                return redirect()->route('reservations.index', $volver);
            }

            // Que no se cuelen dos personas en la misma franja entre que se
            // pinto la pantalla y se envio el formulario
            if ($this->isTaken($request->service_id, $reservedAt)) {

                Session::flash('message', [ 'content' => "Esa franja acaba de ser reservada. Elige otra.", 'type' => 'error' ]);
                return redirect()->route('reservations.index', $volver);
            }

            $reservation = new Reservation();
            $reservation->service_id     = $request->service_id;
            $reservation->customer_name  = $request->customer_name;
            $reservation->customer_email = $request->customer_email;
            $reservation->reserved_at    = $reservedAt;
            $reservation->status         = 'pendiente';
            $reservation->save();

            Session::flash('message', [
                'content' => "Reserva creada para el " . $reservedAt->format('d/m/Y') . " a las " . $reservedAt->format('H:i') . ". Falta confirmarla.",
                'type'    => 'success',
            ]);

            return redirect()->route('reservations.index', $volver);

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function confirm($id) {

        try {

            $reservation = Reservation::find($id);

            if (empty($reservation)) {

                Session::flash('message', [ 'content' => "La reserva con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            if ($reservation->status !== 'pendiente') {

                Session::flash('message', [ 'content' => "Solo se puede confirmar una reserva pendiente.", 'type' => 'error' ]);
                return redirect()->back();
            }

            $reservation->status = 'confirmada';
            $reservation->save();

            Session::flash('message', [ 'content' => "Reserva confirmada con éxito", 'type' => 'success' ]);
            return redirect()->back();

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function cancel($id) {

        try {

            $reservation = Reservation::find($id);

            if (empty($reservation)) {

                Session::flash('message', [ 'content' => "La reserva con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            // Se borra el registro para que la franja vuelva a quedar libre:
            // la BD tiene un UNIQUE sobre (service_id, reserved_at)
            $reservation->delete();

            Session::flash('message', [ 'content' => "Reserva cancelada. La franja quedó libre de nuevo.", 'type' => 'success' ]);
            return redirect()->back();

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
     * Arma las franjas del dia marcando cuales estan libres y cuales no.
     * Una sola consulta: se traen las reservas del dia y se cruzan en memoria.
     */
    private function buildSlots(Service $service, Carbon $day) {

        $taken = Reservation::where('service_id', $service->id)
                            ->whereDate('reserved_at', $day->toDateString())
                            ->get()
                            ->keyBy(fn ($reservation) => $reservation->reserved_at->format('H:i'));

        $slots = collect();

        for ($hour = self::OPENING_HOUR; $hour < self::CLOSING_HOUR; $hour++) {

            $start = $day->copy()->setTime($hour, 0);
            $label = $start->format('H:i');

            $reservation = $taken->get($label);

            $slots->push([
                'time'        => $label,
                'ends'        => $start->copy()->addMinutes($service->duration_minutes)->format('H:i'),
                'past'        => $start->isPast(),
                'reservation' => $reservation,
                'available'   => !$start->isPast() && empty($reservation),
            ]);
        }

        return $slots;
    }

    private function isTaken($serviceId, Carbon $reservedAt): bool {

        return Reservation::where('service_id', $serviceId)
                          ->where('reserved_at', $reservedAt->format('Y-m-d H:i:s'))
                          ->exists();
    }

    private function isValidDate($date): bool {

        if (!is_string($date)) {
            return false;
        }

        $parsed = Carbon::createFromFormat('Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
