<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Requests\StoreRoomRequest;
use App\Modules\FrontDesk\Requests\UpdateRoomRequest;
use App\Modules\FrontDesk\Resources\RoomResource;
use App\Modules\FrontDesk\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

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
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'type', 'floor', 'hotel_id']);
        
        return RoomResource::collection(
            $this->service->paginate($filters)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResource
    {
        $room = $this->service->findOrFail($id, ['hotel', 'reservations']);
        
        return new RoomResource($room);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreRoomRequest $request): JsonResource
    {
        $room = $this->service->create($request->validated());
        
        return (new RoomResource($room->fresh(['hotel'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateRoomRequest $request, int $id): JsonResource
    {
        $room = $this->service->update($id, $request->validated());
        
        return new RoomResource($room);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        
        return response()->json([
            'message' => 'Room deleted successfully.',
        ], 200);
    }

    /**
     * Get rooms by hotel.
     */
    public function byHotel(int $hotelId): AnonymousResourceCollection
    {
        $rooms = $this->service->getByHotel($hotelId);
        
        return RoomResource::collection($rooms);
    }

    /**
     * Get available rooms.
     */
    public function available(int $hotelId): AnonymousResourceCollection
    {
        $rooms = $this->service->getAvailable($hotelId);
        
        return RoomResource::collection($rooms);
    }

    /**
     * Update room status.
     */
    public function updateStatus(Request $request, int $id): JsonResource
    {
        $validated = $request->validate([
            'status' => ['required', 'in:available,occupied,dirty,out_of_order'],
        ]);
        
        $status = RoomStatus::from($validated['status']);
        $room = $this->service->updateStatus($id, $status);
        
        return new RoomResource($room);
    }

    /**
     * Get room statistics.
     */
    public function statistics(int $hotelId): JsonResponse
    {
        $stats = $this->service->getStatistics($hotelId);
        
        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get rooms by floor.
     */
    public function byFloor(Request $request, int $hotelId): AnonymousResourceCollection
    {
        $floor = $request->input('floor');
        $rooms = $this->service->getByFloor($hotelId, (int) $floor);
        
        return RoomResource::collection($rooms);
    }

    /**
     * Get all floors for hotel.
     */
    public function floors(int $hotelId): JsonResponse
    {
        $floors = $this->service->getFloors($hotelId);
        
        return response()->json([
            'data' => $floors,
        ]);
    }

    /**
     * Get all room types for hotel.
     */
    public function types(int $hotelId): JsonResponse
    {
        $types = $this->service->getRoomTypes($hotelId);
        
        return response()->json([
            'data' => $types,
        ]);
    }
}
