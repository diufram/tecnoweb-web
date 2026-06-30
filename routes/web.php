<?php

use App\Http\Controllers\Propietario\ProductoController;
use App\Http\Controllers\Propietario\ClienteController;
use App\Http\Controllers\Propietario\ProveedorController;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
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

Route::middleware(['auth', 'verified', 'actor:propietario'])
    ->prefix('propietario')
    ->name('propietario.')
    ->group(function () {
        Route::resource('productos', ProductoController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);
        Route::resource('clientes', ClienteController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);
        Route::resource('proveedores', ProveedorController::class)
            ->parameters(['proveedores' => 'proveedor'])
            ->only(['index', 'create', 'store', 'edit', 'update']);
    });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
