<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoConformidadController;

// Ruta raíz (Redirige al módulo de no conformidades por ahora)
Route::get('/', function () {
    return redirect('/noconformidades');
});

// Agrupación de rutas para el módulo No Conformidades
Route::prefix('noconformidades')->name('noconformidades.')->group(function () {
    
    // 1. Listado principal
    Route::get('/', [NoConformidadController::class, 'index'])->name('index');
    
    // 2. CRUD (Crear, Editar, Guardar, Consultar)
    Route::get('/mostrar/{id}', [NoConformidadController::class, 'show'])->name('show');
    Route::get('/editar/{id}', [NoConformidadController::class, 'edit'])->name('edit');
    Route::post('/guardar', [NoConformidadController::class, 'store'])->name('store');

    // 3. Seguimientos
    Route::get('/seguimientos/{id}', [NoConformidadController::class, 'getSeguimientos'])->name('seguimientos.get');
    Route::post('/seguimientos', [NoConformidadController::class, 'saveSeguimientos'])->name('seguimientos.save');

    // 4. Cierre
    Route::get('/cierre/{id}', [NoConformidadController::class, 'getCierre'])->name('cierre.get');
    Route::post('/cierre', [NoConformidadController::class, 'saveCierre'])->name('cierre.save');

    // 5. Auxiliares (Procesos, Reportes, Reversar)
    Route::get('/procesos/{id}', [NoConformidadController::class, 'getProcesos'])->name('procesos');
    Route::get('/reportes', [NoConformidadController::class, 'generateReport'])->name('reportes');
    Route::post('/reversar', [NoConformidadController::class, 'revert'])->name('reversar');
    
});