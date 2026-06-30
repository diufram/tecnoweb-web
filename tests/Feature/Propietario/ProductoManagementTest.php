<?php

use App\Models\Producto;
use App\Models\Propietario;
use App\Models\Proveedor;
use App\Models\Usuario;

function propietarioUser(): Usuario
{
    $user = Usuario::factory()->create();

    Propietario::create([
        'id_usuario' => $user->id,
        'login' => 'owner-'.$user->id,
    ]);

    return $user;
}

test('guests cannot access propietario products', function () {
    $this->get(route('propietario.productos.index'))
        ->assertRedirect('/login');
});

test('only propietario can access products management', function () {
    $user = Usuario::factory()->create();

    Proveedor::create([
        'id_usuario' => $user->id,
        'empresa' => 'Proveedor Test',
    ]);

    $this->actingAs($user)
        ->get(route('propietario.productos.index'))
        ->assertForbidden();
});

test('propietario can list products', function () {
    Producto::create([
        'nombre_comercial' => 'Paracetamol 500mg',
        'stock_actual' => 25,
    ]);

    $this->actingAs(propietarioUser())
        ->get(route('propietario.productos.index'))
        ->assertOk();
});

test('propietario can create a product', function () {
    $this->actingAs(propietarioUser())
        ->post(route('propietario.productos.store'), [
            'nombre_comercial' => 'Ibuprofeno 400mg',
            'stock_actual' => 40,
        ])
        ->assertRedirect(route('propietario.productos.index'));

    $this->assertDatabaseHas('producto', [
        'nombre_comercial' => 'Ibuprofeno 400mg',
        'stock_actual' => 40,
    ]);
});

test('product creation validates required fields', function () {
    $this->actingAs(propietarioUser())
        ->post(route('propietario.productos.store'), [
            'nombre_comercial' => '',
            'stock_actual' => '',
        ])
        ->assertSessionHasErrors(['nombre_comercial', 'stock_actual']);
});

test('product creation validates unique name and non negative stock', function () {
    Producto::create([
        'nombre_comercial' => 'Amoxicilina 250mg',
        'stock_actual' => 10,
    ]);

    $this->actingAs(propietarioUser())
        ->post(route('propietario.productos.store'), [
            'nombre_comercial' => 'Amoxicilina 250mg',
            'stock_actual' => -1,
        ])
        ->assertSessionHasErrors(['nombre_comercial', 'stock_actual']);
});

test('propietario can update a product', function () {
    $producto = Producto::create([
        'nombre_comercial' => 'Vitamina C 1000mg',
        'stock_actual' => 100,
    ]);

    $this->actingAs(propietarioUser())
        ->patch(route('propietario.productos.update', $producto), [
            'nombre_comercial' => 'Vitamina C 500mg',
            'stock_actual' => 80,
        ])
        ->assertRedirect(route('propietario.productos.index'));

    $this->assertDatabaseHas('producto', [
        'id' => $producto->id,
        'nombre_comercial' => 'Vitamina C 500mg',
        'stock_actual' => 80,
    ]);
});

test('propietario can soft delete a product', function () {
    $producto = Producto::create([
        'nombre_comercial' => 'Suero Oral 500ml',
        'stock_actual' => 10,
    ]);

    $this->actingAs(propietarioUser())
        ->delete(route('propietario.productos.destroy', $producto))
        ->assertRedirect(route('propietario.productos.index'));

    $this->assertSoftDeleted('producto', ['id' => $producto->id]);
    expect(Producto::find($producto->id))->toBeNull();
    expect(Producto::withTrashed()->find($producto->id))->not->toBeNull();
});

test('non propietario cannot delete products', function () {
    $user = Usuario::factory()->create();

    Proveedor::create([
        'id_usuario' => $user->id,
        'empresa' => 'Proveedor Test',
    ]);

    $producto = Producto::create([
        'nombre_comercial' => 'Aspirina 500mg',
        'stock_actual' => 5,
    ]);

    $this->actingAs($user)
        ->delete(route('propietario.productos.destroy', $producto))
        ->assertForbidden();

    $this->assertNotSoftDeleted('producto', ['id' => $producto->id]);
});
