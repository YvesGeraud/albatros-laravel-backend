<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('login returns a bearer token that grants access to admin routes', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('secret123'),
    ]);

    $login = $this->postJson('/api/v1/login', [
        'email' => 'admin@example.com',
        'password' => 'secret123',
    ]);

    $login->assertOk();
    $token = $login->json('token');
    expect($token)->not->toBeEmpty();
    expect($login->json('user.id'))->toBe($user->id);

    // No token / no session at all -> rejected.
    $this->getJson('/api/v1/admin/categories')->assertStatus(401);

    // Bearer token from login -> accepted.
    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/admin/categories')
        ->assertOk();
});

test('login rejects wrong credentials', function () {
    User::factory()->create(['email' => 'admin@example.com', 'password' => bcrypt('secret123')]);

    $this->postJson('/api/v1/login', [
        'email' => 'admin@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(422);
});

test('logout revokes the current token', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);
    $token = $user->createToken('admin-panel')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/logout')
        ->assertNoContent();

    $this->assertDatabaseCount('personal_access_tokens', 0);

    // Sanctum's guard caches the resolved user on itself for the lifetime
    // of the request; within a single test that means it must be reset
    // explicitly before re-checking auth with the now-revoked token,
    // otherwise the previous (still-authenticated) resolution lingers —
    // a quirk of reusing one application instance across test requests,
    // not something that happens between real, separate HTTP requests.
    Auth::forgetGuards();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/admin/categories')
        ->assertStatus(401);
});

test('tokens expire after the configured lifetime', function () {
    $user = User::factory()->create();
    $token = $user->createToken('admin-panel')->plainTextToken;

    $this->travel(config('sanctum.expiration') + 1)->minutes();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/admin/categories')
        ->assertStatus(401);
});

test('login prunes the user expired tokens', function () {
    $user = User::factory()->create(['email' => 'admin@example.com', 'password' => bcrypt('secret123')]);
    $user->createToken('admin-panel');

    $this->travel(config('sanctum.expiration') + 1)->minutes();

    $this->postJson('/api/v1/login', ['email' => 'admin@example.com', 'password' => 'secret123'])->assertOk();

    expect($user->tokens()->count())->toBe(1);
});

test('login is rate limited after 5 attempts per minute', function () {
    User::factory()->create(['email' => 'admin@example.com', 'password' => bcrypt('secret123')]);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/login', ['email' => 'admin@example.com', 'password' => 'wrong'])->assertStatus(422);
    }

    $this->postJson('/api/v1/login', ['email' => 'admin@example.com', 'password' => 'secret123'])->assertStatus(429);
});

test('tokens idle longer than the idle timeout are rejected', function () {
    $user = User::factory()->create();
    $token = $user->createToken('admin-panel')->plainTextToken;

    $this->travel(config('sanctum.idle_timeout') + 1)->minutes();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/admin/categories')
        ->assertStatus(401);
});

test('regular activity keeps the token alive past the idle timeout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('admin-panel')->plainTextToken;
    $idle = config('sanctum.idle_timeout');

    // Two requests spaced just under the idle limit add up to more than it.
    foreach ([$idle - 1, $idle - 1] as $minutes) {
        $this->travel($minutes)->minutes();
        Auth::forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/categories')
            ->assertOk();
    }
});
