<?php

use App\Models\Proveedor;
use App\Models\Usuario;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = Usuario::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard.default', absolute: false));
});

test('provider login ignores intended routes from another actor', function () {
    $user = Usuario::factory()->create();
    Proveedor::create([
        'id_usuario' => $user->id,
        'empresa' => 'Proveedor Test',
    ]);

    $this->get(route('propietario.productos.index'));

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard.proveedor', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = Usuario::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = Usuario::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
