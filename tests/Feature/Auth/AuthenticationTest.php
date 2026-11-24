<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('opens the login and registration pages', function () {
    $this->get('/login')->assertOk();
    $this->get('/register')->assertOk();
});

it('registers a user and logs them in', function () {
    $this->post('/register', [
        'name' => 'Night Reader',
        'email' => 'reader@example.com',
        'password' => 'a-long-password',
        'password_confirmation' => 'a-long-password',
    ])->assertRedirect('/dashboard');

    $user = User::where('email', 'reader@example.com')->firstOrFail();
    $this->assertAuthenticatedAs($user);
    expect(Hash::check('a-long-password', $user->password))->toBeTrue();
});

it('logs in with correct credentials', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

it('rejects an incorrect password', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'incorrect'])->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('logs out', function () {
    $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});
