<?php

namespace App\Http\Controllers\Contador\utilities;

use App\Models\Contador;
use App\Models\Datos;
use App\Models\Contacto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StoreContador
{
    /**
     * Ejecuta la lógica de creación del contador en una transacción.
     *
     * @param array $data Los datos validados del FormRequest.
     * @return Contador El modelo del contador creado con sus relaciones.
     * @throws \Exception Si algo falla durante la transacción.
     */
    public function __invoke(array $data): Contador
    {
        DB::beginTransaction();

        try {
            // 1. Crear los Datos Personales
            $datos = Datos::create([
                'nombre'          => $data['datos']['nombre'],
                'apellidoPaterno' => $data['datos']['apellidoPaterno'],
                'apellidoMaterno' => $data['datos']['apellidoMaterno'],
                'sexo'            => $data['datos']['sexo'],
                'dni'             => $data['datos']['dni'],
            ]);

            // 2. Crear el Contacto (asociado a los datos)
            Contacto::create([
                'id_Datos'      => $datos->id,
                'telefonoMovil' => $data['contacto']['telefonoMovil'],
                'correo'        => $data['contacto']['correo'] ?? null,
            ]);

            // 3. Crear el Usuario (Contador)
            $contador = Contador::create([
                'id_Datos' => $datos->id,
                'username' => $data['usuario']['username'],
                'password' => Hash::make($data['usuario']['password']),
                'estado'   => 1, // Por defecto 'Activo'
            ]);

            // 4. Si todo salió bien, confirmar la transacción
            DB::commit();

            // Cargar las relaciones para devolver el objeto completo
            $contador->load('datos.contacto');

            return $contador;

        } catch (\Exception $e) {
            // 5. Si algo falló, revertir la transacción
            DB::rollBack();
            
            // Registrar el error
            Log::error('Error al crear contador (Utility): ' . $e->getMessage());

            // Re-lanzar la excepción para que el controlador la capture
            throw new \Exception('Ocurrió un error interno al crear el contador.', 500, $e);
        }
    }
}