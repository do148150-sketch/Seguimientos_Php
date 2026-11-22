<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class TasksController extends Controller
{
    public function index() {

        // Primero las pendientes, y dentro de cada grupo la mas reciente arriba
        $tasks = Task::orderBy('is_completed')
                     ->orderByDesc('created_at')
                     ->get();

        $pending = $tasks->where('is_completed', false)->count();

        return view('tasks/index', [ 'tasks' => $tasks, 'pending' => $pending ]);
    }

    public function store(Request $request) {

        Validator::make($request->all(),
        [
            'title' => 'required|max:120'
        ],
        [
            'title.required' => 'La tarea es requerida',
            'title.max' => 'La tarea no puede ser mayor a :max caracteres'
        ])->validate();

        try {

            $task = new Task();
            $task->title = $request->title;
            $task->is_completed = false;
            $task->save();

            Session::flash('message', [ 'content' => "Tarea agregada con éxito", 'type' => 'success' ]);
            return redirect()->route('tasks.index');

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function toggle($id) {

        try {

            $task = Task::find($id);

            if (empty($task)) {

                Session::flash('message', [ 'content' => "La tarea con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            // Invierte el estado: completada <-> pendiente
            $task->is_completed = !$task->is_completed;
            $task->save();

            $texto = $task->is_completed ? "Tarea completada" : "Tarea marcada como pendiente";

            Session::flash('message', [ 'content' => $texto, 'type' => 'success' ]);
            return redirect()->route('tasks.index');

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function delete($id) {

        try {

            $task = Task::find($id);

            if (empty($task)) {

                Session::flash('message', [ 'content' => "La tarea con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            $task->delete();

            Session::flash('message', [ 'content' => "Tarea eliminada con éxito", 'type' => 'success' ]);
            return redirect()->route('tasks.index');

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }
}
