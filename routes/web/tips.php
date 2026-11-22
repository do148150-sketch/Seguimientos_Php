<?php

use App\Http\Controllers\TipsController;
use Illuminate\Support\Facades\Route;

Route::get('/tips' , [TipsController::class, 'index'])->name('tips.index');

Route::post('/tips/calculate' , [TipsController::class, 'calculate'])->name('tips.calculate');
