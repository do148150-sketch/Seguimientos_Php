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
            [
                'n'           => 6,
                'titulo'      => 'Gestor de Notas',
                'descripcion' => 'Notas organizadas por categoría y con buscador.',
                'ruta'        => 'notes.index',
                'icono'       => 'bi-journal-text',
            ],
            [
                'n'           => 7,
                'titulo'      => 'Calendario de Eventos',
                'descripcion' => 'Agrega, edita y elimina eventos, con recordatorios.',
                'ruta'        => 'events.index',
                'icono'       => 'bi-calendar3',
            ],
            [
                'n'           => 8,
                'titulo'      => 'Plataforma de Recetas',
                'descripcion' => 'Busca y guarda recetas filtrando por tipo e ingredientes.',
                'ruta'        => 'recipes.index',
                'icono'       => 'bi-egg-fried',
            ],
            [
                'n'           => 9,
                'titulo'      => 'Juego de Memoria',
                'descripcion' => 'Empareja cartas con distintos niveles y puntajes.',
                'ruta'        => 'memory.index',
                'icono'       => 'bi-grid-3x3-gap',
            ],
            [
                'n'           => 10,
                'titulo'      => 'Plataforma de Encuestas',
                'descripcion' => 'Crea y responde encuestas, y analiza los resultados.',
                'ruta'        => 'surveys.index',
                'icono'       => 'bi-bar-chart',
            ],
        ];

        return view('home/index', [ 'ejercicios' => $ejercicios ]);
    }
}
