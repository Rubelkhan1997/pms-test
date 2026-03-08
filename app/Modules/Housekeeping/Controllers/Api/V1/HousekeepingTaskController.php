<?php

declare(strict_types=1);

namespace App\Modules\Housekeeping\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Housekeeping\Requests\StoreHousekeepingTaskRequest;
use App\Modules\Housekeeping\Resources\HousekeepingTaskResource;
use App\Modules\Housekeeping\Services\HousekeepingTaskService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HousekeepingTaskController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly HousekeepingTaskService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(): AnonymousResourceCollection
    {
        return HousekeepingTaskResource::collection($this->service->paginate());
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreHousekeepingTaskRequest $request): HousekeepingTaskResource
    {
        return new HousekeepingTaskResource($this->service->create($request->validated()));
    }
}


