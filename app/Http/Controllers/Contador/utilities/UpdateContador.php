<?php

namespace App\Http\Controllers\Contador\utilities;

use App\Models\Contador; // O tu modelo de Usuario/Contador
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class UpdateContador
{
    /**
     * Ejecuta la lógica de actualización del contador y sus relaciones.
     * Hacemos esta clase "invocable" para que se pueda llamar como una función.
     *
     * @param Contador $contador El modelo a actualizar.
     * @param array $validatedData Los datos validados del request.
     * @return Contador El modelo actualizado con relaciones.
     * @throws Exception Si algo sale mal.
     */
    public function __invoke(Contador $contador, array $validatedData): Contador
    {
        // Obtenemos las relaciones del contador
        $datos = $contador->datos;
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

            // 3. Actualizar Usuario/Contador (si se enviaron datos)
            if (isset($validatedData['usuario'])) {
                $usuarioData = [
                    'username' => $validatedData['usuario']['username'],
                    'estado' => $validatedData['usuario']['estado'],
                ];
                
                // Solo actualiza la contraseña si se envió una nueva
                if (!empty($validatedData['usuario']['password'])) {
                    $usuarioData['password'] = Hash::make($validatedData['usuario']['password']);
                }
                
                $contador->update($usuarioData);
            }

            DB::commit();

            // Devolvemos el modelo actualizado con sus relaciones cargadas
            $contador->load('datos.contacto');
            return $contador;

        } catch (Exception $e) {
            DB::rollBack();
            // Relanzamos la excepción para que el controlador la maneje
            throw new Exception("Error en UpdateContador utility: " . $e->getMessage(), 500, $e);
        }
    }
}