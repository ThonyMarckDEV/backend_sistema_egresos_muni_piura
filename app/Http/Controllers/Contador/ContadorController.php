<?php

namespace App\Http\Controllers\Contador;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Contador\utilities\StoreContador;
use App\Http\Requests\Contador\StoreContadorRequest;
use Illuminate\Support\Facades\Log;
// Ya no necesitas importar:
// - Request, DB, Hash, ValidationException, Contador, Datos, Contacto

class ContadorController extends Controller
{
    /**
     * Almacena un nuevo contador (Datos, Contacto y Usuario).
     *
     * Inyectamos el StoreRequest para validar y el StoreContador para la lógica.
     */
    public function store(StoreContadorRequest $request, StoreContador $storeContador)
    {
        try {
            // 2. Obtenemos los datos ya validados.
            $validatedData = $request->validated();
            
            // 3. Ejecutamos la lógica de negocio llamando a la clase de utilidad.
            //    Usamos $storeContador($validatedData) porque tiene el método __invoke.
            $contador = $storeContador($validatedData);

            // 4. Si todo salió bien, devolvemos la respuesta exitosa.
            return response()->json([
                'type' => 'success',
                'message' => 'Contador creado exitosamente.',
                'contador' => $contador
            ], 201);

        } catch (\Exception $e) {
            // 5. Capturamos cualquier error que haya lanzado la clase StoreContador.
            Log::error('Error en ContadorController@store: ' . $e->getMessage());

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage(), // Mensaje de la excepción
                'error' => 'Ocurrió un error interno al procesar la solicitud.'
            ], 500);
        }
    }
}