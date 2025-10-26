<?php

use App\Http\Controllers\Auth\AuthController;

use App\Http\Controllers\Auth\ResetPassword\PasswordResetController;
use App\Http\Controllers\ClienteController\ClienteController;
use App\Http\Controllers\Contador\ContadorController;
use App\Http\Controllers\EvaluacionCliente\EvaluacionClienteController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/refresh', [AuthController::class, 'refresh']);

Route::post('/validate-refresh-token', [AuthController::class, 'validateRefreshToken']);

Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);

// RUTAS PARA cliente VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:admin'])->group(function () { 

//CRUD CONTADOR
    // Ruta para crear un nuevo contador
    Route::post('/contador/store', [ContadorController::class, 'store']);
    // Ruta para listar los contadores
    Route::get('/contadores', [ContadorController::class, 'index']);
    // Obtener un contador específico por ID
    Route::get('/contador/{id}', [ContadorController::class, 'show']);  
    // Actualizar un contador (usamos PUT para la actualización)
    Route::put('/contador/{id}', [ContadorController::class, 'update']);

});

// RUTAS PARA cliente VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:contador'])->group(function () { 



});


// RUTAS PARA VARIOS ROLES
Route::middleware(['auth.jwt', 'checkRolesMW'])->group(function () { 

    Route::post('/logout', [AuthController::class, 'logout']);

});

