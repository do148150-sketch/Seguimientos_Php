<?php

use App\Http\Controllers\MemoryController;
use Illuminate\Support\Facades\Route;

Route::get('/memory' , [MemoryController::class, 'index'])->name('memory.index');

Route::post('/memory/store' , [MemoryController::class, 'store'])->name('memory.store');
