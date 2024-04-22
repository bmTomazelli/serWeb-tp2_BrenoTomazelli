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

//Routes du TP2 ici : 
Route::get('/films', 'App\Http\Controllers\FilmController@index');
Route::post('/signin', 'App\Http\Controllers\AuthController@login')->middleware(['throttle:5,1']);
Route::post('/signup', 'App\Http\Controllers\AuthController@register')->middleware(['throttle:5,1']);
Route::post('/signout', 'App\Http\Controllers\AuthController@logout')->middleware('auth:sanctum');