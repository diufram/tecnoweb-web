<?php

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Propietario;
use App\Models\Proveedor;
use App\Models\Usuario;

function propietarioUserForStockManagement(): Usuario
{
    $user = Usuario::factory()->create();

    Propietario::create([
        'id_usuario' => $user->id,
        'login' => 'owner-stock-'.$user->id,
    ]);

    return $user;
}

function proveedorForStockManagement(): Proveedor
{
    $user = Usuario::factory()->create();

    return Proveedor::create([
        'id_usuario' => $user->id,
        'empresa' => 'Proveedor Stock SRL',
    ]);
}

function productoForStockManagement(array $attributes = []): Producto
{
    return Producto::create(array_merge([
        'nombre_comercial' => 'Producto Stock Test',
        'stock_actual' => 0,
    ], $attributes));
}

function loteForStockManagement(array $attributes = []): Lote
{
    return Lote::create(array_merge([
        'codigo_lote' => 'LOTE-STOCK-TEST',
        'fecha_ingreso' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonth()->toDateString(),
    ], $attributes));
}

test('propietario can list compras and inventario', function () {
    $user = propietarioUserForStockManagement();

    $this->actingAs($user)->get(route('propietario.compras.index'))->assertOk();
    $this->actingAs($user)->get(route('propietario.inventario.index'))->assertOk();
    $this->actingAs($user)->get(route('propietario.lotes.index'))->assertOk();
});

test('propietario can create a compra with calculated total', function () {
    $owner = propietarioUserForStockManagement();
    $proveedor = proveedorForStockManagement();
    $producto = productoForStockManagement();

    $this->actingAs($owner)
        ->post(route('propietario.compras.store'), [
            'estado' => 'SOLICITUD',
            'fecha_emision' => now()->toDateString(),
            'observaciones' => 'Compra de reposicion',
            'id_proveedor' => $proveedor->id_usuario,
            'id_producto' => $producto->id,
            'cantidad' => 10,
            'precio_unitario' => 5.5,
        ])
        ->assertRedirect(route('propietario.compras.index'));

    $this->assertDatabaseHas('compra', [
        'estado' => 'SOLICITUD',
        'monto_total' => 55,
        'id_propietario' => $owner->propietario->id_usuario,
        'id_proveedor' => $proveedor->id_usuario,
    ]);
    $this->assertDatabaseHas('detalle_compra', [
        'id_producto' => $producto->id,
        'cantidad' => 10,
        'subtotal' => 55,
    ]);
});

test('compra validates required fields', function () {
    $this->actingAs(propietarioUserForStockManagement())
        ->post(route('propietario.compras.store'), [])
        ->assertSessionHasErrors(['estado', 'fecha_emision', 'observaciones', 'id_proveedor', 'id_producto', 'cantidad', 'precio_unitario']);
});

test('propietario can update a compra and replace detail', function () {
    $owner = propietarioUserForStockManagement();
    $proveedor = proveedorForStockManagement();
    $producto = productoForStockManagement();
    $otroProducto = productoForStockManagement(['nombre_comercial' => 'Otro Producto Stock']);

    $compra = Compra::create([
        'estado' => 'SOLICITUD',
        'fecha_emision' => now()->toDateString(),
        'monto_total' => 10,
        'observaciones' => 'Inicial',
        'id_propietario' => $owner->propietario->id_usuario,
        'id_proveedor' => $proveedor->id_usuario,
    ]);
    DetalleCompra::create([
        'id_compra' => $compra->id,
        'id_producto' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 10,
        'subtotal' => 10,
    ]);

    $this->actingAs($owner)
        ->patch(route('propietario.compras.update', $compra), [
            'estado' => 'APROBADO',
            'fecha_emision' => now()->toDateString(),
            'observaciones' => 'Actualizada',
            'id_proveedor' => $proveedor->id_usuario,
            'id_producto' => $otroProducto->id,
            'cantidad' => 4,
            'precio_unitario' => 7,
        ])
        ->assertRedirect(route('propietario.compras.index'));

    $this->assertDatabaseHas('compra', [
        'id' => $compra->id,
        'estado' => 'APROBADO',
        'monto_total' => 28,
    ]);
    $this->assertDatabaseHas('detalle_compra', [
        'id_compra' => $compra->id,
        'id_producto' => $otroProducto->id,
        'cantidad' => 4,
        'subtotal' => 28,
    ]);
});

test('propietario can create a lote', function () {
    $this->actingAs(propietarioUserForStockManagement())
        ->post(route('propietario.lotes.store'), [
            'codigo_lote' => 'LOTE-STOCK-001',
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->addMonth()->toDateString(),
        ])
        ->assertRedirect(route('propietario.lotes.index'));

    $this->assertDatabaseHas('lote', ['codigo_lote' => 'LOTE-STOCK-001']);
});

test('lote validates required fields and date order', function () {
    $this->actingAs(propietarioUserForStockManagement())
        ->post(route('propietario.lotes.store'), [
            'codigo_lote' => 'AB',
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => now()->subDay()->toDateString(),
        ])
        ->assertSessionHasErrors(['codigo_lote', 'fecha_vencimiento']);
});

test('propietario can create inventario selecting an existing lote and sync product stock', function () {
    $producto = productoForStockManagement();
    $lote = loteForStockManagement(['codigo_lote' => 'LOTE-STOCK-001']);

    $this->actingAs(propietarioUserForStockManagement())
        ->post(route('propietario.inventario.store'), [
            'id_producto' => $producto->id,
            'id_lote' => $lote->id,
            'cantidad_disponible' => 25,
            'costo_unitario_lote' => 3.25,
        ])
        ->assertRedirect(route('propietario.inventario.index'));

    $this->assertDatabaseHas('inventario', [
        'id_producto' => $producto->id,
        'id_lote' => $lote->id,
        'cantidad_disponible' => 25,
    ]);
    expect($producto->fresh()->stock_actual)->toBe(25);
});

test('inventario validates required fields', function () {
    $producto = productoForStockManagement();

    $this->actingAs(propietarioUserForStockManagement())
        ->post(route('propietario.inventario.store'), [
            'id_producto' => $producto->id,
            'cantidad_disponible' => -1,
            'costo_unitario_lote' => -1,
        ])
        ->assertSessionHasErrors(['id_lote', 'cantidad_disponible', 'costo_unitario_lote']);
});

test('propietario can update inventario and resync stock', function () {
    $producto = productoForStockManagement();
    $lote = loteForStockManagement(['codigo_lote' => 'LOTE-STOCK-003']);
    $nuevoLote = loteForStockManagement(['codigo_lote' => 'LOTE-STOCK-003-A']);
    $inventario = Inventario::create([
        'id_producto' => $producto->id,
        'id_lote' => $lote->id,
        'cantidad_disponible' => 10,
        'costo_unitario_lote' => 2,
    ]);

    $this->actingAs(propietarioUserForStockManagement())
        ->patch(route('propietario.inventario.update', $inventario), [
            'id_producto' => $producto->id,
            'id_lote' => $nuevoLote->id,
            'cantidad_disponible' => 40,
            'costo_unitario_lote' => 4.5,
        ])
        ->assertRedirect(route('propietario.inventario.index'));

    $this->assertDatabaseHas('lote', ['id' => $lote->id, 'codigo_lote' => 'LOTE-STOCK-003']);
    $this->assertDatabaseHas('inventario', ['id' => $inventario->id, 'id_lote' => $nuevoLote->id, 'cantidad_disponible' => 40]);
    expect($producto->fresh()->stock_actual)->toBe(40);
});
