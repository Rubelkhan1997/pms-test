<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    // Create a test user
    $this->user = User::factory()->create([
        'email' => 'existing@example.com',
        'password' => bcrypt('password123'),
    ]);
});

describe('Register Page', function (): void {

    it('displays registration form', function (): void {
        $this->get(route('register'))
            ->assertStatus(200)
            ->assertViewIs('Auth.Register');
    });

    it('redirects authenticated users to dashboard', function (): void {
        $this->actingAs($this->user);

        $this->get(route('register'))
            ->assertRedirect(route('dashboard'));
    });

});

describe('Register Action', function (): void {

    it('creates new user with valid data', function (): void {
        $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasNoErrors();

        expect(User::where('email', 'newuser@example.com')->exists())->toBeTrue();
    });

    it('auto logs in user after registration', function (): void {
        $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        expect(auth()->check())->toBeTrue();
        expect(auth()->user()->email)->toBe('newuser2@example.com');
    });

    it('fails validation with duplicate email', function (): void {
        $this->post(route('register'), [
            'name' => 'Duplicate User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertSessionHasErrors('email');
    });

    it('fails validation with missing name', function (): void {
        $this->post(route('register'), [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertSessionHasErrors('name');
    });

    it('fails validation with missing email', function (): void {
        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertSessionHasErrors('email');
    });

    it('fails validation with invalid email format', function (): void {
        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertSessionHasErrors('email');
    });

    it('fails validation with missing password', function (): void {
        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ])
            ->assertSessionHasErrors('password');
    });

    it('fails validation with password mismatch', function (): void {
        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ])
            ->assertSessionHasErrors('password');
    });

    it('fails validation with short password', function (): void {
        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
            ->assertSessionHasErrors('password');
    });

    it('assigns default role to new user', function (): void {
        $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'newuser3@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'newuser3@example.com')->first();
        expect($user->role)->toBe('staff');
    });

});
