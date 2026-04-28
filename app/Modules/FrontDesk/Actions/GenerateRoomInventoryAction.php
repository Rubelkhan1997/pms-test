<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Actions;

use App\Modules\FrontDesk\Models\Property;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\FrontDesk\Models\RoomType;

class GenerateRoomInventoryAction
{
    public function execute(RoomType $roomType, int $quantity, string $startNumber = '1'): array
    {
        $rooms = [];
        $property = $roomType->property;

        for ($i = 0; $i < $quantity; $i++) {
            $number = str_pad((int) $startNumber + $i, 3, '0', STR_PAD_LEFT);
            $rooms[] = Room::create([
                'property_id'   => $property->id,
                'room_type_id' => $roomType->id,
                'number'     => $roomType->code . '-' . $number,
                'floor'      => $roomType->floor,
                'status'     => 'available',
                'cleaning_status' => 'clean',
            ]);
        }

        $property->increment('number_of_rooms', $quantity);

        return $rooms;
    }
}