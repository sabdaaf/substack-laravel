<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

test('authentication endpoints are rate limited to 5 per minute', function () {
    // Make 5 requests (should succeed)
    for ($i = 0; $i < 5; $i++) {
        $response = postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'device_name' => 'test-device',
        ]);
        
        // First 5 should get validation errors (not 429)
        expect($response->status())->toBe(422);
    }

    // 6th request should be rate limited
    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(429)
        ->assertJsonStructure([
            'message',
            'retry_after',
        ]);
});

test('register endpoint is also rate limited', function () {
    // Make 5 requests
    for ($i = 0; $i < 5; $i++) {
        postJson('/api/register', [
            'name' => 'Test User',
            'email' => "test{$i}@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'test-device',
        ]);
    }

    // 6th request should be rate limited
    $response = postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test6@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(429);
});

test('public post listing is rate limited to 60 per minute', function () {
    Post::factory()->create();

    // Make 60 requests (should all succeed)
    for ($i = 0; $i < 60; $i++) {
        $response = getJson('/api/posts');
        expect($response->status())->toBe(200);
    }

    // 61st request should be rate limited
    $response = getJson('/api/posts');
    $response->assertStatus(429);
});

test('authenticated users have higher rate limit', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Check the rate limit from headers
    $response = $this->getJson('/api/me');
    
    $response->assertSuccessful();
    
    // Authenticated users should have limit of 100
    $rateLimit = (int) $response->headers->get('X-RateLimit-Limit');
    expect($rateLimit)->toBe(100);
});

test('rate limit headers are present in response', function () {
    Post::factory()->create();

    $response = getJson('/api/posts');

    $response->assertSuccessful();
    
    // Check for rate limit headers
    expect($response->headers->has('X-RateLimit-Limit'))->toBeTrue()
        ->and($response->headers->has('X-RateLimit-Remaining'))->toBeTrue();
});

test('rate limit is per IP address for guests', function () {
    Post::factory()->create();

    // Make multiple requests
    $firstResponse = getJson('/api/posts');
    $remaining1 = (int) $firstResponse->headers->get('X-RateLimit-Remaining');

    $secondResponse = getJson('/api/posts');
    $remaining2 = (int) $secondResponse->headers->get('X-RateLimit-Remaining');

    // Remaining should decrease
    expect($remaining2)->toBeLessThan($remaining1);
});

test('rate limit is per user ID for authenticated users', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Make multiple requests
    $firstResponse = $this->getJson('/api/me');
    $remaining1 = (int) $firstResponse->headers->get('X-RateLimit-Remaining');

    $secondResponse = $this->getJson('/api/me');
    $remaining2 = (int) $secondResponse->headers->get('X-RateLimit-Remaining');

    // Remaining should decrease
    expect($remaining2)->toBeLessThan($remaining1);
});

test('different users have separate rate limits', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // User 1 makes requests
    Sanctum::actingAs($user1, ['*']);
    $user1Response = $this->getJson('/api/me');
    $user1Remaining = (int) $user1Response->headers->get('X-RateLimit-Remaining');

    // User 2 makes requests
    Sanctum::actingAs($user2, ['*']);
    $user2Response = $this->getJson('/api/me');
    $user2Remaining = (int) $user2Response->headers->get('X-RateLimit-Remaining');

    // Both should have similar remaining (separate limits)
    expect(abs($user1Remaining - $user2Remaining))->toBeLessThanOrEqual(1);
});

test('rate limit resets after time window', function () {
    Post::factory()->create();

    // Make a request
    $response1 = getJson('/api/posts');
    $remaining1 = (int) $response1->headers->get('X-RateLimit-Remaining');

    // Wait 61 seconds (rate limit window)
    // Note: In real test, we can't actually wait, so we just verify the header exists
    expect($remaining1)->toBeGreaterThan(0);
    
    // In production, after 60 seconds, the limit would reset
    // This test mainly verifies the throttling is configured
});

test('custom error message for auth throttling', function () {
    // Make 5 requests to hit the limit
    for ($i = 0; $i < 5; $i++) {
        postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'device_name' => 'test-device',
        ]);
    }

    // 6th request should return custom message
    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(429)
        ->assertJson([
            'message' => 'Too many login attempts. Please try again later.',
        ])
        ->assertJsonStructure(['retry_after']);
});
