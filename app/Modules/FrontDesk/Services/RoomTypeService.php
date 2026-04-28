<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Actions\GenerateRoomInventoryAction;
use App\Modules\FrontDesk\Data\RoomTypeData;
use App\Modules\FrontDesk\Models\Property;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\FrontDesk\Models\RoomType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

readonly class RoomTypeService
{
    public function __construct(
        private GenerateRoomInventoryAction $generateRoomsAction,
    ) {}

    public function paginate(int $propertyId, int $perPage = 20): LengthAwarePaginator
    {
        return RoomType::where('property_id', $propertyId)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): RoomType
    {
        return RoomType::findOrFail($id);
    }

    public function create(RoomTypeData $data): RoomType
    {
        return RoomType::create([
            'property_id'     => $data->propertyId,
            'name'          => $data->name,
            'code'          => $data->code,
            'type'         => $data->type,
            'floor'         => $data->floor,
            'max_occupancy' => $data->maxOccupancy,
            'adult_occupancy' => $data->adultOccupancy,
            'num_bedrooms'  => $data->numBedrooms,
            'num_bathrooms' => $data->numBathrooms,
            'area_sqm'     => $data->areaSqm,
            'bed_types'    => $data->bedTypes,
            'base_rate'    => $data->baseRate,
            'amenities'    => $data->amenities,
        ]);
    }

    public function generateRooms(RoomType $roomType, int $quantity, string $startNumber = '1'): Collection
    {
        return new Collection(
            $this->generateRoomsAction->execute($roomType, $quantity, $startNumber)
        );
    }

    public function delete(RoomType $roomType): void
    {
        $roomCount = $roomType->rooms()->count();
        if ($roomCount > 0) {
            $roomType->rooms()->delete();
            $roomType->property->decrement('number_of_rooms', $roomCount);
        }
        $roomType->delete();
    }
}