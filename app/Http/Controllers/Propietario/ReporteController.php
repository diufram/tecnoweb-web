<?php

namespace App\Http\Controllers\Propietario;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Propietario;
use App\Models\Proveedor;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    public function index(Request $request): Response
    {
        $tipo = $request->query('tipo', 'general');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $umbral = $request->query('umbral', 10);
        $dias = $request->query('dias', 30);

        $rows = [];

        switch (strtolower($tipo)) {
            case 'ventas':
                $rows = array_merge($rows, $this->reporteVentas($desde, $hasta));
                break;
            case 'compras':
                $rows = array_merge($rows, $this->reporteCompras());
                break;
            case 'inventario':
                $rows = array_merge($rows, $this->reporteInventario());
                break;
            case 'usuarios':
                $rows = array_merge($rows, $this->reporteUsuarios());
                break;
            case 'productos':
                $rows = array_merge($rows, $this->reporteProductos());
                break;
            case 'mas_vendidos':
                $rows = array_merge($rows, $this->reporteProductosMasVendidos());
                break;
            case 'stock_minimo':
                $rows = array_merge($rows, $this->reporteAlertaStockMinimo($umbral));
                break;
            case 'por_vencer':
                $rows = array_merge($rows, $this->reporteProductosProximosAVencer($dias));
                break;
            case 'valor_inventario':
                $rows = array_merge($rows, $this->reporteValorTotalInventario());
                break;
            default:
                $rows = array_merge($rows, $this->reporteVentas($desde, $hasta));
                $rows = array_merge($rows, $this->reporteCompras());
                $rows = array_merge($rows, $this->reporteInventario());
                $rows = array_merge($rows, $this->reporteUsuarios());
                $rows = array_merge($rows, $this->reporteProductos());
                break;
        }

        return Inertia::render('propietario/reportes/Index', [
            'reporteRows' => $rows,
            'filters' => [
                'tipo' => $tipo,
                'desde' => $desde,
                'hasta' => $hasta,
                'umbral' => $umbral,
                'dias' => $dias,
            ],
        ]);
    }

    private function reporteVentas($desde, $hasta): array
    {
        $query = Venta::query();

        if (!empty($desde)) {
            $query->where('fecha', '>=', Carbon::parse($desde)->startOfDay());
        }
        if (!empty($hasta)) {
            $query->where('fecha', '<=', Carbon::parse($hasta)->endOfDay());
        }

        $cantidad = $query->count();
        $total = $query->sum('total') ?: 0.0;

        return [
            [
                'categoria' => 'VENTAS',
                'descripcion' => 'Cantidad de ventas',
                'total' => (string) $cantidad,
            ],
            [
                'categoria' => 'VENTAS',
                'descripcion' => 'Monto total vendido',
                'total' => sprintf('%.2f BOB', $total),
            ],
        ];
    }

    private function reporteCompras(): array
    {
        $results = Compra::select('estado')
            ->selectRaw('COUNT(*) as cantidad')
            ->selectRaw('COALESCE(SUM(monto_total), 0) as total')
            ->groupBy('estado')
            ->get();

        $rows = [];
        foreach ($results as $row) {
            $estado = $row->estado ?? '';
            $rows[] = [
                'categoria' => 'COMPRAS',
                'descripcion' => 'Estado: ' . $estado . ' - Cantidad',
                'total' => (string) $row->cantidad,
            ];
            $rows[] = [
                'categoria' => 'COMPRAS',
                'descripcion' => 'Estado: ' . $estado . ' - Monto',
                'total' => sprintf('%.2f BOB', $row->total),
            ];
        }

        return $rows;
    }

    private function reporteInventario(): array
    {
        $results = Producto::leftJoin('inventario', 'producto.id', '=', 'inventario.id_producto')
            ->select('producto.nombre_comercial')
            ->selectRaw('COALESCE(SUM(inventario.cantidad_disponible), 0) as cantidad')
            ->groupBy('producto.id', 'producto.nombre_comercial')
            ->orderBy('producto.nombre_comercial', 'asc')
            ->get();

        $rows = [];
        foreach ($results as $row) {
            $rows[] = [
                'categoria' => 'INVENTARIO',
                'descripcion' => $row->nombre_comercial ?? '',
                'total' => (string) $row->cantidad,
            ];
        }

        return $rows;
    }

    private function reporteUsuarios(): array
    {
        $clientes = Cliente::count();
        $proveedores = Proveedor::count();
        $propietarios = Propietario::count();

        return [
            [
                'categoria' => 'USUARIOS',
                'descripcion' => 'Clientes',
                'total' => (string) $clientes,
            ],
            [
                'categoria' => 'USUARIOS',
                'descripcion' => 'Proveedores',
                'total' => (string) $proveedores,
            ],
            [
                'categoria' => 'USUARIOS',
                'descripcion' => 'Propietarios',
                'total' => (string) $propietarios,
            ],
        ];
    }

    private function reporteProductos(): array
    {
        $cantidad = Producto::count();
        $stock = Producto::sum('stock_actual') ?: 0;

        return [
            [
                'categoria' => 'PRODUCTOS',
                'descripcion' => 'Cantidad de productos',
                'total' => (string) $cantidad,
            ],
            [
                'categoria' => 'PRODUCTOS',
                'descripcion' => 'Stock total',
                'total' => (string) $stock,
            ],
        ];
    }

    private function reporteProductosMasVendidos(): array
    {
        $results = VentaDetalle::join('inventario', 'venta_detalle.id_inventario', '=', 'inventario.id')
            ->join('producto', 'inventario.id_producto', '=', 'producto.id')
            ->select('producto.nombre_comercial')
            ->selectRaw('COALESCE(SUM(venta_detalle.cantidad), 0) as cantidad')
            ->groupBy('producto.id', 'producto.nombre_comercial')
            ->orderByRaw('SUM(venta_detalle.cantidad) DESC')
            ->limit(20)
            ->get();

        $rows = [];
        foreach ($results as $row) {
            $rows[] = [
                'categoria' => 'MAS VENDIDOS',
                'descripcion' => $row->nombre_comercial ?? '',
                'total' => (string) $row->cantidad,
            ];
        }

        return $rows;
    }

    private function reporteAlertaStockMinimo($umbral): array
    {
        $results = Producto::where('stock_actual', '<=', (int) $umbral)
            ->orderBy('stock_actual', 'asc')
            ->get();

        $rows = [];
        foreach ($results as $row) {
            $rows[] = [
                'categoria' => 'STOCK MINIMO',
                'descripcion' => $row->nombre_comercial ?? '',
                'total' => (string) $row->stock_actual,
            ];
        }

        return $rows;
    }

    private function reporteProductosProximosAVencer($dias): array
    {
        $hoy = Carbon::today();
        $limite = $hoy->copy()->addDays((int) $dias);

        $results = Inventario::join('lote', 'inventario.id_lote', '=', 'lote.id')
            ->join('producto', 'inventario.id_producto', '=', 'producto.id')
            ->select('producto.nombre_comercial', 'lote.fecha_vencimiento')
            ->selectRaw('COALESCE(SUM(inventario.cantidad_disponible), 0) as cantidad')
            ->whereBetween('lote.fecha_vencimiento', [$hoy, $limite])
            ->groupBy('producto.id', 'producto.nombre_comercial', 'lote.fecha_vencimiento')
            ->orderBy('lote.fecha_vencimiento', 'asc')
            ->get();

        $rows = [];
        foreach ($results as $row) {
            $fecha = $row->fecha_vencimiento ? Carbon::parse($row->fecha_vencimiento)->toDateString() : '';
            $rows[] = [
                'categoria' => 'POR VENCER',
                'descripcion' => ($row->nombre_comercial ?? '') . ' (vence: ' . $fecha . ')',
                'total' => (string) $row->cantidad,
            ];
        }

        return $rows;
    }

    private function reporteValorTotalInventario(): array
    {
        $valorTotal = Inventario::selectRaw('SUM(cantidad_disponible * costo_unitario_lote) as total')
            ->value('total') ?: 0.0;

        $productosConStock = Inventario::where('cantidad_disponible', '>', 0)
            ->distinct('id_producto')
            ->count('id_producto');

        return [
            [
                'categoria' => 'VALOR INVENTARIO',
                'descripcion' => 'Valor total del inventario disponible',
                'total' => sprintf('%.2f BOB', $valorTotal),
            ],
            [
                'categoria' => 'VALOR INVENTARIO',
                'descripcion' => 'Productos con stock disponible',
                'total' => (string) $productosConStock,
            ],
        ];
    }
}
