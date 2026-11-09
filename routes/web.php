<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoConformidadController;

// Vista principal y filtrado de la tabla
Route::get('/', [NoConformidadController::class, 'index'])->name('noconformidades.index');
Route::get('/noconformidades', [NoConformidadController::class, 'index']);

// Rutas AJAX (Equivalentes a los bloques if/elseif de TipoModificar en tu script original)
Route::get('/noconformidades/mostrar', [NoConformidadController::class, 'mostrar']);
Route::get('/noconformidades/editar', [NoConformidadController::class, 'editar']);
Route::post('/noconformidades/grabar', [NoConformidadController::class, 'grabar']);

Route::get('/noconformidades/seguimientos', [NoConformidadController::class, 'obtenerSeguimientos']);
Route::post('/noconformidades/seguimientos', [NoConformidadController::class, 'grabarSeguimientos']);

Route::get('/noconformidades/cierre', [NoConformidadController::class, 'obtenerCierre']);
Route::post('/noconformidades/cierre', [NoConformidadController::class, 'grabarCierre']);

Route::get('/noconformidades/procesos', [NoConformidadController::class, 'cambioGestionProceso']);
Route::get('/noconformidades/reportes', [NoConformidadController::class, 'enviarReportes']);
Route::post('/noconformidades/reversar', [NoConformidadController::class, 'reversar']);