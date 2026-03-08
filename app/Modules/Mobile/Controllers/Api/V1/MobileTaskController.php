<?php

declare(strict_types=1);

namespace App\Modules\Mobile\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Mobile\Requests\StoreMobileTaskRequest;
use App\Modules\Mobile\Resources\MobileTaskResource;
use App\Modules\Mobile\Services\MobileTaskService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MobileTaskController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly MobileTaskService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(): AnonymousResourceCollection
    {
        return MobileTaskResource::collection($this->service->paginate());
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreMobileTaskRequest $request): MobileTaskResource
    {
        return new MobileTaskResource($this->service->create($request->validated()));
    }
}


