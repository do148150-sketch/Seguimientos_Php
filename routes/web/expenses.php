<?php

use App\Http\Controllers\ExpensesController;
use Illuminate\Support\Facades\Route;

Route::get('/expenses' , [ExpensesController::class, 'index'])->name('expenses.index');

Route::post('/expenses/store' , [ExpensesController::class, 'store'])->name('expenses.store');
Route::delete('/expenses/delete/{id}' , [ExpensesController::class, 'delete'])->name('expenses.delete');
