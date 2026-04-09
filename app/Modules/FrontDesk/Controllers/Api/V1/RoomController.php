<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Data\RoomData;
use App\Modules\FrontDesk\Requests\StoreRoomRequest;
use App\Modules\FrontDesk\Requests\UpdateRoomRequest;
use App\Modules\FrontDesk\Resources\RoomResource;
use App\Modules\FrontDesk\Services\RoomService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly RoomService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->paginate(
            filters: $request->only(['search', 'hotel_id', 'status']),
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('per_page', 15)
        );

        return response()->json([
            'status' => 1,
            'data' => [
                'items' => RoomResource::collection($paginator),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
            'message' => 'Rooms fetched successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $room = $this->service->find($id);

            return response()->json([
                'status' => 1,
                'data' => new RoomResource($room),
                'message' => 'Room fetched successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Room not found',
            ], 404);
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreRoomRequest $request): JsonResponse
    {
        $roomData = RoomData::from($request->validated());
        $room = $this->service->create($roomData);

        return response()->json([
            'status' => 1,
            'data' => new RoomResource($room->loadMissing('hotel:id,name')),
            'message' => 'Room created successfully',
        ], 201);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateRoomRequest $request, int $id): JsonResponse
    {
        try {
            $room = $this->service->find($id);

            $payload = array_merge([
                'hotel_id' => $room->hotel_id,
                'number' => $room->number,
                'floor' => $room->floor,
                'type' => $room->type,
                'status' => $room->status?->value,
                'base_rate' => $room->base_rate,
            ], $request->validated());

            $roomData = RoomData::from($payload);
            $updatedRoom = $this->service->update($id, $roomData);

            return response()->json([
                'status' => 1,
                'data' => new RoomResource($updatedRoom),
                'message' => 'Room updated successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Room not found',
            ], 404);
        }
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json([
                'status' => 1,
                'data' => null,
                'message' => 'Room deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Room not found',
            ], 404);
        }
    }
}
