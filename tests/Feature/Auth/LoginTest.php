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
    ]);
});

it('returns an access token on successful login', function () {
    $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ])->assertOk()
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'user' => ['id', 'name', 'email', 'role'],
        ])
        ->assertJson([
            'token_type' => 'Bearer',
            'user' => ['email' => 'test@example.com'],
        ]);
});

it('returns 401 on wrong credentials', function () {
    $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ])->assertUnauthorized();
});

it('returns 422 on missing fields', function () {
    $this->postJson('/api/login', [
        'email' => '',
        'password' => '',
    ])->assertUnprocessable();
});

it('requires authentication for the profile endpoint', function () {
    $this->getJson('/api/me')->assertUnauthorized();
});
