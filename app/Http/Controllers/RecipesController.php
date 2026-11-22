<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class RecipesController extends Controller
{
    /**
     * Tipos de comida por los que se puede filtrar.
     */
    public const MEAL_TYPES = [
        'Desayuno',
        'Almuerzo',
        'Cena',
        'Postre',
        'Snack',
        'Bebida',
    ];

    public function index(Request $request) {

        $search = trim((string) $request->input('search'));
        $type   = $request->input('type');
        $only   = $request->input('only');   // 'favorites' o nada

        if (!in_array($type, self::MEAL_TYPES, true)) {
            $type = null;
        }

        $query = Recipe::query();

        // Se busca tanto por nombre como por ingrediente, que es lo que pide el enunciado
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('ingredients', 'like', "%$search%");
            });
        }

        if ($type !== null) {
            $query->where('meal_type', $type);
        }

        if ($only === 'favorites') {
            $query->where('is_favorite', true);
        }

        // Las guardadas primero, y dentro de cada grupo la mas reciente arriba
        $recipes = $query->orderByDesc('is_favorite')
                         ->orderByDesc('id')
                         ->get();

        $counts = Recipe::all()->groupBy('meal_type')->map->count();

        return view('recipes/index', [
            'recipes'   => $recipes,
            'counts'    => $counts,
            'search'    => $search,
            'type'      => $type,
            'only'      => $only,
            'mealTypes' => self::MEAL_TYPES,
            'favorites' => Recipe::where('is_favorite', true)->count(),
        ]);
    }

    /**
     * Ficha completa de la receta. Es la URL que se comparte.
     */
    public function show($id) {

        $recipe = Recipe::find($id);

        if (empty($recipe)) {

            Session::flash('message', [ 'content' => "La receta con id '$id' no existe.", 'type' => 'error' ]);
            return redirect()->route('recipes.index');
        }

        return view('recipes/show', [ 'recipe' => $recipe ]);
    }

    public function store(Request $request) {

        Validator::make($request->all(),
        [
            'title'        => 'required|max:120',
            'meal_type'    => 'required|in:' . implode(',', self::MEAL_TYPES),
            'minutes'      => 'required|integer|min:1|max:1440',
            'ingredients'  => 'required|max:2000',
            'instructions' => 'required|max:5000',
        ],
        [
            'title.required'        => 'El nombre de la receta es requerido',
            'title.max'             => 'El nombre no puede ser mayor a :max caracteres',
            'meal_type.required'    => 'El tipo de comida es requerido',
            'meal_type.in'          => 'El tipo de comida seleccionado no es válido',
            'minutes.required'      => 'El tiempo de preparación es requerido',
            'minutes.integer'       => 'El tiempo de preparación debe ser un número de minutos',
            'minutes.min'           => 'El tiempo de preparación debe ser mayor que cero',
            'minutes.max'           => 'El tiempo de preparación es demasiado grande',
            'ingredients.required'  => 'Los ingredientes son requeridos',
            'ingredients.max'       => 'Los ingredientes no pueden ser mayores a :max caracteres',
            'instructions.required' => 'La preparación es requerida',
            'instructions.max'      => 'La preparación no puede ser mayor a :max caracteres',
        ])->validate();

        try {

            $recipe = new Recipe();
            $recipe->title        = $request->title;
            $recipe->meal_type    = $request->meal_type;
            $recipe->minutes      = $request->minutes;
            $recipe->ingredients  = $request->ingredients;
            $recipe->instructions = $request->instructions;
            $recipe->is_favorite  = false;
            $recipe->save();

            Session::flash('message', [ 'content' => "Receta publicada con éxito", 'type' => 'success' ]);
            return redirect()->route('recipes.show', $recipe->id);

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    /**
     * Guardar o quitar de guardadas. Invierte el estado, como el toggle de tareas.
     */
    public function favorite($id) {

        try {

            $recipe = Recipe::find($id);

            if (empty($recipe)) {

                Session::flash('message', [ 'content' => "La receta con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            $recipe->is_favorite = !$recipe->is_favorite;
            $recipe->save();

            $texto = $recipe->is_favorite
                   ? "Receta guardada en tus favoritas"
                   : "Receta quitada de tus favoritas";

            Session::flash('message', [ 'content' => $texto, 'type' => 'success' ]);
            return redirect()->back();

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    public function delete($id) {

        try {

            $recipe = Recipe::find($id);

            if (empty($recipe)) {

                Session::flash('message', [ 'content' => "La receta con id '$id' no existe.", 'type' => 'error' ]);
                return redirect()->back();
            }

            $recipe->delete();

            Session::flash('message', [ 'content' => "Receta eliminada con éxito", 'type' => 'success' ]);
            return redirect()->route('recipes.index');

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }
}
