<?php

test('guests are redirected to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

test('guests can access the login page', function () {
    $this->withoutVite();

    $response = $this->get('/login');

    $response->assertStatus(200);
});