<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipsController extends Controller
{
    public function index() {

        return view('tips/index', [ 'resultado' => null ]);
    }

    public function calculate(Request $request) {

        Validator::make($request->all(),
        [
            'amount' => 'required|numeric|min:0.01',
            'percentage' => 'required|numeric|min:0|max:100',
            'people' => 'required|integer|min:1|max:100'
        ],
        [
            'amount.required' => 'El monto de la cuenta es requerido',
            'amount.numeric' => 'El monto debe ser un número',
            'amount.min' => 'El monto debe ser mayor que cero',

            'percentage.required' => 'El porcentaje es requerido',
            'percentage.numeric' => 'El porcentaje debe ser un número',
            'percentage.max' => 'El porcentaje no puede ser mayor a :max',

            'people.required' => 'La cantidad de personas es requerida',
            'people.integer' => 'La cantidad de personas debe ser un número entero',
            'people.min' => 'Debe haber al menos :min persona'
        ])->validate();

        $amount = (float) $request->amount;
        $percentage = (float) $request->percentage;
        $people = (int) $request->people;

        // El calculo vive en el controlador: la vista solo muestra el resultado
        $tip = $amount * ($percentage / 100);
        $total = $amount + $tip;

        $resultado = [
            'amount' => $amount,
            'percentage' => $percentage,
            'people' => $people,
            'tip' => $tip,
            'total' => $total,
            'perPerson' => $total / $people,
            'tipPerPerson' => $tip / $people,
        ];

        return view('tips/index', [ 'resultado' => $resultado ]);
    }
}
