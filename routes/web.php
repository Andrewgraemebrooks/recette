<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/recipe', [\App\Http\Controllers\RecipeController::class, 'store']);
Route::get('/test-csrf-token', [\App\Http\Controllers\TestController::class, 'index']);
