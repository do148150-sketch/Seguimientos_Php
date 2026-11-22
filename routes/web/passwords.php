<?php

use App\Http\Controllers\PasswordsController;
use Illuminate\Support\Facades\Route;

Route::get('/passwords' , [PasswordsController::class, 'index'])->name('passwords.index');

Route::post('/passwords/generate' , [PasswordsController::class, 'generate'])->name('passwords.generate');
