<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Housekeeping\Requests\StoreHousekeepingTaskRequest;
use App\Modules\Housekeeping\Services\HousekeepingTaskService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HousekeepingTaskController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly HousekeepingTaskService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(): Response
    {
        return Inertia::render('Housekeeping/Index', [
            'items' => $this->service->paginate(),
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreHousekeepingTaskRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back();
    }
}


