<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Actions\CreateHotelAction;
use App\Modules\FrontDesk\Data\HotelData;
use App\Modules\FrontDesk\Models\Hotel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

readonly class HotelService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private CreateHotelAction $createAction)
    {
    }

    /**
     * Return a paginated list.
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = Hotel::query()->latest('id');

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage, page: $page);
    }

    /**
     * Find a hotel by ID.
     */
    public function find(int $id): Hotel
    {
        return Hotel::query()->find($id); 
    }

    /**
     * Store a new record.
     */
    public function create(HotelData $data): Hotel
    {
        return DB::transaction(function () use ($data): Hotel {
            return ($this->createAction)($data->toArray());
        });
    }

    /**
     * Update an existing record.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, HotelData $data): Hotel
    {
        return DB::transaction(function () use ($id, $data): Hotel {
            $hotel = $this->find($id);
            $hotel->update($data->toArray());
            return $hotel;
        });
    }

    /**
     * Delete a hotel.
     */
    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $hotel = $this->find($id);
            $hotel->delete();
        });
    }
}
