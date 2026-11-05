<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Catálogos
    Route::get('departamentos', [CatalogoController::class, 'departamentos']);
    Route::get('municipios', [CatalogoController::class, 'municipios']);
    Route::get('areas-proceso', [CatalogoController::class, 'areasProceso']);

    // Clientes
    Route::patch('clientes/{cliente}/estado', [ClienteController::class, 'toggleEstado']);
    Route::apiResource('clientes', ClienteController::class);

    // Solo administradores
    Route::middleware('admin')->group(function () {
        Route::get('roles', [CatalogoController::class, 'roles']);
        Route::patch('usuarios/{usuario}/estado', [UsuarioController::class, 'toggleEstado']);
        Route::apiResource('usuarios', UsuarioController::class);
    });
});
