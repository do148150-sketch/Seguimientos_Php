<?php

use App\Http\Controllers\EventsController;
use Illuminate\Support\Facades\Route;

Route::get('/events' , [EventsController::class, 'index'])->name('events.index');

Route::post('/events/store' , [EventsController::class, 'store'])->name('events.store');
Route::put('/events/update/{id}' , [EventsController::class, 'update'])->name('events.update');
Route::delete('/events/delete/{id}' , [EventsController::class, 'delete'])->name('events.delete');
