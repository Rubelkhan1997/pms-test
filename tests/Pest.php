<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to all of the "expect()" functions provided by
| Expectation class. You can customize the expectations available for your tests.
|
*/

expect()->extend('toBeValidUuid', function () {
    return $this->toBeString()
        ->and(\Ramsey\Uuid\Uuid::isValid($this->value))->toBeTrue();
});

expect()->extend('toBeValidJson', function () {
    json_decode($this->value);
    return $this->toBeString()
        ->and(json_last_error())->toBe(JSON_ERROR_NONE);
});

expect()->extend('toBeFuture', function () {
    return $this->value->isFuture();
});

expect()->extend('toBePast', function () {
    return $this->value->isPast();
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very convenient out of the box, there may be things you want to extend or
| customize. You can add your own custom functions here. These functions can be used in
| your tests to reduce duplication and improve readability.
|
*/

/**
 * Create a test user with specified role
 */
function createUserWithRole(string $role, array $attributes = []): \App\Models\User
{
    $user = \App\Models\User::factory()->create($attributes);
    $user->assignRole($role);
    return $user;
}

/**
 * Get authentication token for API testing
 */
function getApiToken(\App\Models\User $user): string
{
    return $user->createToken('test-token')->plainTextToken;
}

/**
 * Mock the current hotel for multi-tenancy
 */
function mockCurrentHotel(\App\Models\Hotel $hotel): void
{
    \Illuminate\Support\Facades\Session::put('current_hotel_id', $hotel->id);
}
