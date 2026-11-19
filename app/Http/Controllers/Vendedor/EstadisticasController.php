<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Throwable;

class EstadisticasController extends Controller
{
    /**
     * Muestra la vista principal de estadísticas con SQL puro.
     */
    public function index(Request $request)
    {
        try {
            $vendedorId = Auth::id();

            $request->validate([
                'fecha_inicio' => ['nullable', 'date'],
                'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            ]);

            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subDays(30)->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

            $resultado = DB::select("
                SELECT 
                    COUNT(p.id) AS total_pedidos,
                    COALESCE(SUM(CASE WHEN p.estado_pedido = 'entregado' THEN p.total ELSE 0 END), 0) AS total_ingresos,
                    COALESCE(SUM(CASE WHEN p.estado_pedido = 'entregado' THEN 1 ELSE 0 END), 0) AS pedidos_entregados,
                    COALESCE(SUM(CASE WHEN p.estado_pedido = 'cancelado' THEN 1 ELSE 0 END), 0) AS pedidos_cancelados
                FROM pedidos p
                INNER JOIN locales l ON p.local_id = l.id
                WHERE l.vendedor_id = ?
                  AND p.created_at BETWEEN ? AND ?
            ", [$vendedorId, $fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);

            $stats = $resultado[0] ?? null;

            $totalIngresos = (float) ($stats->total_ingresos ?? 0);
            $pedidosEntregados = (int) ($stats->pedidos_entregados ?? 0);
            $ticketPromedio = $pedidosEntregados > 0 ? round($totalIngresos / $pedidosEntregados, 2) : 0;

            return view('vendedor.estadisticas', [
                'totalIngresos' => $totalIngresos,
                'totalPedidos' => (int) ($stats->total_pedidos ?? 0),
                'pedidosEntregados' => $pedidosEntregados,
                'pedidosCancelados' => (int) ($stats->pedidos_cancelados ?? 0),
                'ticketPromedio' => $ticketPromedio,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
            ]);
        } catch (Throwable $e) {
            dd($e);
        }
    }

    /**
     * API: Ventas por periodo en SQL puro.
     */
    public function ventasPorPeriodo(Request $request)
    {
        try {
            $vendedorId = Auth::id();
            $dias = (int) $request->input('dias', 7);

            $ventas = DB::select("
                SELECT 
                    DATE(p.created_at) AS fecha,
                    COALESCE(SUM(CASE WHEN p.estado_pedido != 'cancelado' THEN p.total ELSE 0 END), 0) AS total_ventas,
                    COUNT(p.id) AS cantidad_pedidos
                FROM pedidos p
                INNER JOIN locales l ON p.local_id = l.id
                WHERE l.vendedor_id = ?
                  AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(p.created_at)
                ORDER BY fecha ASC
            ", [$vendedorId, $dias]);

            $ventas = array_map(function ($fila) {
                $fila->total_ventas = (float) $fila->total_ventas;
                $fila->cantidad_pedidos = (int) $fila->cantidad_pedidos;
                return $fila;
            }, $ventas);

            return response()->json([
                'success' => true,
                'data' => $ventas,
            ]);
        } catch (Throwable $e) {
            dd($e);
        }
    }

    /**
     * API: Productos más vendidos en SQL puro.
     */
    public function productosMasVendidos(Request $request)
    {
        try {
            $vendedorId = Auth::id();
            $limite = (int) $request->input('limite', 5);

            $topProductos = DB::select("
                SELECT 
                    pd.producto_id,
                    pd.nombre_producto,
                    COALESCE(SUM(pd.cantidad), 0) AS total_vendido,
                    COALESCE(SUM(pd.subtotal), 0) AS total_recaudado
                FROM pedido_detalles pd
                INNER JOIN pedidos p ON pd.pedido_id = p.id
                INNER JOIN locales l ON p.local_id = l.id
                WHERE l.vendedor_id = ?
                  AND p.estado_pedido != 'cancelado'
                GROUP BY pd.producto_id, pd.nombre_producto
                ORDER BY total_vendido DESC, total_recaudado DESC
                LIMIT {$limite}
            ", [$vendedorId]);

            $topProductos = array_map(function ($fila) {
                $fila->total_vendido = (int) $fila->total_vendido;
                $fila->total_recaudado = (float) $fila->total_recaudado;
                return $fila;
            }, $topProductos);

            return response()->json([
                'success' => true,
                'data' => $topProductos,
            ]);
        } catch (Throwable $e) {
            dd($e);
        }
    }

    /**
     * API: Resumen general en JSON con SQL puro.
     */
    public function resumenGeneral(Request $request)
    {
        try {
            $vendedorId = Auth::id();

            $request->validate([
                'fecha_inicio' => ['nullable', 'date'],
                'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            ]);

            $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subDays(30)->format('Y-m-d'));
            $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

            $resultado = DB::select("
                SELECT 
                    COUNT(p.id) AS total_pedidos,
                    COALESCE(SUM(CASE WHEN p.estado_pedido = 'entregado' THEN p.total ELSE 0 END), 0) AS total_ingresos,
                    COALESCE(SUM(CASE WHEN p.estado_pedido = 'entregado' THEN 1 ELSE 0 END), 0) AS pedidos_entregados,
                    COALESCE(SUM(CASE WHEN p.estado_pedido = 'cancelado' THEN 1 ELSE 0 END), 0) AS pedidos_cancelados
                FROM pedidos p
                INNER JOIN locales l ON p.local_id = l.id
                WHERE l.vendedor_id = ?
                  AND p.created_at BETWEEN ? AND ?
            ", [$vendedorId, $fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);

            $stats = $resultado[0] ?? null;
            $totalIngresos = (float) ($stats->total_ingresos ?? 0);
            $pedidosEntregados = (int) ($stats->pedidos_entregados ?? 0);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_pedidos' => (int) ($stats->total_pedidos ?? 0),
                    'total_ingresos' => $totalIngresos,
                    'pedidos_entregados' => $pedidosEntregados,
                    'pedidosCancelados' => (int) ($stats->pedidos_cancelados ?? 0),
                    'ticket_promedio' => $pedidosEntregados > 0 ? round($totalIngresos / $pedidosEntregados, 2) : 0,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                ],
            ]);
        } catch (Throwable $e) {
            dd($e);
        }
    }

    /**
     * API: Pedidos por estado en SQL puro.
     */
    public function pedidosPorEstado()
    {
        try {
            $vendedorId = Auth::id();

            $pedidos = DB::select("
                SELECT 
                    p.estado_pedido,
                    COUNT(p.id) AS cantidad
                FROM pedidos p
                INNER JOIN locales l ON p.local_id = l.id
                WHERE l.vendedor_id = ?
                GROUP BY p.estado_pedido
                ORDER BY cantidad DESC
            ", [$vendedorId]);

            $pedidos = array_map(function ($fila) {
                $fila->cantidad = (int) $fila->cantidad;
                return $fila;
            }, $pedidos);

            return response()->json([
                'success' => true,
                'data' => $pedidos,
            ]);
        } catch (Throwable $e) {
            dd($e);
        }
    }
}