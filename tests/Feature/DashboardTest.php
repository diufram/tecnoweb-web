<?php

use App\Models\Cliente;
use App\Models\Propietario;
use App\Models\Proveedor;
use App\Models\Usuario;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = Usuario::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertRedirect(route('dashboard.default'));
});

test('propietario is redirected to propietario dashboard', function () {
    $user = Usuario::factory()->create();
    Propietario::create([
        'id_usuario' => $user->id,
        'login' => 'owner-'.$user->id,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('dashboard.propietario'));
});

test('proveedor is redirected to proveedor dashboard', function () {
    $user = Usuario::factory()->create();
    Proveedor::create([
        'id_usuario' => $user->id,
        'empresa' => 'Proveedor Test',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('dashboard.proveedor'));
});

test('cliente is redirected to cliente dashboard', function () {
    $user = Usuario::factory()->create();
    Cliente::create([
        'id_usuario' => $user->id,
        'linea_credito' => 1000,
        'nit_facturacion' => $user->ci_nit,
        'saldo_actual' => 0,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('dashboard.cliente'));
});
