<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Data\ReservationData;
use App\Modules\FrontDesk\Requests\StoreReservationRequest;
use App\Modules\FrontDesk\Requests\UpdateReservationRequest;
use App\Modules\FrontDesk\Resources\ReservationResource;
use App\Modules\FrontDesk\Services\ReservationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        try {
            $reservation = $this->service->find($id);

            return response()->json([
                'status' => 1,
                'data' => new ReservationResource($reservation),
                'message' => 'Reservation fetched successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Reservation not found',
            ], 404);
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $reservationData = ReservationData::from($request->validated());
        $reservation = $this->service->create($reservationData);

        return response()->json([
            'status' => 1,
            'data' => new ReservationResource($reservation),
            'message' => 'Reservation created successfully',
        ], 201);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateReservationRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            unset($validated['hotel_id']); // Can't change hotel

            $reservationData = ReservationData::from($validated);
            $reservation = $this->service->update($id, $reservationData);


            return response()->json([
                'status' => 1,
                'data' => new ReservationResource($reservation),
                'message' => 'Reservation updated successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Reservation not found',
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
                'message' => 'Reservation deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Reservation not found',
            ], 404);
        } 
    }

    /**
     * Cancel reservation.
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $reservation = $this->service->cancel($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Reservation not found',
            ], 404);
        }

        return response()->json([
            'status' => 1,
            'data' => new ReservationResource($reservation),
            'message' => 'Reservation cancelled successfully',
        ]);
    }
}
