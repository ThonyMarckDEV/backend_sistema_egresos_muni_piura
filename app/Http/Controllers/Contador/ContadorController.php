<?php

namespace App\Http\Controllers\Contador;

use App\Http\Controllers\Contador\utilities\UpdateContador;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Contador\utilities\StoreContador;
use App\Http\Requests\Contador\StoreContadorRequest;
use App\Http\Requests\Contador\UpdateContadorRequest;
use App\Models\Contador;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

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

    /**
     * Muestra una lista paginada de contadores.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $contadores = Contador::with('datos')
                                ->orderBy('id', 'desc')
                                ->paginate(10);

            return response()->json($contadores, 200);

        } catch (\Exception $e) {
            Log::error('Error al listar contadores: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al obtener la lista de contadores.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


/**
     * Muestra un contador específico con sus relaciones,
     * formateado para coincidir con el initialState del frontend.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $contador = Contador::with(['datos.contacto'])->findOrFail($id);

            $datos = $contador->datos;
            $contacto = $datos ? $datos->contacto : null;

            $responseData = [
                // Objeto 'datos' (para DatosPersonalesFields)
                'datos' => [
                    'nombre'          => $datos ? $datos->nombre : '',
                    'apellidoPaterno' => $datos ? $datos->apellidoPaterno : '',
                    'apellidoMaterno' => $datos ? $datos->apellidoMaterno : '',
                    'sexo'            => $datos ? $datos->sexo : 'M',
                    'dni'             => $datos ? $datos->dni : '',
                ],
                
                // Objeto 'contacto' (para DatosContactoFields)
                'contacto' => [
                    'telefonoMovil' => $contacto ? $contacto->telefonoMovil : '',
                    'correo'        => $contacto ? $contacto->correo : '',
                ],
                
                // Objeto 'usuario' (para DatosAccesoFields)
                'usuario' => [
                    'username' => $contador->username,
                    'estado'   => $contador->estado,
                    // 'password' no se envía, lo cual es correcto
                ],
            ];

            // 4. Retornamos el JSON formateado
            return response()->json($responseData, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Contador no encontrado.'], 404);
        } catch (\Exception $e) {
            Log::error("Error al obtener contador $id: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al obtener el contador.'], 500);
        }
    }

   /**
     * 3. Actualiza un contador existente usando la utilidad UpdateContador.
     *
     * @param  \App\Http\Requests\Contador\UpdateContadorRequest  $request
     * @param  int  $id
     * @param  \App\Utilities\UpdateContador $updater // Inyección de dependencia
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateContadorRequest $request, $id, UpdateContador $updater)
    {
        // La validación ya la hizo UpdateRequest
        $validatedData = $request->validated();
        
        try {
            // 1. Buscamos el contador
            $contador = Contador::findOrFail($id);

            // 2. Llamamos a la utilidad para hacer el trabajo
            $updatedContador = $updater($contador, $validatedData);

            // 3. Devolvemos la respuesta exitosa
            return response()->json([
                'message' => 'Contador actualizado exitosamente.',
                'contador' => $updatedContador
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Contador no encontrado.'], 404);
        
        } catch (Exception $e) {
            // Capturamos cualquier error lanzado por la utilidad
            DB::rollBack();
            Log::error("Error al actualizar contador $id: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al actualizar el contador.', 'error' => $e->getMessage()], 500);
        }
    }
}