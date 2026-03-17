<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Hotel;
use App\Models\User;
use App\Modules\Guest\Factories\GuestProfileFactory;
use App\Modules\Guest\Models\GuestProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GuestProfileApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seedRolesAndPermissions();
        $this->user = $this->createUserWithRole('front_desk');
        $this->token = $this->user->createToken('api-token')->plainTextToken;
        $this->hotel = Hotel::factory()->create(['is_active' => true]);
    }

    #[Test]
    public function it_returns_paginated_guest_profiles(): void
    {
        // Arrange
        GuestProfile::factory()->count(15)->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/guests/profiles');

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'reference',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'status',
                    ]
                ],
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function it_creates_guest_profile_via_api(): void
    {
        // Arrange
        $payload = [
            'hotel_id' => $this->hotel->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'status' => 'active',
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/guests/profiles', $payload);

        // Assert
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@example.com',
                ]
            ]);

        $this->assertDatabaseHas('guest_profiles', [
            'email' => 'john.doe@example.com',
            'hotel_id' => $this->hotel->id,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_create(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/guests/profiles', []);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'email',
            ]);
    }

    #[Test]
    public function it_validates_email_format(): void
    {
        // Arrange
        $payload = [
            'hotel_id' => $this->hotel->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/guests/profiles', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    #[Test]
    public function it_updates_guest_profile_via_api(): void
    {
        // Arrange
        $guest = GuestProfile::factory()->create([
            'hotel_id' => $this->hotel->id,
            'first_name' => 'John',
        ]);

        $payload = [
            'first_name' => 'Jane',
            'email' => 'jane@example.com',
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/v1/guests/profiles/{$guest->id}", $payload);

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'first_name' => 'Jane',
                    'email' => 'jane@example.com',
                ]
            ]);
    }

    #[Test]
    public function it_deletes_guest_profile_via_api(): void
    {
        // Arrange
        $guest = GuestProfile::factory()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/v1/guests/profiles/{$guest->id}");

        // Assert
        $response->assertOk()
            ->assertJson([
                'message' => 'Guest profile deleted successfully.',
            ]);

        $this->assertSoftDeleted('guest_profiles', ['id' => $guest->id]);
    }

    #[Test]
    public function it_searches_guests_by_name(): void
    {
        // Arrange
        GuestProfile::factory()->create([
            'hotel_id' => $this->hotel->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        GuestProfile::factory()->create([
            'hotel_id' => $this->hotel->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/guests/profiles/search?search=john');

        // Assert
        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function it_searches_guests_by_email(): void
    {
        // Arrange
        GuestProfile::factory()->create([
            'hotel_id' => $this->hotel->id,
            'email' => 'john@example.com',
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/guests/profiles/search?search=john@example.com');

        // Assert
        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function it_returns_vip_guests(): void
    {
        // Arrange
        GuestProfile::factory()->create([
            'hotel_id' => $this->hotel->id,
            'status' => 'vip',
            'meta' => ['vip' => true],
        ]);

        GuestProfile::factory()->create([
            'hotel_id' => $this->hotel->id,
            'status' => 'active',
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/guests/profiles/vip');

        // Assert
        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function it_checks_if_email_exists(): void
    {
        // Arrange
        GuestProfile::factory()->create([
            'hotel_id' => $this->hotel->id,
            'email' => 'exists@example.com',
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/guests/profiles/check-email', [
            'email' => 'exists@example.com',
            'hotel_id' => $this->hotel->id,
        ]);

        // Assert
        $response->assertOk()
            ->assertJson(['exists' => true]);
    }

    #[Test]
    public function it_returns_false_when_email_does_not_exist(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/guests/profiles/check-email', [
            'email' => 'notexists@example.com',
            'hotel_id' => $this->hotel->id,
        ]);

        // Assert
        $response->assertOk()
            ->assertJson(['exists' => false]);
    }
}
