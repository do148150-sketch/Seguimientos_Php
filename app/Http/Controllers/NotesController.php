<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class NotesController extends Controller
{
    /**
     * Categorias fijas del ejercicio. Estan aqui y no en la vista para que
     * el formulario y la validacion lean siempre la misma lista.
     */
    public const CATEGORIES = [
        'Personal',
        'Estudio',
        'Trabajo',
        'Ideas',
        'Otros',
    ];

    public function index(Request $request) {

        $search   = trim((string) $request->input('search'));
        $category = $request->input('category');

        // Solo se acepta una categoria de la lista; cualquier otra cosa se ignora
        if (!in_array($category, self::CATEGORIES, true)) {
            $category = null;
        }

        $query = Note::query();

        // Buscar "facilmente": el texto se busca en el titulo y en el cuerpo
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('content', 'like', "%$search%");
            });
        }

        if ($category !== null) {
            $query->where('category', $category);
        }

        $notes = $query->orderByDesc('updated_at')->get();

        // Cuantas notas hay en cada categoria, para las pestañas de arriba
        $counts = Note::all()->groupBy('category')->map->count();

        // Si viene ?edit=5 el formulario se abre con esa nota cargada
        $editing = $request->filled('edit')
                 ? Note::find($request->input('edit'))
                 : null;

        return view('notes/index', [
            'notes'      => $notes,
            'counts'     => $counts,
            'search'     => $search,
            'category'   => $category,
            'editing'    => $editing,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request) {

        $this->validateNote($request);

        try {

            $note = new Note();
            $note->title    = $request->title;
            $note->content  = $request->content;
            $note->category = $request->category;
            $note->save();

            Session::flash('message', [ 'content' => "Nota guardada con éxito", 'type' => 'success' ]);
            return redirect()->route('notes.index');

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function update(Request $request, $id) {

        $this->validateNote($request);

        try {

            $note = Note::find($id);

            if (empty($note)) {

                Session::flash('message', [ 'content' => "La nota con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            $note->title    = $request->title;
            $note->content  = $request->content;
            $note->category = $request->category;
            $note->save();

            Session::flash('message', [ 'content' => "Nota actualizada con éxito", 'type' => 'success' ]);
            return redirect()->route('notes.index');

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function delete($id) {

        try {

            $note = Note::find($id);

            if (empty($note)) {

                Session::flash('message', [ 'content' => "La nota con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            $note->delete();

            Session::flash('message', [ 'content' => "Nota eliminada con éxito", 'type' => 'success' ]);
            return redirect()->route('notes.index');

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    /**
     * Crear y editar validan lo mismo, asi que las reglas viven en un solo sitio.
     */
    private function validateNote(Request $request): void {

        Validator::make($request->all(),
        [
            'title'    => 'required|max:120',
            'content'  => 'required|max:5000',
            'category' => 'required|in:' . implode(',', self::CATEGORIES),
        ],
        [
            'title.required'    => 'El título de la nota es requerido',
            'title.max'         => 'El título no puede ser mayor a :max caracteres',
            'content.required'  => 'El contenido de la nota es requerido',
            'content.max'       => 'El contenido no puede ser mayor a :max caracteres',
            'category.required' => 'La categoría es requerida',
            'category.in'       => 'La categoría seleccionada no es válida',
        ])->validate();
    }
}
