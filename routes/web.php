<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home.index');

// Un archivo de rutas por ejercicio
include('web/tasks.php');         //  1 · Lista de Tareas
include('web/tips.php');          //  2 · Calculadora de Propinas
include('web/passwords.php');     //  3 · Generador de Contraseñas
include('web/expenses.php');      //  4 · Gestor de Gastos
include('web/reservations.php');  //  5 · Sistema de Reservas
include('web/notes.php');         //  6 · Gestor de Notas
include('web/events.php');        //  7 · Calendario de Eventos
include('web/recipes.php');       //  8 · Plataforma de Recetas
include('web/memory.php');        //  9 · Juego de Memoria
include('web/surveys.php');       // 10 · Plataforma de Encuestas
