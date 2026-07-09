<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

test('user can register with valid data', function () {
    $response = postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'test-device',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

test('user cannot register with invalid email', function () {
    $response = postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'test-device',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('user cannot register with existing email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'test-device',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
        'device_name' => 'test-device',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ]);
});

test('user can login with name and password', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = postJson('/api/login', [
        'name' => 'Test User',
        'password' => 'password123',
        'device_name' => 'test-device',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ])
        ->assertJsonPath('user.name', $user->name);
});

test('user cannot login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
        'device_name' => 'test-device',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('authenticated user can access protected routes', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/me');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
        ]);
});

test('unauthenticated user cannot access protected routes', function () {
    $response = $this->getJson('/api/me');

    $response->assertUnauthorized();
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $response = postJson('/api/logout');

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'Logged out successfully',
        ]);
});

test('user model uses uuid as primary key', function () {
    $user = User::factory()->create();

    expect($user->id)->toBeString()
        ->and($user->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});
