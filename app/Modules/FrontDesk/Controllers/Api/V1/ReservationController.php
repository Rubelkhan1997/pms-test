<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Requests\StoreReservationRequest;
use App\Modules\FrontDesk\Requests\UpdateReservationRequest;
use App\Modules\FrontDesk\Resources\ReservationResource;
use App\Modules\FrontDesk\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
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
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'check_in_date', 'check_out_date']);
        
        return ReservationResource::collection(
            $this->service->paginate($filters)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResource
    {
        $reservation = $this->service->findOrFail($id, ['room', 'guestProfile', 'createdBy']);
        
        return new ReservationResource($reservation->load(['room', 'guestProfile', 'createdBy']));
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreReservationRequest $request): JsonResource
    {
        $reservation = $this->service->create($request->validated());
        
        return (new ReservationResource($reservation->fresh(['room', 'guestProfile', 'createdBy'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateReservationRequest $request, int $id): JsonResource
    {
        $reservation = $this->service->update($id, $request->validated());
        
        return new ReservationResource($reservation);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        
        return response()->json([
            'message' => 'Reservation deleted successfully.',
        ], 200);
    }

    /**
     * Check in a reservation.
     */
    public function checkIn(int $id): JsonResource
    {
        $reservation = $this->service->checkIn($id);
        
        return new ReservationResource($reservation);
    }

    /**
     * Check out a reservation.
     */
    public function checkOut(int $id): JsonResource
    {
        $reservation = $this->service->checkOut($id);
        
        return new ReservationResource($reservation);
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(int $id): JsonResource
    {
        $reservation = $this->service->cancel($id);
        
        return new ReservationResource($reservation);
    }

    /**
     * Get today's arrivals.
     */
    public function arrivals(): AnonymousResourceCollection
    {
        return ReservationResource::collection($this->service->getTodayArrivals());
    }

    /**
     * Get today's departures.
     */
    public function departures(): AnonymousResourceCollection
    {
        return ReservationResource::collection($this->service->getTodayDepartures());
    }

    /**
     * Get in-house guests.
     */
    public function inHouse(): AnonymousResourceCollection
    {
        return ReservationResource::collection($this->service->getInHouse());
    }
}
