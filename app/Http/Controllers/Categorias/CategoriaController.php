<?php

namespace App\Http\Controllers\Categorias;

use App\Models\Categoria;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categoria\StoreCategoriaRequest;
use App\Http\Requests\Categoria\UpdateCategoriaRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CategoriaController extends Controller
{
    /**
     * Muestra una lista paginada de categorías.
     */
    public function index(Request $request)
    {
        try {
            // Ordenar por nombre es más útil para categorías
            $categorias = Categoria::orderBy('nombre', 'asc')->paginate(10);
            return response()->json($categorias, 200);

        } catch (Exception $e) {
            Log::error('Error al listar categorías: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al obtener la lista de categorías.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena una nueva categoría.
     */
    public function store(StoreCategoriaRequest $request)
    {
        try {
            $validatedData = $request->validated();
            
            // La lógica es simple, no requiere una clase Utility
            $categoria = Categoria::create($validatedData);

            return response()->json([
                'type' => 'success',
                'message' => 'Categoría creada exitosamente.',
                'categoria' => $categoria
            ], 201);

        } catch (Exception $e) {
            Log::error('Error en CategoriaController@store: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error interno al crear la categoría.'
            ], 500);
        }
    }

    /**
     * Muestra una categoría específica.
     * No necesita el formato complejo de 'show' de JefeContabilidad.
     */
    public function show($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            // Simplemente devolvemos el modelo
            return response()->json($categoria, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        } catch (Exception $e) {
            Log::error("Error al obtener categoría $id: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al obtener la categoría.'], 500);
        }
    }

    /**
     * Actualiza una categoría existente.
     */
    public function update(UpdateCategoriaRequest $request, $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $validatedData = $request->validated();
            
            // La lógica de actualización es simple
            $categoria->update($validatedData);

            return response()->json([
                'message' => 'Categoría actualizada exitosamente.',
                'categoria' => $categoria
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);

        } catch (Exception $e) {
            Log::error("Error al actualizar categoría $id: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al actualizar la categoría.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina una categoría.
     */
    public function destroy($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->delete();

            return response()->json(['message' => 'Categoría eliminada exitosamente.'], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        } catch (Exception $e) {
             Log::error("Error al eliminar categoría $id: " . $e->getMessage());
            return response()->json(['message' => 'Error interno al eliminar la categoría.'], 500);
        }
    }
}