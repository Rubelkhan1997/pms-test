<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Data\SubscriptionPlanData;
use App\Modules\SuperAdmin\Requests\StoreSubscriptionPlanRequest;
use App\Modules\SuperAdmin\Resources\SubscriptionPlanResource;
use App\Modules\SuperAdmin\Services\SubscriptionPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function __construct(
        private readonly SubscriptionPlanService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $plans = $this->service->paginate((int) $request->get('per_page', 20));

        return response()->json([
            'status'  => 1,
            'data'    => [
                'items'      => SubscriptionPlanResource::collection($plans),
                'pagination' => [
                    'current_page' => $plans->currentPage(),
                    'per_page'     => $plans->perPage(),
                    'total'        => $plans->total(),
                    'last_page'    => $plans->lastPage(),
                ],
            ],
            'message' => 'Plans fetched successfully',
        ]);
    }

    public function store(StoreSubscriptionPlanRequest $request): JsonResponse
    {
        $plan = $this->service->create(SubscriptionPlanData::from($request->validated()));

        return response()->json([
            'status'  => 1,
            'data'    => new SubscriptionPlanResource($plan),
            'message' => 'Plan created successfully',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $plan = $this->service->findOrFail($id);

        return response()->json([
            'status'  => 1,
            'data'    => new SubscriptionPlanResource($plan),
            'message' => 'Plan fetched successfully',
        ]);
    }

    public function update(StoreSubscriptionPlanRequest $request, int $id): JsonResponse
    {
        $plan    = $this->service->findOrFail($id);
        $updated = $this->service->update($plan, SubscriptionPlanData::from($request->validated()));

        return response()->json([
            'status'  => 1,
            'data'    => new SubscriptionPlanResource($updated),
            'message' => 'Plan updated successfully',
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($this->service->findOrFail($id));

        return response()->json([
            'status'  => 1,
            'data'    => null,
            'message' => 'Plan deleted successfully',
        ]);
    }
}
