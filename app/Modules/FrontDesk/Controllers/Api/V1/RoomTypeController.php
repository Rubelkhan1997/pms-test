<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Data\RoomTypeData;
use App\Modules\FrontDesk\Requests\StoreRoomTypeRequest;
use App\Modules\FrontDesk\Resources\RoomTypeResource;
use App\Modules\FrontDesk\Services\RoomTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function __construct(
        private readonly RoomTypeService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $propertyId = $request->input('property_id');
        $roomTypes = $this->service->paginate(
            (int) $propertyId,
            (int) $request->get('per_page', 20),
        );

        return response()->json([
            'status'  => 1,
            'data'    => [
                'items'      => RoomTypeResource::collection($roomTypes),
                'pagination' => [
                    'current_page' => $roomTypes->currentPage(),
                    'per_page'     => $roomTypes->perPage(),
                    'total'        => $roomTypes->total(),
                    'last_page'    => $roomTypes->lastPage(),
                ],
            ],
            'message' => 'Room types fetched successfully',
        ]);
    }

    public function store(StoreRoomTypeRequest $request): JsonResponse
    {
        $roomType = $this->service->create(RoomTypeData::from($request->validated()));

        $quantity = $request->integer('room_quantity', 0);
        if ($quantity > 0) {
            $this->service->generateRooms(
                $roomType,
                $quantity,
                $request->input('start_number', '1')
            );
        }

        return response()->json([
            'status'  => 1,
            'data'    => new RoomTypeResource($roomType),
            'message' => 'Room type created successfully',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'status'  => 1,
            'data'    => new RoomTypeResource($this->service->findOrFail($id)),
            'message' => 'Room type fetched successfully',
        ]);
    }

    public function generateRooms(Request $request, int $id): JsonResponse
    {
        $roomType = $this->service->findOrFail($id);
        $quantity = $request->integer('quantity', 1);
        $startNumber = $request->input('start_number', '1');

        $rooms = $this->service->generateRooms($roomType, $quantity, $startNumber);

        return response()->json([
            'status'  => 1,
            'data'    => ['count' => $rooms->count()],
            'message' => 'Rooms generated successfully',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($this->service->findOrFail($id));

        return response()->json([
            'status'  => 1,
            'data'    => null,
            'message' => 'Room type deleted successfully',
        ]);
    }
}