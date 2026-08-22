<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->credentials = [
        'email' => 'web-login-test@example.com',
        'password' => 'password123',
    ];

    $this->user = User::create([
        'name' => 'Web Login Test',
        'email' => $this->credentials['email'],
        'password' => Hash::make($this->credentials['password']),
        'role' => 'user',
    ]);
});

it('shows the login form with a csrf token', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('name="_token"', false);
});

it('logs in with valid credentials and redirects to the dashboard', function () {
    $this->post('/login', $this->credentials)
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($this->user);
});

it('renders the dashboard after login without view errors', function () {
    $this->post('/login', $this->credentials)
        ->assertRedirect(route('dashboard', absolute: false));

    $this->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Page Expired');
});

it('rejects wrong credentials', function () {
    $this->post('/login', [
        'email' => $this->credentials['email'],
        'password' => 'wrong-password',
    ])->assertSessionHasErrors();

    $this->assertGuest();
});

it('rejects login when csrf token does not match', function () {
    $this->withSession(['_token' => 'stale-token'])->post('/login', [
        '_token' => 'stale-token',
        'email' => $this->credentials['email'],
        'password' => $this->credentials['password'],
    ])->assertStatus(419);
});
