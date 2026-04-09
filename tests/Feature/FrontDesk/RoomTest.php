<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\FrontDesk\Models\Hotel;
use App\Modules\FrontDesk\Models\Room;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    $this->hotel = Hotel::query()->create([
        'name' => 'Test Hotel',
        'code' => 'TST-HOTEL',
        'timezone' => 'Asia/Dhaka',
        'currency' => 'BDT',
        'email' => 'test@example.com',
        'phone' => '0123456789',
        'address' => 'Dhaka',
    ]);

    $this->room = Room::query()->create([
        'hotel_id' => $this->hotel->id,
        'number' => '101',
        'floor' => '1',
        'type' => 'Deluxe',
        'status' => 'available',
        'base_rate' => 5000,
    ]);

    Permission::firstOrCreate(['name' => 'view rooms', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'create rooms', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'edit rooms', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'delete rooms', 'guard_name' => 'web']);
});

describe('Room API - Authentication', function (): void {
    it('returns 401 for unauthenticated access to index', function (): void {
        $this->getJson('/api/v1/front-desk/rooms')
            ->assertUnauthorized();
    });

    it('returns 401 for unauthenticated access to store', function (): void {
        $this->postJson('/api/v1/front-desk/rooms', [])
            ->assertUnauthorized();
    });

    it('returns 401 for unauthenticated access to show', function (): void {
        $this->getJson('/api/v1/front-desk/rooms/1')
            ->assertUnauthorized();
    });

    it('returns 401 for unauthenticated access to update', function (): void {
        $this->putJson('/api/v1/front-desk/rooms/1', [])
            ->assertUnauthorized();
    });

    it('returns 401 for unauthenticated access to destroy', function (): void {
        $this->deleteJson('/api/v1/front-desk/rooms/1')
            ->assertUnauthorized();
    });
});

describe('Room API - Index', function (): void {
    it('returns paginated list of rooms for authorized user', function (): void {
        $this->user->givePermissionTo('view rooms');

        $this->actingAs($this->user)
            ->getJson('/api/v1/front-desk/rooms')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    'items' => ['*' => ['id', 'hotel_id', 'number', 'type', 'status', 'base_rate', 'created_at', 'updated_at']],
                    'pagination' => ['current_page', 'per_page', 'total', 'last_page'],
                ],
                'message',
            ]);
    });

    it('filters rooms by search term', function (): void {
        $this->user->givePermissionTo('view rooms');

        Room::query()->create([
            'hotel_id' => $this->hotel->id,
            'number' => 'VIP-201',
            'floor' => '2',
            'type' => 'Suite',
            'status' => 'available',
            'base_rate' => 8000,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v1/front-desk/rooms?search=VIP')
            ->assertOk()
            ->assertJsonFragment(['number' => 'VIP-201']);
    });
});

describe('Room API - Show', function (): void {
    it('returns single room for authorized user', function (): void {
        $this->user->givePermissionTo('view rooms');

        $this->actingAs($this->user)
            ->getJson("/api/v1/front-desk/rooms/{$this->room->id}")
            ->assertOk()
            ->assertJson([
                'status' => 1,
                'data' => ['id' => $this->room->id],
            ]);
    });

    it('returns 404 for non-existent room id', function (): void {
        $this->user->givePermissionTo('view rooms');

        $this->actingAs($this->user)
            ->getJson('/api/v1/front-desk/rooms/99999')
            ->assertNotFound()
            ->assertJson([
                'status' => 0,
                'data' => null,
                'message' => 'Room not found',
            ]);
    });
});

describe('Room API - Store', function (): void {
    it('creates new room for authorized user', function (): void {
        $this->user->givePermissionTo('create rooms');

        $payload = [
            'hotel_id' => $this->hotel->id,
            'number' => '102',
            'floor' => '1',
            'type' => 'Standard',
            'status' => 'available',
            'base_rate' => 3000,
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v1/front-desk/rooms', $payload)
            ->assertCreated()
            ->assertJson([
                'status' => 1,
                'data' => [
                    'number' => '102',
                    'type' => 'Standard',
                ],
                'message' => 'Room created successfully',
            ]);

        $this->assertDatabaseHas('rooms', [
            'hotel_id' => $this->hotel->id,
            'number' => '102',
        ]);
    });

    it('returns 422 on validation failure', function (): void {
        $this->user->givePermissionTo('create rooms');

        $this->actingAs($this->user)
            ->postJson('/api/v1/front-desk/rooms', [])
            ->assertUnprocessable()
            ->assertJsonStructure(['status', 'message', 'errors']);
    });
});

describe('Room API - Update', function (): void {
    it('updates existing room for authorized user', function (): void {
        $this->user->givePermissionTo('edit rooms');

        $this->actingAs($this->user)
            ->putJson("/api/v1/front-desk/rooms/{$this->room->id}", [
                'type' => 'Executive',
                'status' => 'occupied',
                'base_rate' => 6500,
            ])
            ->assertOk()
            ->assertJson([
                'status' => 1,
                'data' => [
                    'type' => 'Executive',
                    'status' => 'occupied',
                ],
                'message' => 'Room updated successfully',
            ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $this->room->id,
            'type' => 'Executive',
        ]);
    });

    it('returns 404 for non-existent room id', function (): void {
        $this->user->givePermissionTo('edit rooms');

        $this->actingAs($this->user)
            ->putJson('/api/v1/front-desk/rooms/99999', ['type' => 'Updated'])
            ->assertNotFound()
            ->assertJson([
                'status' => 0,
                'data' => null,
                'message' => 'Room not found',
            ]);
    });
});

describe('Room API - Destroy', function (): void {
    it('deletes room for authorized user', function (): void {
        $this->user->givePermissionTo('delete rooms');

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/front-desk/rooms/{$this->room->id}")
            ->assertOk()
            ->assertJson([
                'status' => 1,
                'message' => 'Room deleted successfully',
            ]);

        $this->assertSoftDeleted('rooms', [
            'id' => $this->room->id,
        ]);
    });

    it('returns 404 for non-existent room id', function (): void {
        $this->user->givePermissionTo('delete rooms');

        $this->actingAs($this->user)
            ->deleteJson('/api/v1/front-desk/rooms/99999')
            ->assertNotFound()
            ->assertJson([
                'status' => 0,
                'data' => null,
                'message' => 'Room not found',
            ]);
    });
});

describe('Room API - Authorization', function (): void {
    it('returns 403 for unauthorized user trying to view rooms', function (): void {
        $this->actingAs($this->user)
            ->getJson('/api/v1/front-desk/rooms')
            ->assertForbidden();
    });

    it('returns 403 for unauthorized user trying to create room', function (): void {
        $this->actingAs($this->user)
            ->postJson('/api/v1/front-desk/rooms', [])
            ->assertForbidden();
    });

    it('returns 403 for unauthorized user trying to update room', function (): void {
        $this->actingAs($this->user)
            ->putJson("/api/v1/front-desk/rooms/{$this->room->id}", [])
            ->assertForbidden();
    });

    it('returns 403 for unauthorized user trying to delete room', function (): void {
        $this->actingAs($this->user)
            ->deleteJson("/api/v1/front-desk/rooms/{$this->room->id}")
            ->assertForbidden();
    });
});
