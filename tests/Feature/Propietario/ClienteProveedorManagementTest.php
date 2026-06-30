<?php

use App\Models\Cliente;
use App\Models\Propietario;
use App\Models\Proveedor;
use App\Models\Usuario;

function propietarioUserForActorManagement(): Usuario
{
    $user = Usuario::factory()->create();

    Propietario::create([
        'id_usuario' => $user->id,
        'login' => 'owner-'.$user->id,
    ]);

    return $user;
}

test('propietario can list clientes and proveedores', function () {
    $user = propietarioUserForActorManagement();

    $this->actingAs($user)->get(route('propietario.clientes.index'))->assertOk();
    $this->actingAs($user)->get(route('propietario.proveedores.index'))->assertOk();
});

test('non propietario cannot access clientes and proveedores management', function () {
    $user = Usuario::factory()->create();

    Proveedor::create([
        'id_usuario' => $user->id,
        'empresa' => 'Proveedor Test',
    ]);

    $this->actingAs($user)->get(route('propietario.clientes.index'))->assertForbidden();
    $this->actingAs($user)->get(route('propietario.proveedores.index'))->assertForbidden();
});

test('propietario can create a cliente', function () {
    $this->actingAs(propietarioUserForActorManagement())
        ->post(route('propietario.clientes.store'), [
            'ci_nit' => '90010001',
            'nombre' => 'Cliente Nuevo',
            'email' => 'cliente.nuevo@example.com',
            'direccion' => 'Av. Cliente 123',
            'telefono' => '+591 70000001',
            'password' => '123123123',
            'password_confirmation' => '123123123',
            'linea_credito' => 5000,
            'nit_facturacion' => '90010001',
            'saldo_actual' => 0,
        ])
        ->assertRedirect(route('propietario.clientes.index'));

    $usuario = Usuario::where('ci_nit', '90010001')->firstOrFail();

    $this->assertDatabaseHas('cliente', [
        'id_usuario' => $usuario->id,
        'nit_facturacion' => '90010001',
    ]);
});

test('cliente creation validates required fields', function () {
    $this->actingAs(propietarioUserForActorManagement())
        ->post(route('propietario.clientes.store'), [])
        ->assertSessionHasErrors(['ci_nit', 'nombre', 'email', 'direccion', 'telefono', 'password', 'linea_credito', 'nit_facturacion', 'saldo_actual']);
});

test('propietario can update a cliente', function () {
    $usuario = Usuario::factory()->create([
        'ci_nit' => '90010002',
        'email' => 'cliente.editar@example.com',
    ]);

    $cliente = Cliente::create([
        'id_usuario' => $usuario->id,
        'linea_credito' => 1000,
        'nit_facturacion' => '90010002',
        'saldo_actual' => 100,
    ]);

    $this->actingAs(propietarioUserForActorManagement())
        ->patch(route('propietario.clientes.update', $cliente), [
            'ci_nit' => '90010002',
            'nombre' => 'Cliente Editado',
            'email' => 'cliente.editar@example.com',
            'direccion' => 'Av. Editada 456',
            'telefono' => '+591 70000002',
            'password' => '',
            'password_confirmation' => '',
            'linea_credito' => 7000,
            'nit_facturacion' => '90010002-A',
            'saldo_actual' => 300,
        ])
        ->assertRedirect(route('propietario.clientes.index'));

    $this->assertDatabaseHas('usuario', [
        'id' => $usuario->id,
        'nombre' => 'Cliente Editado',
        'direccion' => 'Av. Editada 456',
    ]);
    $this->assertDatabaseHas('cliente', [
        'id_usuario' => $usuario->id,
        'nit_facturacion' => '90010002-A',
    ]);
});

test('propietario can create a proveedor', function () {
    $this->actingAs(propietarioUserForActorManagement())
        ->post(route('propietario.proveedores.store'), [
            'ci_nit' => '90020001',
            'nombre' => 'Responsable Proveedor',
            'email' => 'proveedor.nuevo@example.com',
            'direccion' => 'Av. Proveedor 123',
            'telefono' => '+591 71000001',
            'password' => '123123123',
            'password_confirmation' => '123123123',
            'empresa' => 'Proveedor Nuevo SRL',
        ])
        ->assertRedirect(route('propietario.proveedores.index'));

    $usuario = Usuario::where('ci_nit', '90020001')->firstOrFail();

    $this->assertDatabaseHas('proveedor', [
        'id_usuario' => $usuario->id,
        'empresa' => 'Proveedor Nuevo SRL',
    ]);
});

test('proveedor creation validates required fields', function () {
    $this->actingAs(propietarioUserForActorManagement())
        ->post(route('propietario.proveedores.store'), [])
        ->assertSessionHasErrors(['ci_nit', 'nombre', 'email', 'direccion', 'telefono', 'password', 'empresa']);
});

test('propietario can update a proveedor', function () {
    $usuario = Usuario::factory()->create([
        'ci_nit' => '90020002',
        'email' => 'proveedor.editar@example.com',
    ]);

    $proveedor = Proveedor::create([
        'id_usuario' => $usuario->id,
        'empresa' => 'Proveedor Original',
    ]);

    $this->actingAs(propietarioUserForActorManagement())
        ->patch(route('propietario.proveedores.update', $proveedor), [
            'ci_nit' => '90020002',
            'nombre' => 'Proveedor Editado',
            'email' => 'proveedor.editar@example.com',
            'direccion' => 'Av. Proveedor Editado',
            'telefono' => '+591 71000002',
            'password' => '',
            'password_confirmation' => '',
            'empresa' => 'Proveedor Editado SRL',
        ])
        ->assertRedirect(route('propietario.proveedores.index'));

    $this->assertDatabaseHas('usuario', [
        'id' => $usuario->id,
        'nombre' => 'Proveedor Editado',
    ]);
    $this->assertDatabaseHas('proveedor', [
        'id_usuario' => $usuario->id,
        'empresa' => 'Proveedor Editado SRL',
    ]);
});
