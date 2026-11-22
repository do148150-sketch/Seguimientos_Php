<?php

use App\Http\Controllers\ReservationsController;
use Illuminate\Support\Facades\Route;

Route::get('/reservations' , [ReservationsController::class, 'index'])->name('reservations.index');

Route::post('/reservations/store' , [ReservationsController::class, 'store'])->name('reservations.store');
Route::put('/reservations/confirm/{id}' , [ReservationsController::class, 'confirm'])->name('reservations.confirm');
Route::delete('/reservations/cancel/{id}' , [ReservationsController::class, 'cancel'])->name('reservations.cancel');
