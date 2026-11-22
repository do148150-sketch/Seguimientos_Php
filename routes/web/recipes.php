<?php

use App\Http\Controllers\RecipesController;
use Illuminate\Support\Facades\Route;

Route::get('/recipes' , [RecipesController::class, 'index'])->name('recipes.index');
Route::get('/recipes/{id}' , [RecipesController::class, 'show'])->name('recipes.show');

Route::post('/recipes/store' , [RecipesController::class, 'store'])->name('recipes.store');
Route::put('/recipes/favorite/{id}' , [RecipesController::class, 'favorite'])->name('recipes.favorite');
Route::delete('/recipes/delete/{id}' , [RecipesController::class, 'delete'])->name('recipes.delete');
