<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index() {

        // Cada ejercicio es un modulo independiente dentro del mismo proyecto:
        // su propio controlador, sus propias rutas y su propia vista.
        $ejercicios = [
            [
                'n'           => 1,
                'titulo'      => 'Lista de Tareas',
                'descripcion' => 'Agregar, eliminar y marcar tareas como completadas.',
                'ruta'        => 'tasks.index',
                'icono'       => 'bi-check2-square',
            ],
            [
                'n'           => 2,
                'titulo'      => 'Calculadora de Propinas',
                'descripcion' => 'Calcula la propina según el total y el porcentaje.',
                'ruta'        => 'tips.index',
                'icono'       => 'bi-cash-coin',
            ],
            [
                'n'           => 3,
                'titulo'      => 'Generador de Contraseñas',
                'descripcion' => 'Contraseñas aleatorias con longitud y tipos ajustables.',
                'ruta'        => 'passwords.index',
                'icono'       => 'bi-shield-lock',
            ],
            [
                'n'           => 4,
                'titulo'      => 'Gestor de Gastos',
                'descripcion' => 'Registra gastos, categorízalos y ve el resumen mensual.',
                'ruta'        => 'expenses.index',
                'icono'       => 'bi-wallet2',
            ],
            [
                'n'           => 5,
                'titulo'      => 'Sistema de Reservas',
                'descripcion' => 'Reserva citas mostrando disponibilidad y confirmación.',
                'ruta'        => 'reservations.index',
                'icono'       => 'bi-calendar-check',
            ],
        ];

        return view('home/index', [ 'ejercicios' => $ejercicios ]);
    }
}
