<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::patch('usuarios/{usuario}/estado', [UsuarioController::class, 'toggleEstado']);
    Route::apiResource('usuarios', UsuarioController::class);
});
