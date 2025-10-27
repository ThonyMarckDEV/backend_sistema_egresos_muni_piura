<?php

namespace App\Http\Controllers\CuentaPorPagar;

use App\Http\Controllers\Controller;
use App\Http\Requests\CuentaPorPagar\PagarCuentaRequest;
use App\Models\CuentaPorPagar;
use App\Http\Requests\CuentaPorPagar\StoreCuentaPorPagarRequest;
// Opcional: Si necesitas el modelo Egreso para validaciones extra
// use App\Models\Egreso; 
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;

class CuentaPorPagarController extends Controller
{
    /**
     * Almacena una nueva cuenta por pagar.
     */
    public function store(StoreCuentaPorPagarRequest $request)
    {
        try {
            // La validación ya pasó gracias al Request
            $validatedData = $request->validated();
            
            // Creamos la cuenta por pagar (estado por defecto es 'pendiente')
            $cuentaPorPagar = CuentaPorPagar::create($validatedData);

            // Cargamos la relación con egreso para devolver info completa si es necesario
            $cuentaPorPagar->load('egreso.proveedor'); 

            return response()->json([
                'type' => 'success',
                'message' => 'Cuenta por pagar registrada exitosamente.',
                'cuenta_por_pagar' => $cuentaPorPagar
            ], 201);

        } catch (Exception $e) {
            Log::error('Error en CuentaPorPagarController@store: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error interno al registrar la cuenta por pagar.'
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $cuentas = CuentaPorPagar::with(['egreso.proveedor', 'egreso.categoria'])
                                    ->orderBy('fecha_vencimiento', 'asc')
                                    ->paginate(15);
            return response()->json($cuentas, 200);
        } catch (Exception $e) {
            Log::error('Error en CuentaPorPagarController@index: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error interno al listar las cuentas por pagar.'
            ], 500);
        }
    }
    
    public function show($id)
    {
         try {
            $cuenta = CuentaPorPagar::with(['egreso.proveedor', 'egreso.categoria'])->findOrFail($id);
            return response()->json($cuenta, 200);
        } catch (ModelNotFoundException $e) {
             return response()->json(['message' => 'Cuenta por pagar no encontrada.'], 404);
        } catch (Exception $e) {
             Log::error('Error en CuentaPorPagarController@show: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error interno al mostrar la cuenta por pagar.'
            ], 500);
        }
    }

    /**
     * Marca una cuenta por pagar como pagada y registra los detalles.
     */
    public function marcarComoPagado(PagarCuentaRequest $request, $id)
    {
        // Usamos una transacción por si algo falla
        DB::beginTransaction();
        try {
            // Busca la cuenta o falla (404)
            $cuenta = CuentaPorPagar::with('egreso')->findOrFail($id);

            // Validación extra: No se puede pagar si ya está pagada
            if ($cuenta->estado === 'pagado') {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Esta cuenta ya ha sido marcada como pagada.',
                ], 409); // 409 Conflict
            }

            $validatedData = $request->validated();

            // Actualizamos la cuenta
            $cuenta->estado = 'pagado';
            // Marcamos el monto total del egreso como pagado
            $cuenta->monto_pagado = $cuenta->egreso->monto; 
            $cuenta->metodo_pago = $validatedData['metodo_pago'];
            // Guarda null si es 'Efectivo', sino el número ingresado
            $cuenta->numero_operacion = ($validatedData['metodo_pago'] === 'Efectivo') ? null : $validatedData['numero_operacion'];
            
            $cuenta->save();

            DB::commit(); // Todo OK, confirma los cambios

            return response()->json([
                'type' => 'success',
                'message' => 'Cuenta marcada como pagada exitosamente.',
                'cuenta_por_pagar' => $cuenta->fresh()->load('egreso.proveedor') // Devuelve actualizada
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack(); // Revierte si no se encontró
            return response()->json(['message' => 'Cuenta por pagar no encontrada.'], 404);
        } catch (Exception $e) {
            DB::rollBack(); // Revierte en cualquier otro error
            Log::error("Error al marcar como pagada la cuenta $id: " . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Error interno al actualizar la cuenta por pagar.', 
                'error' => $e->getMessage()
            ], 500);
        }
    }
 
}