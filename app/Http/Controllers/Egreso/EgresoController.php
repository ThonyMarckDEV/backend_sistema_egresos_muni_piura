<?php

namespace App\Http\Controllers\Egreso;

use App\Http\Controllers\Egreso\utilities\StoreEgreso;
use App\Http\Controllers\Egreso\utilities\UpdateEgreso;
use App\Models\Egreso;
use App\Http\Controllers\Controller;
// Imports de Requests
use App\Http\Requests\Egreso\StoreEgresoRequest;
use App\Http\Requests\Egreso\UpdateEgresoRequest;
// Imports de Utilities


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class EgresoController extends Controller
{
    /**
     * Muestra una lista paginada de egresos.
     */
    public function index(Request $request)
    {
        try {
            // Eager loading para evitar N+1 queries
            $egresos = Egreso::with(['categoria', 'proveedor'])
                             ->orderBy('created_at', 'desc') // Ordenar por más reciente
                             ->paginate(10);
                             
            return response()->json($egresos, 200);

        } catch (Exception $e) {
            Log::error('Error al listar egresos: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al obtener la lista de egresos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena un nuevo egreso usando la Utility.
     */
    public function store(StoreEgresoRequest $request, StoreEgreso $storeEgreso)
    {
        try {
            // La validación ya pasó, usamos la utility para crear
            $egreso = $storeEgreso->execute($request->validated());

            return response()->json([
                'type' => 'success',
                'message' => 'Egreso registrado exitosamente.',
                'egreso' => $egreso
            ], 201);

        } catch (Exception $e) {
            Log::error('Error en EgresoController@store: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error interno al crear el egreso.'
            ], 500);
        }
    }

    /**
     * Muestra un egreso específico.
     */
    public function show($id)
    {
        try {
            // Eager loading para la vista de detalle
            $egreso = Egreso::with(['categoria', 'proveedor'])->findOrFail($id);
            return response()->json($egreso, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Egreso no encontrado.'], 404);
        } catch (Exception $e) {
            Log::error("Error al obtener egreso $id: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al obtener el egreso.'], 500);
        }
    }

    /**
     * Actualiza un egreso existente usando la Utility.
     */
    public function update(UpdateEgresoRequest $request, UpdateEgreso $updateEgreso, $id)
    {
        try {
            $egreso = Egreso::findOrFail($id);
            
            // Usamos la utility para actualizar
            $egreso = $updateEgreso->execute($egreso, $request->validated());

            return response()->json([
                'message' => 'Egreso actualizado exitosamente.',
                'egreso' => $egreso
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Egreso no encontrado.'], 404);

        } catch (Exception $e) {
            Log::error("Error al actualizar egreso $id: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al actualizar el egreso.', 'error' => $e->getMessage()], 500);
        }
    }
}