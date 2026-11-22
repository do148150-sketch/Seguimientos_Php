<?php

use App\Http\Controllers\SurveysController;
use Illuminate\Support\Facades\Route;

Route::get('/surveys' , [SurveysController::class, 'index'])->name('surveys.index');
Route::get('/surveys/{id}' , [SurveysController::class, 'show'])->name('surveys.show');

Route::post('/surveys/store' , [SurveysController::class, 'store'])->name('surveys.store');
Route::post('/surveys/vote/{id}' , [SurveysController::class, 'vote'])->name('surveys.vote');
Route::put('/surveys/toggle/{id}' , [SurveysController::class, 'toggle'])->name('surveys.toggle');
Route::delete('/surveys/delete/{id}' , [SurveysController::class, 'delete'])->name('surveys.delete');
