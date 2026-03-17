<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Housekeeping\Requests\StoreHousekeepingTaskRequest;
use App\Modules\Housekeeping\Requests\UpdateHousekeepingTaskRequest;
use App\Modules\Housekeeping\Resources\HousekeepingTaskResource;
use App\Modules\Housekeeping\Services\HousekeepingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class HousekeepingTaskController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly HousekeepingService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'task_type', 'priority']);
        
        return HousekeepingTaskResource::collection(
            $this->service->paginate($filters)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResource
    {
        $task = $this->service->findOrFail($id, ['room', 'hotel', 'createdBy', 'assignedTo']);
        
        return new HousekeepingTaskResource($task);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreHousekeepingTaskRequest $request): JsonResource
    {
        $task = $this->service->create($request->validated());
        
        return (new HousekeepingTaskResource($task->fresh(['room', 'hotel'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateHousekeepingTaskRequest $request, int $id): JsonResource
    {
        $task = $this->service->update($id, $request->validated());
        
        return new HousekeepingTaskResource($task);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        
        return response()->json([
            'message' => 'Housekeeping task deleted successfully.',
        ], 200);
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, int $id): JsonResource
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed,blocked'],
        ]);
        
        $status = \App\Modules\Housekeeping\Enums\HousekeepingStatus::from($validated['status']);
        $task = $this->service->updateStatus($id, $status);
        
        return new HousekeepingTaskResource($task);
    }

    /**
     * Get today's tasks.
     */
    public function today(int $hotelId): AnonymousResourceCollection
    {
        $tasks = $this->service->getTodayTasks($hotelId);
        
        return HousekeepingTaskResource::collection($tasks);
    }

    /**
     * Get pending tasks.
     */
    public function pending(int $hotelId): AnonymousResourceCollection
    {
        $tasks = $this->service->getPendingTasks($hotelId);
        
        return HousekeepingTaskResource::collection($tasks);
    }

    /**
     * Get task statistics.
     */
    public function statistics(int $hotelId): JsonResponse
    {
        $stats = $this->service->getStatistics($hotelId);
        
        return response()->json([
            'data' => $stats,
        ]);
    }
}
