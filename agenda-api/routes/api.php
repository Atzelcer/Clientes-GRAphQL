<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\LoginController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('personas', PersonaController::class)
    ->middleware(JwtMiddleware::class);

Route::post('login', [LoginController::class, 'login']);   
 
