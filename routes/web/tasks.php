<?php

use App\Http\Controllers\TasksController;
use Illuminate\Support\Facades\Route;

Route::get('/tasks' , [TasksController::class, 'index'])->name('tasks.index');

Route::post('/tasks/store' , [TasksController::class, 'store'])->name('tasks.store');
Route::put('/tasks/toggle/{id}' , [TasksController::class, 'toggle'])->name('tasks.toggle');
Route::delete('/tasks/delete/{id}' , [TasksController::class, 'delete'])->name('tasks.delete');
