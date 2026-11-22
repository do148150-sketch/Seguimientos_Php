<?php

use App\Http\Controllers\NotesController;
use Illuminate\Support\Facades\Route;

Route::get('/notes' , [NotesController::class, 'index'])->name('notes.index');

Route::post('/notes/store' , [NotesController::class, 'store'])->name('notes.store');
Route::put('/notes/update/{id}' , [NotesController::class, 'update'])->name('notes.update');
Route::delete('/notes/delete/{id}' , [NotesController::class, 'delete'])->name('notes.delete');
