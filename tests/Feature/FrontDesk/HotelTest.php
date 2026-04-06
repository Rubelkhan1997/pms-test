<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\FrontDesk\Models\Hotel;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    // Create a test user
    $this->user = User::factory()->create();

    // Create a test hotel
    $this->hotel = Hotel::factory()->create([
        'name' => 'Test Hotel',
        'code' => 'TEST001',
        'timezone' => 'Asia/Dhaka',
        'currency' => 'BDT',
        'email' => 'test@hotel.com',
        'phone' => '+8801234567890',
        'address' => '123 Test Street, Dhaka',
    ]);

    // Create hotel permissions if they don't exist
    Permission::firstOrCreate(['name' => 'view hotels', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'create hotels', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'edit hotels', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'delete hotels', 'guard_name' => 'web']);
});

describe('Hotel API - Authentication', function (): void {

    it('returns 401 for unauthenticated access to index', function (): void {
        $this->getJson('/api/v1/front-desk/hotels')
            ->assertUnauthorized();
    });

    it('returns 401 for unauthenticated access to store', function (): void {
        $this->postJson('/api/v1/front-desk/hotels', [])
            ->assertUnauthorized();
    });

    it('returns 401 for unauthenticated access to show', function (): void {
        $this->getJson('/api/v1/front-desk/hotels/1')
            ->assertUnauthorized();
    });

    it('returns 401 for unauthenticated access to update', function (): void {
        $this->putJson('/api/v1/front-desk/hotels/1', [])
            ->assertUnauthorized();
    });

    it('returns 401 for unauthenticated access to destroy', function (): void {
        $this->deleteJson('/api/v1/front-desk/hotels/1')
            ->assertUnauthorized();
    });

});

describe('Hotel API - Index', function (): void {

    it('returns paginated list of hotels for authorized user', function (): void {
        $this->user->givePermissionTo('view hotels');

        $this->actingAs($this->user)
            ->getJson('/api/v1/front-desk/hotels')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    'items' => ['*' => ['id', 'name', 'code', 'email', 'phone', 'address', 'created_at', 'updated_at']],
                    'pagination' => ['current_page', 'per_page', 'total', 'last_page'],
                ],
                'message',
            ]);
    });

    it('filters hotels by search term', function (): void {
        $this->user->givePermissionTo('view hotels');

        Hotel::factory()->create([
            'name' => 'Unique Hotel Name',
            'code' => 'UNQ001',
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v1/front-desk/hotels?search=Unique')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Unique Hotel Name']);
    });

});

describe('Hotel API - Show', function (): void {

    it('returns single hotel for authorized user', function (): void {
        $this->user->givePermissionTo('view hotels');

        $this->actingAs($this->user)
            ->getJson("/api/v1/front-desk/hotels/{$this->hotel->id}")
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => ['id', 'name', 'code', 'email', 'phone', 'address', 'created_at', 'updated_at'],
                'message',
            ])
            ->assertJson([
                'data' => ['id' => $this->hotel->id],
            ]);
    });

    it('returns 404 for non-existent hotel id', function (): void {
        $this->user->givePermissionTo('view hotels');

        $this->actingAs($this->user)
            ->getJson('/api/v1/front-desk/hotels/99999')
            ->assertNotFound()
            ->assertJson([
                'status' => 0,
                'data' => null,
                'message' => 'Hotel not found',
            ]);
    });

});

describe('Hotel API - Store', function (): void {

    it('creates new hotel for authorized user', function (): void {
        $this->user->givePermissionTo('create hotels');

        $payload = [
            'name' => 'New Hotel',
            'code' => 'NEW001',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'email' => 'new@hotel.com',
            'phone' => '+1234567890',
            'address' => '456 New Avenue',
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v1/front-desk/hotels', $payload)
            ->assertCreated()
            ->assertJson([
                'status' => 1,
                'data' => [
                    'name' => 'New Hotel',
                    'code' => 'NEW001',
                ],
                'message' => 'Hotel created successfully',
            ]);

        $this->assertDatabaseHas('hotels', [
            'name' => 'New Hotel',
            'code' => 'NEW001',
        ]);
    });

    it('returns 422 on validation failure', function (): void {
        $this->user->givePermissionTo('create hotels');

        $this->actingAs($this->user)
            ->postJson('/api/v1/front-desk/hotels', [
                'name' => '',
                'code' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['status', 'message', 'errors']);
    });

});

describe('Hotel API - Update', function (): void {

    it('updates existing hotel for authorized user', function (): void {
        $this->user->givePermissionTo('edit hotels');

        $payload = [
            'name' => 'Updated Hotel Name',
            'email' => 'updated@hotel.com',
        ];

        $this->actingAs($this->user)
            ->putJson("/api/v1/front-desk/hotels/{$this->hotel->id}", $payload)
            ->assertOk()
            ->assertJson([
                'status' => 1,
                'data' => [
                    'name' => 'Updated Hotel Name',
                    'email' => 'updated@hotel.com',
                ],
                'message' => 'Hotel updated successfully',
            ]);

        $this->assertDatabaseHas('hotels', [
            'id' => $this->hotel->id,
            'name' => 'Updated Hotel Name',
        ]);
    });

    it('returns 404 for non-existent hotel id', function (): void {
        $this->user->givePermissionTo('edit hotels');

        $this->actingAs($this->user)
            ->putJson('/api/v1/front-desk/hotels/99999', [
                'name' => 'Updated Hotel',
            ])
            ->assertNotFound()
            ->assertJson([
                'status' => 0,
                'data' => null,
                'message' => 'Hotel not found',
            ]);
    });

});

describe('Hotel API - Destroy', function (): void {

    it('deletes hotel for authorized user', function (): void {
        $this->user->givePermissionTo('delete hotels');

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/front-desk/hotels/{$this->hotel->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('hotels', [
            'id' => $this->hotel->id,
        ]);
    });

    it('returns 404 for non-existent hotel id', function (): void {
        $this->user->givePermissionTo('delete hotels');

        $this->actingAs($this->user)
            ->deleteJson('/api/v1/front-desk/hotels/99999')
            ->assertNotFound()
            ->assertJson([
                'status' => 0,
                'data' => null,
                'message' => 'Hotel not found',
            ]);
    });

});

describe('Hotel API - Authorization', function (): void {

    it('returns 403 for unauthorized user trying to view hotels', function (): void {
        $this->actingAs($this->user)
            ->getJson('/api/v1/front-desk/hotels')
            ->assertForbidden();
    });

    it('returns 403 for unauthorized user trying to create hotel', function (): void {
        $this->actingAs($this->user)
            ->postJson('/api/v1/front-desk/hotels', [])
            ->assertForbidden();
    });

    it('returns 403 for unauthorized user trying to update hotel', function (): void {
        $this->actingAs($this->user)
            ->putJson("/api/v1/front-desk/hotels/{$this->hotel->id}", [])
            ->assertForbidden();
    });

    it('returns 403 for unauthorized user trying to delete hotel', function (): void {
        $this->actingAs($this->user)
            ->deleteJson("/api/v1/front-desk/hotels/{$this->hotel->id}")
            ->assertForbidden();
    });

});
