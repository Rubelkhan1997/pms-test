<?php

declare(strict_types=1);

namespace App\Modules\Guest\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Guest\Requests\StoreGuestProfileRequest;
use App\Modules\Guest\Requests\UpdateGuestProfileRequest;
use App\Modules\Guest\Resources\GuestProfileResource;
use App\Modules\Guest\Services\GuestProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestProfileController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly GuestProfileService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'status', 'hotel_id']);
        
        return GuestProfileResource::collection(
            $this->service->paginate($filters)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResource
    {
        $guest = $this->service->findOrFail($id, ['hotel', 'agent', 'creator', 'reservations']);
        
        return new GuestProfileResource($guest);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreGuestProfileRequest $request): JsonResource
    {
        $guest = $this->service->create($request->validated());
        
        return (new GuestProfileResource($guest->fresh(['hotel', 'agent', 'creator'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateGuestProfileRequest $request, int $id): JsonResource
    {
        $guest = $this->service->update($id, $request->validated());
        
        return new GuestProfileResource($guest);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        
        return response()->json([
            'message' => 'Guest profile deleted successfully.',
        ], 200);
    }

    /**
     * Search guests.
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'status']);
        $guests = $this->service->search($filters);
        
        return GuestProfileResource::collection($guests);
    }

    /**
     * Get VIP guests.
     */
    public function vip(): AnonymousResourceCollection
    {
        return GuestProfileResource::collection($this->service->getVipGuests());
    }

    /**
     * Get guest stay history.
     */
    public function stayHistory(int $id): JsonResponse
    {
        $history = $this->service->getStayHistory($id);
        
        return response()->json([
            'data' => $history,
        ]);
    }

    /**
     * Check if email exists.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $hotelId = $request->input('hotel_id');
        $excludeId = $request->input('exclude_id');
        
        $exists = $this->service->emailExists($email, (int) $hotelId, $excludeId ? (int) $excludeId : null);
        
        return response()->json([
            'exists' => $exists,
        ]);
    }
}
