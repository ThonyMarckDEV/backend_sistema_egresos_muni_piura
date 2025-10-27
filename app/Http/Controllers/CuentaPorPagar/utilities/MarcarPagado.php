<?php

namespace App\Http\Controllers\CuentaPorPagar\utilities;

use App\Models\CuentaPorPagar;
use Illuminate\Support\Facades\Log;
use Exception;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException; // Para el error 409

class MarcarPagado
{
    /**
     * Marca una cuenta por pagar como pagada y actualiza sus detalles.
     *
     * @param CuentaPorPagar $cuenta La cuenta a marcar como pagada.
     * @param array $validatedData Datos validados del pago ['metodo_pago', 'numero_operacion'].
     * @return CuentaPorPagar La cuenta actualizada.
     * @throws ConflictHttpException Si la cuenta ya estaba pagada.
     * @throws Exception Si ocurre otro error al guardar.
     */
    public function execute(CuentaPorPagar $cuenta, array $validatedData): CuentaPorPagar
    {
        // 1. Validación: No se puede pagar si ya está pagada
        if ($cuenta->estado === 'pagado') {
            // Lanza una excepción específica que el controlador puede manejar
            throw new ConflictHttpException('Esta cuenta ya ha sido marcada como pagada.');
        }

        try {
            // 2. Actualizamos los campos de la cuenta
            $cuenta->estado = 'pagado';
            // Marcamos el monto total del egreso como pagado
            // Asegúrate que la relación 'egreso' esté cargada antes de llamar a execute()
            if (!$cuenta->relationLoaded('egreso')) {
                 $cuenta->load('egreso');
            }
            $cuenta->monto_pagado = $cuenta->egreso->monto;
            $cuenta->metodo_pago = $validatedData['metodo_pago'];
            // Guarda null si es 'Efectivo', sino el número ingresado
            $cuenta->numero_operacion = ($validatedData['metodo_pago'] === 'Efectivo') ? null : $validatedData['numero_operacion'];

            // 3. Guardamos los cambios
            $cuenta->save();

            // 4. Devolvemos la cuenta actualizada
            return $cuenta->fresh(); // 'fresh()' recarga el modelo desde la BD

        } catch (Exception $e) {
            Log::error('Error en Utility MarcarPagado: ' . $e->getMessage());
            // Relanza la excepción para que el controlador la maneje (y haga rollback)
            throw new Exception('Error al actualizar la cuenta en la utilidad.');
        }
    }
}