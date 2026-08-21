<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
        'role' => 'user',
        'verified_at' => now(),
    ]);

    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

it('requires authentication for profile endpoints', function () {
    $this->getJson('/api/me')->assertUnauthorized();

    $this->putJson('/api/me', ['name' => 'X', 'phone' => '081234567890'])->assertUnauthorized();
});

it('returns the authenticated user profile', function () {
    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->getJson('/api/me')
        ->assertOk()
        ->assertJson([
            'user' => ['id' => $this->user->id, 'email' => 'test@example.com'],
        ]);
});

it('updates the authenticated user profile', function () {
    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->putJson('/api/me', ['name' => 'Updated Name', 'phone' => '081234567890'])
        ->assertOk()
        ->assertJson([
            'user' => ['id' => $this->user->id, 'name' => 'Updated Name'],
        ]);

    expect($this->user->fresh()->name)->toBe('Updated Name');
});

it('revokes the token on logout', function () {
    $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/logout')
        ->assertOk();

    expect($this->user->tokens()->count())->toBe(0);
});
