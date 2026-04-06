<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller; 
use App\Modules\FrontDesk\Requests\StoreReservationRequest;
use App\Modules\FrontDesk\Resources\ReservationResource;
use App\Modules\FrontDesk\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly ReservationService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->paginate(
            filters: $request->only(['status', 'check_in_date', 'check_out_date', 'search']),
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('per_page', 15)
        );

        return response()->json([
            'status' => 1,
            'data' => [
                'items' => ReservationResource::collection($paginator),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
            'message' => 'Reservations fetched successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $reservation = $this->service->find($id);

        if (!$reservation) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Reservation not found',
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'data' => new ReservationResource($reservation),
            'message' => 'Reservation fetched successfully',
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $reservation = $this->service->create($request->validated());

        return response()->json([
            'status' => 1,
            'data' => new ReservationResource($reservation),
            'message' => 'Reservation created successfully',
        ], 201);
    }

    /**
     * Update the specified resource.
     */
    public function update(StoreReservationRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        unset($validated['hotel_id']); // Can't change hotel

        $reservation = $this->service->update($id, $validated);

        if (!$reservation) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message'  => 'Reservation not found',
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'data' => new ReservationResource($reservation),
            'message' => 'Reservation updated successfully',
        ]);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->delete($id);

        if (!$deleted) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Reservation not found',
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'data' => null,
            'message' => 'Reservation deleted successfully',
        ]);
    }

    /**
     * Cancel reservation.
     */
    public function cancel(int $id): JsonResponse
    {
        $reservation = $this->service->cancel($id);

        if (!$reservation) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Failed to cancel reservation',
            ], 422);
        }

        return response()->json([
            'status' => 1,
            'data' => new ReservationResource($reservation),
            'message' => 'Reservation cancelled successfully',
        ]);
    }
}
