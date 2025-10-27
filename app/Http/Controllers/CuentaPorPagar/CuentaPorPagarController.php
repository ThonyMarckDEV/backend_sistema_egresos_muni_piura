<?php

namespace App\Http\Controllers\CuentaPorPagar;

use App\Http\Controllers\Controller;
use App\Models\CuentaPorPagar;
use App\Http\Requests\CuentaPorPagar\StoreCuentaPorPagarRequest;
// Opcional: Si necesitas el modelo Egreso para validaciones extra
// use App\Models\Egreso; 
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CuentaPorPagarController extends Controller
{
    /**
     * Almacena una nueva cuenta por pagar.
     */
    public function store(StoreCuentaPorPagarRequest $request)
    {
        try {
            // La validación ya pasó gracias al Request
            $validatedData = $request->validated();
            
            // Creamos la cuenta por pagar (estado por defecto es 'pendiente')
            $cuentaPorPagar = CuentaPorPagar::create($validatedData);

            // Cargamos la relación con egreso para devolver info completa si es necesario
            $cuentaPorPagar->load('egreso.proveedor'); 

            return response()->json([
                'type' => 'success',
                'message' => 'Cuenta por pagar registrada exitosamente.',
                'cuenta_por_pagar' => $cuentaPorPagar
            ], 201);

        } catch (Exception $e) {
            Log::error('Error en CuentaPorPagarController@store: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error interno al registrar la cuenta por pagar.'
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $cuentas = CuentaPorPagar::with(['egreso.proveedor', 'egreso.categoria'])
                                    ->orderBy('fecha_vencimiento', 'asc')
                                    ->paginate(15);
            return response()->json($cuentas, 200);
        } catch (Exception $e) {
            Log::error('Error en CuentaPorPagarController@index: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error interno al listar las cuentas por pagar.'
            ], 500);
        }
    }
    
    public function show($id)
    {
         try {
            $cuenta = CuentaPorPagar::with(['egreso.proveedor', 'egreso.categoria'])->findOrFail($id);
            return response()->json($cuenta, 200);
        } catch (ModelNotFoundException $e) {
             return response()->json(['message' => 'Cuenta por pagar no encontrada.'], 404);
        } catch (Exception $e) {
             Log::error('Error en CuentaPorPagarController@show: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error interno al mostrar la cuenta por pagar.'
            ], 500);
        }
    }
 
}