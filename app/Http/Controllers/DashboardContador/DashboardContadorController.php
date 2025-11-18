<?php

namespace App\Http\Controllers\DashboardContador;

use App\Http\Controllers\Controller;
use App\Models\Egreso;
use App\Models\Categoria;
use App\Models\CuentaPorPagar;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardContadorController extends Controller
{
    /**
     * Obtiene todos los datos para el dashboard del Contador.
     */
    public function getContadorDashboardData(Request $request)
    {
        try {
            // 1. KPIs Principales
            $kpis = $this->getKpis();

            // 2. Egresos por Categoría (para gráfico de Torta/Dona)
            $egresosPorCategoria = $this->getEgresosPorCategoria();

            // 3. Egresos en los últimos 6 meses (para gráfico de Línea)
            $egresosMensuales = $this->getEgresosMensuales();

            // --- CORRECCIÓN ---
            // Se envuelve la respuesta en un objeto 'data' para que
            // el 'handleResponse' del frontend pueda procesarlo correctamente.
            return response()->json([
                'success' => true,
                'data' => [
                    'kpis' => $kpis,
                    'egresosPorCategoria' => $egresosPorCategoria,
                    'egresosMensuales' => $egresosMensuales,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en DashboardContadorController@getContadorDashboardData: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Ocurrió un error interno al cargar los datos del dashboard.'
            ], 500);
        }
    }

    /**
     * Obtiene los KPIs (Indicadores Clave de Rendimiento).
     */
    private function getKpis()
    {
        $now = Carbon::now();
        $mesActualStart = $now->copy()->startOfMonth();
        $mesActualEnd = $now->copy()->endOfMonth();

        // Total Egresos del Mes
        $totalEgresosMes = Egreso::whereBetween('created_at', [$mesActualStart, $mesActualEnd])
                                    ->sum('monto');

        // Total Pendiente en Cuentas por Pagar (--- CORREGIDO ---)
        $totalPendiente = CuentaPorPagar::query()
            // 1. Unir con la tabla de egresos, usando la llave foránea
            ->join('egresos', 'cuentas_por_pagar.egreso_id', '=', 'egresos.id')
            // 2. Filtrar solo por las cuentas pendientes
            ->where('cuentas_por_pagar.estado', 'pendiente')
            // 3. Calcular la suma de (total - pagado)
            ->sum(DB::raw('egresos.monto - cuentas_por_pagar.monto_pagado'));

        // Total Proveedores Activos
        $totalProveedores = Proveedor::where('estado', 1)->count();
        
        // Total Categorías Activas
        $totalCategorias = Categoria::where('estado', 1)->count();

        return [
            'totalEgresosMes' => (float) $totalEgresosMes,
            'totalPendiente' => (float) $totalPendiente,
            'totalProveedores' => (int) $totalProveedores,
            'totalCategorias' => (int) $totalCategorias,
        ];
    }

    /**
     * Obtiene la suma de egresos agrupados por categoría.
     */
    private function getEgresosPorCategoria()
    {
        return Egreso::join('categorias', 'egresos.categoria_id', '=', 'categorias.id')
            ->select('categorias.nombre', DB::raw('SUM(egresos.monto) as total'))
            ->groupBy('categorias.nombre')
            ->orderBy('total', 'desc')
            ->limit(7) // Limitar a las 7 categorías principales
            ->get();
    }

    /**
     * Obtiene la suma de egresos de los últimos 6 meses.
     */
    private function getEgresosMensuales()
    {
        $egresos = Egreso::select(
                DB::raw('SUM(monto) as total'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as mes")
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->groupBy('mes')
            ->orderBy('mes', 'asc')
            ->get();
            
        // Formatear para Chart.js (asegurar que todos los meses estén)
        $labels = [];
        $data = [];
        $mesActual = Carbon::now()->subMonths(5)->startOfMonth();

        for ($i = 0; $i < 6; $i++) {
            $mesKey = $mesActual->format('Y-m');
            $labels[] = $mesActual->format('M Y'); // ej: "Nov 2025"
            
            $egresoMes = $egresos->firstWhere('mes', $mesKey);
            $data[] = $egresoMes ? (float) $egresoMes->total : 0;
            
            $mesActual->addMonth();
        }

        return ['labels' => $labels, 'data' => $data];
    }
}