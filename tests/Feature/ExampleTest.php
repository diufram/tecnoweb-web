<?php

test('guests are redirected from home to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login', absolute: false));
});
