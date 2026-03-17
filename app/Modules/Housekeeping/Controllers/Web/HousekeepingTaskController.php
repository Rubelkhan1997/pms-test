<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Housekeeping\Requests\StoreHousekeepingTaskRequest;
use App\Modules\Housekeeping\Requests\UpdateHousekeepingTaskRequest;
use App\Modules\Housekeeping\Services\HousekeepingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HousekeepingTaskController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly HousekeepingService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'task_type', 'priority']);
        $tasks = $this->service->paginate($filters);
        $statistics = $this->service->getStatistics(currentHotel()->id);
        
        return Inertia::render('Housekeeping/Tasks/Index', [
            'tasks' => $tasks,
            'statistics' => $statistics,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        $task = $this->service->findOrFail($id, ['room', 'hotel', 'createdBy', 'assignedTo']);
        
        return Inertia::render('Housekeeping/Tasks/Show', [
            'task' => $task,
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreHousekeepingTaskRequest $request): RedirectResponse
    {
        $task = $this->service->create($request->validated());

        return redirect()
            ->route('housekeeping.tasks.show', $task->id)
            ->with('success', 'Housekeeping task created successfully.');
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateHousekeepingTaskRequest $request, int $id): RedirectResponse
    {
        $task = $this->service->update($id, $request->validated());

        return redirect()
            ->route('housekeeping.tasks.show', $task->id)
            ->with('success', 'Housekeeping task updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()
            ->route('housekeeping.tasks.index')
            ->with('success', 'Housekeeping task deleted successfully.');
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed,blocked'],
        ]);
        
        $status = \App\Modules\Housekeeping\Enums\HousekeepingStatus::from($validated['status']);
        $task = $this->service->updateStatus($id, $status);

        return redirect()
            ->route('housekeeping.tasks.show', $task->id)
            ->with('success', 'Task status updated successfully.');
    }

    /**
     * Get today's tasks.
     */
    public function today(): Response
    {
        $tasks = $this->service->getTodayTasks(currentHotel()->id);
        
        return Inertia::render('Housekeeping/Tasks/Today', [
            'tasks' => $tasks,
        ]);
    }
}
