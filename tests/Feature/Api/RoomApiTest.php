<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Hotel;
use App\Models\User;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Factories\RoomFactory;
use App\Modules\FrontDesk\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoomApiTest extends TestCase
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
    public function it_returns_paginated_rooms(): void
    {
        // Arrange
        Room::factory()->count(15)->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/front-desk/rooms');

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'number',
                        'floor',
                        'type',
                        'status',
                        'base_rate',
                    ]
                ],
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function it_creates_room_via_api(): void
    {
        // Arrange
        $payload = [
            'hotel_id' => $this->hotel->id,
            'number' => '301',
            'floor' => 3,
            'type' => 'Deluxe',
            'status' => 'available',
            'base_rate' => 150.00,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/rooms', $payload);

        // Assert
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'number' => '301',
                    'type' => 'Deluxe',
                    'base_rate' => 150.00,
                ]
            ]);

        $this->assertDatabaseHas('rooms', [
            'number' => '301',
            'hotel_id' => $this->hotel->id,
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_create(): void
    {
        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/rooms', []);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'hotel_id',
                'number',
                'type',
                'base_rate',
            ]);
    }

    #[Test]
    public function it_validates_base_rate_is_not_negative(): void
    {
        // Arrange
        $payload = [
            'hotel_id' => $this->hotel->id,
            'number' => '301',
            'type' => 'Deluxe',
            'base_rate' => -100,
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/front-desk/rooms', $payload);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors('base_rate');
    }

    #[Test]
    public function it_updates_room_via_api(): void
    {
        // Arrange
        $room = Room::factory()->create([
            'hotel_id' => $this->hotel->id,
            'base_rate' => 100,
        ]);

        $payload = [
            'base_rate' => 150,
            'status' => 'occupied',
        ];

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/v1/front-desk/rooms/{$room->id}", $payload);

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'base_rate' => 150,
                    'status' => 'occupied',
                ]
            ]);
    }

    #[Test]
    public function it_deletes_room_via_api(): void
    {
        // Arrange
        $room = Room::factory()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/v1/front-desk/rooms/{$room->id}");

        // Assert
        $response->assertOk()
            ->assertJson([
                'message' => 'Room deleted successfully.',
            ]);

        $this->assertSoftDeleted('rooms', ['id' => $room->id]);
    }

    #[Test]
    public function it_updates_room_status_via_api(): void
    {
        // Arrange
        $room = Room::factory()->create([
            'hotel_id' => $this->hotel->id,
            'status' => RoomStatus::Available,
        ]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/front-desk/rooms/{$room->id}/status", [
            'status' => 'dirty',
        ]);

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'status' => 'dirty',
                ]
            ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => RoomStatus::Dirty->value,
        ]);
    }

    #[Test]
    public function it_returns_rooms_by_hotel(): void
    {
        // Arrange
        Room::factory()->count(5)->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/front-desk/rooms/hotel/{$this->hotel->id}");

        // Assert
        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    public function it_returns_available_rooms_only(): void
    {
        // Arrange
        Room::factory()->count(3)->available()->create(['hotel_id' => $this->hotel->id]);
        Room::factory()->count(2)->occupied()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/front-desk/rooms/hotel/{$this->hotel->id}/available");

        // Assert
        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_returns_room_statistics(): void
    {
        // Arrange
        Room::factory()->count(5)->available()->create(['hotel_id' => $this->hotel->id]);
        Room::factory()->count(3)->occupied()->create(['hotel_id' => $this->hotel->id]);
        Room::factory()->count(2)->dirty()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/front-desk/rooms/hotel/{$this->hotel->id}/statistics");

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'total' => 10,
                    'available' => 5,
                    'occupied' => 3,
                    'dirty' => 2,
                ]
            ]);
    }

    #[Test]
    public function it_returns_all_floors_for_hotel(): void
    {
        // Arrange
        Room::factory()->create(['hotel_id' => $this->hotel->id, 'floor' => 1]);
        Room::factory()->create(['hotel_id' => $this->hotel->id, 'floor' => 2]);
        Room::factory()->create(['hotel_id' => $this->hotel->id, 'floor' => 3]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/front-desk/rooms/hotel/{$this->hotel->id}/floors");

        // Assert
        $response->assertOk()
            ->assertJson([
                'data' => [1, 2, 3],
            ]);
    }

    #[Test]
    public function it_returns_all_room_types_for_hotel(): void
    {
        // Arrange
        Room::factory()->create(['hotel_id' => $this->hotel->id, 'type' => 'Standard']);
        Room::factory()->create(['hotel_id' => $this->hotel->id, 'type' => 'Deluxe']);
        Room::factory()->create(['hotel_id' => $this->hotel->id, 'type' => 'Suite']);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/front-desk/rooms/hotel/{$this->hotel->id}/types");

        // Assert
        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_filters_rooms_by_status(): void
    {
        // Arrange
        Room::factory()->count(5)->available()->create(['hotel_id' => $this->hotel->id]);
        Room::factory()->count(3)->occupied()->create(['hotel_id' => $this->hotel->id]);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/front-desk/rooms?status=available');

        // Assert
        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    public function it_filters_rooms_by_type(): void
    {
        // Arrange
        Room::factory()->count(3)->create(['hotel_id' => $this->hotel->id, 'type' => 'Deluxe']);
        Room::factory()->count(2)->create(['hotel_id' => $this->hotel->id, 'type' => 'Suite']);

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/front-desk/rooms?type=Deluxe');

        // Assert
        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
