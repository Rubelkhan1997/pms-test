<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Data\PropertyData;
use App\Modules\FrontDesk\Requests\StorePropertyRequest;
use App\Modules\FrontDesk\Resources\PropertyResource;
use App\Modules\FrontDesk\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $properties = $this->service->paginate(
            filters: $request->only(['search']),
            perPage: (int) $request->get('per_page', 20),
        );

        return response()->json([
            'status'  => 1,
            'data'    => [
                'items'      => PropertyResource::collection($properties),
                'pagination' => [
                    'current_page' => $properties->currentPage(),
                    'per_page'     => $properties->perPage(),
                    'total'        => $properties->total(),
                    'last_page'    => $properties->lastPage(),
                ],
            ],
            'message' => 'Properties fetched successfully',
        ]);
    }

    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = $this->service->create(PropertyData::from($request->validated()));

        return response()->json([
            'status'  => 1,
            'data'    => new PropertyResource($property),
            'message' => 'Property created successfully',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'status'  => 1,
            'data'    => new PropertyResource($this->service->findOrFail($id)),
            'message' => 'Property fetched successfully',
        ]);
    }

    public function update(StorePropertyRequest $request, int $id): JsonResponse
    {
        $property = $this->service->findOrFail($id);
        $updated = $this->service->update($property, PropertyData::from($request->validated()));

        return response()->json([
            'status'  => 1,
            'data'    => new PropertyResource($updated),
            'message' => 'Property updated successfully',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($this->service->findOrFail($id));

        return response()->json([
            'status'  => 1,
            'data'    => null,
            'message' => 'Property deleted successfully',
        ]);
    }
}