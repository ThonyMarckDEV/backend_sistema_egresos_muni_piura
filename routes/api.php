<?php

use App\Http\Controllers\Auth\AuthController;

use App\Http\Controllers\Auth\ResetPassword\PasswordResetController;
use App\Http\Controllers\Categoria\CategoriaController;
use App\Http\Controllers\Contador\ContadorController;
use App\Http\Controllers\CuentaPorPagar\CuentaPorPagarController;
use App\Http\Controllers\DashboardContador\DashboardContadorController;
use App\Http\Controllers\Egreso\EgresoController;
use App\Http\Controllers\JefeContabilidad\JefeContabilidadController;
use App\Http\Controllers\Proveedor\ProveedorController;
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

//CRUD JEFE CONTABILIDAD
    // Ruta para crear un nuevo jefe contabilidad
    Route::post('/jefe-contabilidad/store', [JefeContabilidadController::class, 'store']);
    // Ruta para listar los jefes de contabilidad
    Route::get('/jefes-contabilidad', [JefeContabilidadController::class, 'index']);
    // Obtener un jefe de contabilidad específico por ID
    Route::get('/jefe-contabilidad/{id}', [JefeContabilidadController::class, 'show']);  
    // Actualizar un jefe de contabilidad (usamos PUT para la actualización)
    Route::put('/jefe-contabilidad/{id}', [JefeContabilidadController::class, 'update']);  

});

// RUTAS PARA cliente VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:contador'])->group(function () { 

    //CRUD CATEGORIAS
    Route::post('/categoria/store', [CategoriaController::class, 'store']);
    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::get('/categoria/{id}', [CategoriaController::class, 'show']);
    Route::put('/categoria/{id}', [CategoriaController::class, 'update']);
    Route::get('/categorias/all', [CategoriaController::class, 'getAll']);

    //CRUD EGRESOS
    Route::post('/egreso/store', [EgresoController::class, 'store']);
    Route::get('/egresos', [EgresoController::class, 'index']);
    Route::get('/egreso/{id}', [EgresoController::class, 'show']);
    Route::put('/egreso/{id}', [EgresoController::class, 'update']);

    //CRUD CUENTAS POR PAGAR
    Route::post('/cuenta-por-pagar/store', [CuentaPorPagarController::class, 'store']);
    Route::get('/cuentas-por-pagar', [CuentaPorPagarController::class, 'index']);
    Route::get('/cuenta-por-pagar/{id}', [CuentaPorPagarController::class, 'show']);
    Route::put('/cuenta-por-pagar/{id}/pagar', [CuentaPorPagarController::class, 'marcarComoPagado']);

    //DASHBOARD
    Route::get('/dashboard-data-contador', [DashboardContadorController::class, 'getContadorDashboardData']);
});

// RUTAS PARA cliente VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:jefe_contabilidad'])->group(function () { 

    //CRUD PROVEEDOR
    Route::post('/proveedor/store', [ProveedorController::class, 'store']);
    Route::get('/proveedores', [ProveedorController::class, 'index']);
    Route::get('/proveedor/{id}', [ProveedorController::class, 'show']);
    Route::put('/proveedor/{id}', [ProveedorController::class, 'update']);

});

// RUTAS PARA cliente VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'CheckRolesMW_CONTADOR_JEFE_CONTABILIDAD'])->group(function () { 

    Route::get('/proveedores/all', [ProveedorController::class, 'getAll']);

});

    




// RUTAS PARA VARIOS ROLES
Route::middleware(['auth.jwt', 'checkRolesMW'])->group(function () { 

    Route::post('/logout', [AuthController::class, 'logout']);

});

