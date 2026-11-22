<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PasswordsController extends Controller
{
    // Sin caracteres ambiguos: ni l ni I ni 1, ni 0 ni O
    private const LOWER   = 'abcdefghijkmnopqrstuvwxyz';
    private const UPPER   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    private const DIGITS  = '23456789';
    private const SYMBOLS = '!@#$%&*?-_+=';

    public function index() {

        return view('passwords/index', [ 'password' => null, 'fuerza' => null ]);
    }

    public function generate(Request $request) {

        Validator::make($request->all(),
        [
            'length' => 'required|integer|min:4|max:64'
        ],
        [
            'length.required' => 'La longitud es requerida',
            'length.integer' => 'La longitud debe ser un número entero',
            'length.min' => 'La longitud mínima es :min',
            'length.max' => 'La longitud máxima es :max'
        ])->validate();

        $length = (int) $request->length;

        // Cada tipo marcado aporta su alfabeto
        $grupos = [];
        if ($request->boolean('lower'))   $grupos[] = self::LOWER;
        if ($request->boolean('upper'))   $grupos[] = self::UPPER;
        if ($request->boolean('digits'))  $grupos[] = self::DIGITS;
        if ($request->boolean('symbols')) $grupos[] = self::SYMBOLS;

        if (empty($grupos)) {

            return redirect()->back()
                             ->withInput()
                             ->withErrors(['tipos' => 'Debe seleccionar al menos un tipo de carácter']);
        }

        $password = $this->build($grupos, $length);

        return view('passwords/index', [
            'password' => $password,
            'fuerza' => $this->strength($password, $grupos),
            'opciones' => $request->all()
        ]);
    }

    /**
     * Garantiza al menos un caracter de cada grupo elegido y despues
     * completa al azar. random_int es criptograficamente seguro, rand no.
     */
    private function build(array $grupos, int $length): string {

        $caracteres = [];

        foreach ($grupos as $grupo) {

            $caracteres[] = $grupo[random_int(0, strlen($grupo) - 1)];
        }

        $todos = implode('', $grupos);

        while (count($caracteres) < $length) {

            $caracteres[] = $todos[random_int(0, strlen($todos) - 1)];
        }

        // Mezclar para que los primeros caracteres no sigan el orden de los grupos
        for ($i = count($caracteres) - 1; $i > 0; $i--) {

            $j = random_int(0, $i);
            [$caracteres[$i], $caracteres[$j]] = [$caracteres[$j], $caracteres[$i]];
        }

        return implode('', $caracteres);
    }

    private function strength(string $password, array $grupos): array {

        $alfabeto = strlen(implode('', $grupos));

        // Entropia en bits: longitud x log2(tamaño del alfabeto)
        $bits = strlen($password) * log($alfabeto, 2);

        if ($bits < 40)  return ['texto' => 'Débil',      'clase' => 'danger',  'porcentaje' => 25,  'bits' => $bits];
        if ($bits < 60)  return ['texto' => 'Aceptable',  'clase' => 'warning', 'porcentaje' => 50,  'bits' => $bits];
        if ($bits < 80)  return ['texto' => 'Fuerte',     'clase' => 'info',    'porcentaje' => 75,  'bits' => $bits];

        return ['texto' => 'Muy fuerte', 'clase' => 'success', 'porcentaje' => 100, 'bits' => $bits];
    }
}
