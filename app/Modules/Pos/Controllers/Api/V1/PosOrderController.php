<?php

declare(strict_types=1);

namespace App\Modules\Pos\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Pos\Requests\StorePosOrderRequest;
use App\Modules\Pos\Requests\UpdatePosOrderRequest;
use App\Modules\Pos\Resources\PosOrderResource;
use App\Modules\Pos\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class PosOrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly PosService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'outlet']);
        
        return PosOrderResource::collection(
            $this->service->paginate($filters)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResource
    {
        $order = $this->service->findOrFail($id, ['hotel', 'createdBy', 'reservation']);
        
        return new PosOrderResource($order);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StorePosOrderRequest $request): JsonResource
    {
        $order = $this->service->create($request->validated());
        
        return (new PosOrderResource($order->fresh(['hotel', 'createdBy'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdatePosOrderRequest $request, int $id): JsonResource
    {
        $order = $this->service->update($id, $request->validated());
        
        return new PosOrderResource($order);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        
        return response()->json([
            'message' => 'POS order deleted successfully.',
        ], 200);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, int $id): JsonResource
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,submitted,served,settled,cancelled'],
        ]);
        
        $status = \App\Modules\Pos\Enums\POSOrderStatus::from($validated['status']);
        $order = $this->service->updateStatus($id, $status);
        
        return new PosOrderResource($order);
    }

    /**
     * Charge order to room.
     */
    public function chargeToRoom(Request $request, int $id): JsonResource
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
        ]);
        
        $order = $this->service->chargeToRoom($id, (int) $validated['reservation_id']);
        
        return new PosOrderResource($order);
    }

    /**
     * Get today's orders.
     */
    public function today(int $hotelId): AnonymousResourceCollection
    {
        $orders = $this->service->getTodayOrders($hotelId);
        
        return PosOrderResource::collection($orders);
    }

    /**
     * Get orders by outlet.
     */
    public function byOutlet(Request $request, int $hotelId): AnonymousResourceCollection
    {
        $outlet = $request->input('outlet', 'Restaurant');
        $orders = $this->service->getByOutlet($hotelId, $outlet);
        
        return PosOrderResource::collection($orders);
    }

    /**
     * Get order statistics.
     */
    public function statistics(int $hotelId): JsonResponse
    {
        $stats = $this->service->getStatistics($hotelId);
        
        return response()->json([
            'data' => $stats,
        ]);
    }
}
