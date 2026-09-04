<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('guest can view the login screen', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('INFORA');
    $response->assertSee('Masuk ke Sistem');
});

test('super admin can authenticate using username', function () {
    $user = User::factory()->create([
        'username' => 'supertester',
        'email' => 'supertester@infora.test',
        'password' => Hash::make('password'),
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'supertester',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('super admin can authenticate using email', function () {
    $user = User::factory()->create([
        'username' => 'supertester_email',
        'email' => 'supertester_email@infora.test',
        'password' => Hash::make('password'),
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'supertester_email@infora.test',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('inactive super admin cannot authenticate', function () {
    User::factory()->create([
        'username' => 'inactive_admin',
        'email' => 'inactive_admin@infora.test',
        'password' => Hash::make('password'),
        'user_type' => 'super_admin',
        'is_active' => false,
    ]);

    $response = $this->post('/login', [
        'login' => 'inactive_admin',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('super admin cannot authenticate with invalid password', function () {
    User::factory()->create([
        'username' => 'valid_user',
        'email' => 'valid_user@infora.test',
        'password' => Hash::make('password'),
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'valid_user',
        'password' => 'wrong_password',
    ]);

    $response->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('authenticated super admin can access dashboard', function () {
    $user = User::factory()->create([
        'username' => 'dashboard_tester',
        'email' => 'dashboard_tester@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Dashboard Super Administrator');
    $response->assertSee('Entitas Pengembang Platform');
});

test('guest cannot access dashboard and is redirected to login', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('non super admin user cannot access dashboard', function () {
    $guru = User::factory()->create([
        'username' => 'guru_user',
        'email' => 'guru@infora.test',
        'user_type' => 'guru',
        'is_active' => true,
    ]);

    $response = $this->actingAs($guru)->get('/dashboard');

    $response->assertForbidden();
});

test('super admin can log out', function () {
    $user = User::factory()->create([
        'username' => 'logout_tester',
        'email' => 'logout_tester@infora.test',
        'user_type' => 'super_admin',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});
