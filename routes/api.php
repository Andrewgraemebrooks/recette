<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Recipes
Route::post('/recipe', [\App\Http\Controllers\RecipeController::class, 'store'])->name('recipe.store');

// Categories
Route::post('/category', [\App\Http\Controllers\CategoryController::class, 'store'])->name('category.store');

// Testing
Route::get('/test-csrf-token', [\App\Http\Controllers\TestController::class, 'index'])->name('csrf.index');
