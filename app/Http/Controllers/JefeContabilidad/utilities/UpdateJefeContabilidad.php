<?php

namespace App\Http\Controllers\JefeContabilidad\utilities;

use App\Models\JefeContabilidad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class UpdateJefeContabilidad
{
    /**
     * Ejecuta la lógica de actualización del jefe de contabilidad y sus relaciones.
     * Hacemos esta clase "invocable" para que se pueda llamar como una función.
     *
     * @param JefeContabilidad $jefeContabilidad El modelo a actualizar.
     * @param array $validatedData Los datos validados del request.
     * @return JefeContabilidads El modelo actualizado con relaciones.
     * @throws Exception Si algo sale mal.
     */
    public function __invoke(JefeContabilidad $jefeContabilidad, array $validatedData): JefeContabilidad
    {
        // Obtenemos las relaciones del jefe de contabilidad
        $datos = $jefeContabilidad->datos;
        $contacto = $datos ? $datos->contacto : null; // Verificación segura

        DB::beginTransaction();
        try {
            // 1. Actualizar Datos Personales (si se enviaron)
            if ($datos && isset($validatedData['datos'])) {
                $datos->update($validatedData['datos']);
            }

            // 2. Actualizar Contacto (si se envió y existe)
            if ($contacto && isset($validatedData['contacto'])) {
                $contacto->update($validatedData['contacto']);
            }

            // 3. Actualizar Usuario/Jefe de Contabilidad (si se enviaron datos)
            if (isset($validatedData['usuario'])) {
                $usuarioData = [
                    'username' => $validatedData['usuario']['username'],
                    'estado' => $validatedData['usuario']['estado'],
                ];
                
                // Solo actualiza la contraseña si se envió una nueva
                if (!empty($validatedData['usuario']['password'])) {
                    $usuarioData['password'] = Hash::make($validatedData['usuario']['password']);
                }

                $jefeContabilidad->update($usuarioData);
            }

            DB::commit();

            // Devolvemos el modelo actualizado con sus relaciones cargadas
            $jefeContabilidad->load('datos.contacto');
            return $jefeContabilidad;

        } catch (Exception $e) {
            DB::rollBack();
            // Relanzamos la excepción para que el controlador la maneje
            throw new Exception("Error en UpdateJefeContabilidad utility: " . $e->getMessage(), 500, $e);
        }
    }
}