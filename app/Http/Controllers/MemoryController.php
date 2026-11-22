<?php

namespace App\Http\Controllers;

use App\Models\MemoryScore;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class MemoryController extends Controller
{
    /**
     * Niveles de dificultad: cuantas parejas tiene el tablero
     * y en cuantas columnas se reparten.
     */
    public const LEVELS = [
        'Fácil'   => ['pairs' => 6,  'columns' => 4],
        'Medio'   => ['pairs' => 8,  'columns' => 4],
        'Difícil' => ['pairs' => 12, 'columns' => 6],
    ];

    /**
     * Las caras de las cartas. Se barajan en el servidor, no en el navegador,
     * para que cada partida reparta distinto.
     */
    public const FACES = [
        '⚽', '🏀', '🎸', '🚀', '🍕', '🐙',
        '🌵', '🎩', '🦊', '🍉', '⛵', '🔥',
    ];

    public function index(Request $request) {

        $difficulty = $request->input('difficulty', 'Fácil');

        if (!array_key_exists($difficulty, self::LEVELS)) {
            $difficulty = 'Fácil';
        }

        $level = self::LEVELS[$difficulty];

        // Se toman tantas caras como parejas, se duplican y se barajan
        $faces = array_slice(self::FACES, 0, $level['pairs']);
        $deck  = array_merge($faces, $faces);
        shuffle($deck);

        // Ranking del nivel que se esta viendo: mas puntos arriba
        $ranking = MemoryScore::where('difficulty', $difficulty)
                              ->orderByDesc('points')
                              ->orderBy('seconds')
                              ->limit(10)
                              ->get();

        return view('memory/index', [
            'difficulty' => $difficulty,
            'level'      => $level,
            'deck'       => $deck,
            'ranking'    => $ranking,
            'levels'     => self::LEVELS,
        ]);
    }

    public function store(Request $request) {

        $difficulty = $request->input('difficulty');
        $pairs      = self::LEVELS[$difficulty]['pairs'] ?? 0;

        Validator::make($request->all(),
        [
            'player_name' => 'required|max:60',
            'difficulty'  => 'required|in:' . implode(',', array_keys(self::LEVELS)),
            // No se puede ganar con menos jugadas que parejas hay
            'moves'       => "required|integer|min:$pairs|max:9999",
            'seconds'     => 'required|integer|min:1|max:9999',
        ],
        [
            'player_name.required' => 'El nombre del jugador es requerido',
            'player_name.max'      => 'El nombre no puede ser mayor a :max caracteres',
            'difficulty.required'  => 'La dificultad es requerida',
            'difficulty.in'        => 'La dificultad seleccionada no es válida',
            'moves.required'       => 'Faltan las jugadas de la partida',
            'moves.integer'        => 'Las jugadas deben ser un número',
            'moves.min'            => 'La partida reporta menos jugadas de las posibles',
            'seconds.required'     => 'Falta el tiempo de la partida',
            'seconds.integer'      => 'El tiempo debe ser un número',
        ])->validate();

        try {

            $score = new MemoryScore();
            $score->player_name = $request->player_name;
            $score->difficulty  = $difficulty;
            $score->pairs       = $pairs;
            $score->moves       = $request->moves;
            $score->seconds     = $request->seconds;

            // El puntaje lo calcula el servidor, no el navegador
            $score->points = $this->calculatePoints($pairs, (int) $request->moves, (int) $request->seconds);
            $score->save();

            Session::flash('message', [
                'content' => "¡Partida guardada! Obtuviste $score->points puntos.",
                'type'    => 'success',
            ]);

            return redirect()->route('memory.index', [ 'difficulty' => $difficulty ]);

        } catch(Exception $ex) {

            Log::error($ex);
            Session::flash('message', [ 'content' => "Ha ocurrido un error.", 'type' => 'error' ]);
            return redirect()->back();
        }
    }

    /**
     * Se parte de un puntaje base por nivel y se descuenta por cada jugada
     * de mas y por cada segundo. Nunca baja de cero.
     */
    private function calculatePoints(int $pairs, int $moves, int $seconds): int {

        $base      = $pairs * 100;
        $extraMoves = max(0, $moves - $pairs);   // las jugadas perfectas no descuentan

        return max(0, $base - ($extraMoves * 5) - ($seconds * 2));
    }
}
