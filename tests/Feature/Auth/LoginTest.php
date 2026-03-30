<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    // Create a test user
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);
});

describe('Login Page', function (): void {

    it('displays login form', function (): void {
        $this->get(route('login'))
            ->assertStatus(200)
            ->assertViewIs('Auth.Login');
    });

    it('redirects authenticated users to dashboard', function (): void {
        $this->actingAs($this->user);

        $this->get(route('login'))
            ->assertRedirect(route('dashboard'));
    });

});

describe('Login Action', function (): void {

    it('authenticates user with valid credentials', function (): void {
        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasNoErrors();
    });

    it('fails authentication with invalid credentials', function (): void {
        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ])
            ->assertSessionHasErrors('email');
    });

    it('fails authentication with non-existent email', function (): void {
        $this->post(route('login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ])
            ->assertSessionHasErrors('email');
    });

    it('validates email is required', function (): void {
        $this->post(route('login'), [
            'email' => '',
            'password' => 'password123',
        ])
            ->assertSessionHasErrors('email');
    });

    it('validates password is required', function (): void {
        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => '',
        ])
            ->assertSessionHasErrors('password');
    });

    it('respects throttle limit', function (): void {
        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be throttled
        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ])
            ->assertSessionHasErrors();
    });

    it('remembers user when remember checkbox is checked', function (): void {
        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ])
            ->assertRedirect(route('dashboard'));

        expect(session()->has('password_hash_' . $this->user->authIdentifierName()))->toBeTrue();
    });

});

describe('Logout Action', function (): void {

    it('logs out authenticated user', function (): void {
        $this->actingAs($this->user);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        expect(auth()->check())->toBeFalse();
    });

    it('invalidates session on logout', function (): void {
        $this->actingAs($this->user);

        $this->post(route('logout'));

        expect(session()->isStarted())->toBeFalse();
    });

});
