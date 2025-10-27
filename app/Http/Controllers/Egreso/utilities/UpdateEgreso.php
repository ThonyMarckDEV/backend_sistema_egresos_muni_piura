<?php

namespace App\Http\Controllers\Egreso\utilities;

use App\Models\Egreso;
use Illuminate\Support\Facades\Log;
use Exception;

class UpdateEgreso
{
    /**
     * Actualiza un Egreso existente.
     *
     * @param Egreso $egreso El modelo a actualizar
     * @param array $validatedData Los nuevos datos
     * @return Egreso
     * @throws Exception
     */
    public function execute(Egreso $egreso, array $validatedData): Egreso
    {
        try {
            $egreso->update($validatedData);
            
            return $egreso->fresh(); // Devuelve el modelo actualizado

        } catch (Exception $e) {
            Log::error('Error en Utility UpdateEgreso: ' . $e->getMessage());
            throw new Exception('Error al actualizar el egreso en la utilidad.');
        }
    }
}