<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'ci_nit' => '12345678',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'direccion' => 'Av. Siempre Viva 123',
        'telefono' => '70000000',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
