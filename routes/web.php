<?php

use App\Http\Controllers\Cliente\CarritoController;
use App\Http\Controllers\Cliente\PagoController as ClientePagoController;
use App\Http\Controllers\PagoFacilCallbackController;
use App\Http\Controllers\Propietario\ClienteController;
use App\Http\Controllers\Propietario\CompraController;
use App\Http\Controllers\Propietario\InventarioController;
use App\Http\Controllers\Propietario\LoteController;
use App\Http\Controllers\Propietario\ProductoController;
use App\Http\Controllers\Propietario\ProveedorController;
use App\Http\Controllers\Proveedor\CompraController as ProveedorCompraController;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Inventario;
use App\Models\PlanPago;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Venta;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    $user = request()->user();

    return redirect()->route($user->dashboardRouteName());
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('dashboard/default', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard.default');

Route::get('dashboard/propietario', function () {
    return Inertia::render('dashboard/Propietario', [
        'stats' => [
            'productos' => Producto::count(),
            'clientes' => Cliente::count(),
            'proveedores' => Proveedor::count(),
            'compras' => Compra::count(),
        ],
    ]);
})->middleware(['auth', 'verified', 'actor:propietario'])->name('dashboard.propietario');

Route::get('dashboard/proveedor', function () {
    $proveedor = request()->user()->proveedor;

    return Inertia::render('dashboard/Proveedor', [
        'stats' => [
            'solicitudes' => Compra::where('id_proveedor', $proveedor->id_usuario)->count(),
            'aprobadas' => Compra::where('id_proveedor', $proveedor->id_usuario)->where('estado', 'APROBADO')->count(),
            'contraOfertas' => Compra::where('id_proveedor', $proveedor->id_usuario)->where('estado', 'CONTRA_OFERTA')->count(),
        ],
    ]);
})->middleware(['auth', 'verified', 'actor:proveedor'])->name('dashboard.proveedor');

Route::get('dashboard/cliente', function () {
    $cliente = request()->user()->cliente;

    return Inertia::render('dashboard/Cliente', [
        'stats' => [
            'lineaCredito' => $cliente->linea_credito,
            'saldoActual' => $cliente->saldo_actual,
            'creditoDisponible' => $cliente->linea_credito - $cliente->saldo_actual,
        ],
    ]);
})->middleware(['auth', 'verified', 'actor:cliente'])->name('dashboard.cliente');

Route::post('callbacks/pagofacil', PagoFacilCallbackController::class)->name('callbacks.pagofacil');

Route::middleware(['auth', 'verified', 'actor:cliente'])
    ->prefix('cliente')
    ->name('cliente.')
    ->group(function () {
        Route::get('catalogo', function () {
            $catalogo = Inventario::query()
                ->with(['producto', 'lote'])
                ->where('cantidad_disponible', '>', 0)
                ->orderByDesc('cantidad_disponible')
                ->get()
                ->map(fn (Inventario $inventario) => [
                    'id' => $inventario->id,
                    'cantidad_disponible' => $inventario->cantidad_disponible,
                    'precio_referencial' => $inventario->costo_unitario_lote,
                    'producto' => [
                        'id' => $inventario->producto->id,
                        'nombre_comercial' => $inventario->producto->nombre_comercial,
                        'stock_actual' => $inventario->producto->stock_actual,
                    ],
                    'lote' => [
                        'id' => $inventario->lote->id,
                        'fecha_vencimiento' => $inventario->lote->fecha_vencimiento,
                    ],
                ]);

            return Inertia::render('cliente/Catalogo', [
                'catalogo' => $catalogo,
            ]);
        })->name('catalogo');

        Route::get('compras', function () {
            $clienteId = request()->user()->cliente?->id_usuario;
            abort_unless($clienteId, 403);

            $compras = Venta::query()
                ->with(['detalles.inventario.producto', 'planPago.cuotas'])
                ->where('id_cliente', $clienteId)
                ->orderByDesc('fecha')
                ->get()
                ->map(fn (Venta $venta) => [
                    'id' => $venta->id,
                    'estado_venta' => $venta->estado_venta,
                    'fecha' => $venta->fecha,
                    'total' => $venta->total,
                    'detalles' => $venta->detalles->map(fn ($detalle) => [
                        'id' => $detalle->id,
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'subtotal' => $detalle->subtotal,
                        'producto' => [
                            'nombre_comercial' => $detalle->inventario->producto->nombre_comercial,
                        ],
                    ]),
                    'plan_pago' => $venta->planPago ? [
                        'estado_plan' => $venta->planPago->estado_plan,
                        'tipo_pago' => $venta->planPago->tipo_pago,
                        'cuotas_pendientes' => $venta->planPago->cuotas
                            ->reject(fn ($cuota) => in_array(strtoupper($cuota->estado_cuota), ['PAGADA', 'PAGADO'], true))
                            ->count(),
                    ] : null,
                ]);

            return Inertia::render('cliente/MisCompras', [
                'compras' => $compras,
            ]);
        })->name('compras');

        Route::get('carrito', [CarritoController::class, 'index'])->name('carrito');
        Route::post('carrito', [CarritoController::class, 'store'])->name('carrito.store');
        Route::patch('carrito/{inventario}', [CarritoController::class, 'update'])->name('carrito.update');
        Route::delete('carrito/{inventario}', [CarritoController::class, 'destroy'])->name('carrito.destroy');
        Route::post('carrito/checkout', [CarritoController::class, 'checkout'])->name('carrito.checkout');

        Route::get('pagos', function () {
            $clienteId = request()->user()->cliente?->id_usuario;
            abort_unless($clienteId, 403);

            $planes = PlanPago::query()
                ->with(['venta', 'cuotas' => fn ($query) => $query->orderBy('nro_cuota')])
                ->whereHas('venta', fn ($query) => $query->where('id_cliente', $clienteId))
                ->orderByDesc('created_at')
                ->get()
                ->map(function (PlanPago $plan) {
                    $cuotasPagadas = $plan->cuotas->filter(fn ($cuota) => in_array(strtoupper($cuota->estado_cuota), ['PAGADA', 'PAGADO'], true));
                    $cuotasPendientes = $plan->cuotas->reject(fn ($cuota) => in_array(strtoupper($cuota->estado_cuota), ['PAGADA', 'PAGADO'], true));

                    return [
                        'id' => $plan->id,
                        'estado_plan' => $plan->estado_plan,
                        'tipo_pago' => $plan->tipo_pago,
                        'venta' => [
                            'id' => $plan->venta->id,
                            'fecha' => $plan->venta->fecha,
                            'total' => $plan->venta->total,
                        ],
                        'total_pagado' => $cuotasPagadas->sum('monto'),
                        'total_pendiente' => $cuotasPendientes->sum('monto'),
                        'proxima_cuota' => $cuotasPendientes->first() ? [
                            'id' => $cuotasPendientes->first()->id,
                            'nro_cuota' => $cuotasPendientes->first()->nro_cuota,
                            'fecha_vencimiento' => $cuotasPendientes->first()->fecha_vencimiento,
                            'monto' => $cuotasPendientes->first()->monto,
                            'estado_cuota' => $cuotasPendientes->first()->estado_cuota,
                            'id_transaccion_pago_facil' => $cuotasPendientes->first()->id_transaccion_pago_facil,
                        ] : null,
                        'cuotas' => $plan->cuotas->map(fn ($cuota) => [
                            'id' => $cuota->id,
                            'nro_cuota' => $cuota->nro_cuota,
                            'fecha_vencimiento' => $cuota->fecha_vencimiento,
                            'monto' => $cuota->monto,
                            'estado_cuota' => $cuota->estado_cuota,
                            'id_transaccion_pago_facil' => $cuota->id_transaccion_pago_facil,
                        ]),
                    ];
                });

            return Inertia::render('cliente/Pagos', [
                'planes' => $planes,
            ]);
        })->name('pagos');
        Route::post('pagos/{venta}/qr', [ClientePagoController::class, 'generarQr'])->name('pagos.qr');
    });

Route::middleware(['auth', 'verified', 'actor:propietario'])
    ->prefix('propietario')
    ->name('propietario.')
    ->group(function () {
        Route::resource('productos', ProductoController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('lotes', LoteController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);
        Route::resource('inventario', InventarioController::class)
            ->parameters(['inventario' => 'inventario'])
            ->only(['index', 'create', 'store', 'edit', 'update']);
        Route::resource('compras', CompraController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);
        Route::resource('clientes', ClienteController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('proveedores', ProveedorController::class)
            ->parameters(['proveedores' => 'proveedor'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified', 'actor:proveedor'])
    ->prefix('proveedor')
    ->name('proveedor.')
    ->group(function () {
        Route::get('solicitudes', [ProveedorCompraController::class, 'solicitudes'])->name('solicitudes');
        Route::get('contraofertas', [ProveedorCompraController::class, 'contraofertas'])->name('contraofertas');
        Route::get('compras', [ProveedorCompraController::class, 'compras'])->name('compras');
        Route::get('historial', [ProveedorCompraController::class, 'historial'])->name('historial');
        Route::get('compras/{compra}', [ProveedorCompraController::class, 'show'])->name('show');
        Route::post('compras/{compra}/responder', [ProveedorCompraController::class, 'responder'])->name('responder');
    });
