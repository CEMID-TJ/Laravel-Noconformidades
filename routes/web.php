<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoConformidadController;

// Vista principal
Route::get('/', [NoConformidadController::class, 'index']);

// Endpoints JSON que reemplazarán tus antiguos llamados Ajax
Route::get('/noconformidades/procesos/{codGestion}', [NoConformidadController::class, 'getProcesos']);
Route::get('/noconformidades/show/{id}', [NoConformidadController::class, 'show']);
Route::get('/noconformidades/edit/{id}', [NoConformidadController::class, 'edit']);
Route::post('/noconformidades/store', [NoConformidadController::class, 'store']);
Route::get('/noconformidades/seguimientos/{id}', [NoConformidadController::class, 'getSeguimientos']);
Route::post('/noconformidades/seguimientos', [NoConformidadController::class, 'storeSeguimientos']);
Route::post('/noconformidades/cerrar', [NoConformidadController::class, 'cerrar']);
Route::post('/noconformidades/reversar', [NoConformidadController::class, 'reversar']);