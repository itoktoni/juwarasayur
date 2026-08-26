<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('redirects unverified user to verification notice', function () {
    $user = User::create([
        'name' => 'Verif Test',
        'email' => 'verif-test-'.uniqid().'@test.local',
        'password' => Hash::make('Password123!'),
        'type' => 'reseller',
    ]);

    $this->actingAs($user)->get('/admin/dashboard')
        ->assertRedirect(route('verification.notice'));

    $this->actingAs($user)->get('/email/verify')
        ->assertOk()
        ->assertSee('Verify your email');
});
