<?php

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('admins can authenticate using email and access admin dashboard', function () {
    $admin = Admin::create([
        'name' => 'Test Admin',
        'username' => 'test_admin',
        'email' => 'admin-test@example.com',
        'password' => Hash::make('admin-pass-123'),
    ]);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'admin-pass-123',
    ]);

    $response->assertRedirect(route('admin.dashboard', absolute: false));
    $this->assertAuthenticated('admin');

    $this->get(route('admin.dashboard'))->assertOk();
});
