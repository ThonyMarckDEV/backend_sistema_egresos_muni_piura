<?php

namespace App\Http\Controllers\JefeContabilidad;

use App\Http\Controllers\JefeContabilidad\utilities\StoreJefeContabilidad;
use App\Http\Controllers\JefeContabilidad\utilities\UpdateJefeContabilidad;
use App\Http\Controllers\Controller;
use App\Http\Requests\JefeContabilidad\StoreJefeContabilidadRequest;
use App\Http\Requests\JefeContabilidad\UpdateJefeContabilidadRequest;
use App\Models\JefeContabilidad;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;


    class JefeContabilidadController extends Controller
{
    /**
     * Almacena un nuevo jefe de contabilidad (Datos, Contacto y Usuario).
     *
     * Inyectamos el StoreRequest para validar y el StoreJefeContabilidad para la lógica.
     */
    public function store(StoreJefeContabilidadRequest $request, StoreJefeContabilidad $storeJefeContabilidad)
    {
        try {
            // 2. Obtenemos los datos ya validados.
            $validatedData = $request->validated();
            
            // 3. Ejecutamos la lógica de negocio llamando a la clase de utilidad.
            //    Usamos $storeJefeContabilidad($validatedData) porque tiene el método __invoke.
            $jefeContabilidad = $storeJefeContabilidad($validatedData);

            // 4. Si todo salió bien, devolvemos la respuesta exitosa.
            return response()->json([
                'type' => 'success',
                'message' => 'Jefe de contabilidad creado exitosamente.',
                'jefe_contabilidad' => $jefeContabilidad
            ], 201);

        } catch (\Exception $e) {
            // 5. Capturamos cualquier error que haya lanzado la clase StoreJefeContabilidad.
            Log::error('Error en JefeContabilidadController@store: ' . $e->getMessage());

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
            $jefesContabilidad = JefeContabilidad::with('datos')
                                ->orderBy('id', 'desc')
                                ->paginate(10);

            return response()->json($jefesContabilidad, 200);

        } catch (\Exception $e) {
            Log::error('Error al listar jefes de contabilidad: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al obtener la lista de jefes de contabilidad.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


/**
     * Muestra un jefe de contabilidad específico con sus relaciones,
     * formateado para coincidir con el initialState del frontend.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $jefeContabilidad = JefeContabilidad::with(['datos.contacto'])->findOrFail($id);

            $datos = $jefeContabilidad->datos;
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
                    'username' => $jefeContabilidad->username,
                    'estado'   => $jefeContabilidad->estado,
                    // 'password' no se envía, lo cual es correcto
                ],
            ];

            // 4. Retornamos el JSON formateado
            return response()->json($responseData, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Jefe de contabilidad no encontrado.'], 404);
        } catch (\Exception $e) {
            Log::error("Error al obtener jefe de contabilidad $id: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al obtener el jefe de contabilidad.'], 500);
        }
    }

   /**
     * 3. Actualiza un jefe de contabilidad existente usando la utilidad UpdateJefeContabilidad.
     *
     * @param  \App\Http\Requests\Contador\UpdateContadorRequest  $request
     * @param  int  $id
     * @param  \App\Utilities\UpdateContador $updater // Inyección de dependencia
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateJefeContabilidadRequest $request, $id, UpdateJefeContabilidad $updater)
    {
        // La validación ya la hizo UpdateRequest
        $validatedData = $request->validated();
        
        try {
            // 1. Buscamos el jefe de contabilidad a actualizar
            $jefeContabilidad = JefeContabilidad::findOrFail($id);

            // 2. Llamamos a la utilidad para hacer el trabajo
            $updatedJefeContabilidad = $updater($jefeContabilidad, $validatedData);

            // 3. Devolvemos la respuesta exitosa
            return response()->json([
                'message' => 'Jefe de contabilidad actualizado exitosamente.',
                'jefeContabilidad' => $updatedJefeContabilidad
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Jefe de contabilidad no encontrado.'], 404);

        } catch (Exception $e) {
            // Capturamos cualquier error lanzado por la utilidad
            DB::rollBack();
            Log::error("Error al actualizar jefe de contabilidad $id: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al actualizar el jefe de contabilidad.', 'error' => $e->getMessage()], 500);
        }
    }
}