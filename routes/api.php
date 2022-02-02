<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\TestController;

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
Route::post('/recipe', [RecipeController::class, 'store'])->name('recipe.store');
Route::get('/recipe', [RecipeController::class, 'index'])->name('recipe.index');

// Categories
Route::post('/category', [CategoryController::class, 'store'])->name('category.store');

// Ingredients
Route::post('/ingredient', [IngredientController::class, 'store'])->name('ingredient.store');

// Testing
Route::get('/test-csrf-token', [TestController::class, 'index'])->name('csrf.index');
