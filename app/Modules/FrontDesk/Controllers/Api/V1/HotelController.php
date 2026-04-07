<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Data\HotelData;
use App\Modules\FrontDesk\Requests\StoreHotelRequest;
use App\Modules\FrontDesk\Requests\UpdateHotelRequest;
use App\Modules\FrontDesk\Resources\HotelResource;
use App\Modules\FrontDesk\Services\HotelService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly HotelService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->paginate(
            filters: $request->only(['search']),
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('per_page', 15)
        );

        return response()->json([
            'status' => 1,
            'data' => [
                'items' => HotelResource::collection($paginator),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                ],
            ],
            'message' => 'Hotels fetched successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $hotel = $this->service->find($id);

            return response()->json([
                'status' => 1,
                'data' => new HotelResource($hotel),
                'message' => 'Hotel fetched successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Hotel not found',
            ], 404);
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreHotelRequest $request): JsonResponse
    {
        $data = HotelData::from($request->validated());
        $hotel = $this->service->create($data);

        return response()->json([
            'status' => 1,
            'data' => new HotelResource($hotel),
            'message' => 'Hotel created successfully',
        ], 201);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateHotelRequest $request, int $id): JsonResponse
    {
        try {
            $data = HotelData::from($request->validated());
            $hotel = $this->service->update($id, $data);

            return response()->json([
                'status' => 1,
                'data' => new HotelResource($hotel),
                'message' => 'Hotel updated successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Hotel not found',
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
                'message' => 'Hotel deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Hotel not found',
            ], 404);
        }
    }
}
