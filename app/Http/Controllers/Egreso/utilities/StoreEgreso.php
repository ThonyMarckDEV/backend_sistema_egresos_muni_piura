<?php

namespace App\Http\Controllers\Egreso\utilities;

use App\Models\Egreso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class StoreEgreso
{
    /**
     * Crea un nuevo registro de Egreso.
     *
     * @param array $validatedData
     * @return Egreso
     * @throws Exception
     */
    public function execute(array $validatedData): Egreso
    {
        try {
            // (Aquí podrías agregar lógica más compleja, como transacciones)
            $egreso = Egreso::create($validatedData);

            return $egreso;
            
        } catch (Exception $e) {
            Log::error('Error en Utility StoreEgreso: ' . $e->getMessage());
            // Relanza la excepción para que el controlador la atrape
            throw new Exception('Error al guardar el egreso en la utilidad.');
        }
    }
}