<?php

namespace App\Http\Controllers\Proveedor;

// Modelos y Requests adaptados
use App\Models\Proveedor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedor\StoreProveedorRequest;
use App\Http\Requests\Proveedor\UpdateProveedorRequest;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ProveedorController extends Controller
{
    /**
     * Muestra una lista paginada de proveedores.
     */
    public function index(Request $request)
    {
        try {
            $proveedores = Proveedor::orderBy('nombre', 'asc')->paginate(10);
            return response()->json($proveedores, 200);

        } catch (Exception $e) {
            Log::error('Error al listar proveedores: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al obtener la lista de proveedores.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   /**
     * Obtiene TODOS los proveedores (para comboboxes).
     */
    public function getAll(Request $request)
    {
        try {
            // Filtramos solo los activos para los selects
            $proveedores = Proveedor::where('estado', 1)->orderBy('nombre', 'asc')->get();
            return response()->json($proveedores, 200);

        } catch (Exception $e) {
            Log::error('Error al obtener todos los proveedores: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al obtener la lista de proveedores.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena un nuevo proveedor.
     */
    public function store(StoreProveedorRequest $request)
    {
        try {
            $validatedData = $request->validated();
            
            $proveedor = Proveedor::create($validatedData);

            return response()->json([
                'type' => 'success',
                'message' => 'Proveedor creado exitosamente.',
                'proveedor' => $proveedor
            ], 201);

        } catch (Exception $e) {
            Log::error('Error en ProveedorController@store: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error interno al crear el proveedor.'
            ], 500);
        }
    }

    /**
     * Muestra un proveedor específico.
     */
    public function show($id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            return response()->json($proveedor, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Proveedor no encontrado.'], 404);
        } catch (Exception $e) {
            Log::error("Error al obtener proveedor $id: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al obtener el proveedor.'], 500);
        }
    }

    /**
     * Actualiza un proveedor existente.
     * El parámetro de ruta debe coincidir (ej. 'proveedor')
     */
    public function update(UpdateProveedorRequest $request, $id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            $validatedData = $request->validated();
            
            $proveedor->update($validatedData);

            return response()->json([
                'message' => 'Proveedor actualizado exitosamente.',
                'proveedor' => $proveedor
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Proveedor no encontrado.'], 404);

        } catch (Exception $e) {
            Log::error("Error al actualizar proveedor $id: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al actualizar el proveedor.', 'error' => $e->getMessage()], 500);
        }
    }

}