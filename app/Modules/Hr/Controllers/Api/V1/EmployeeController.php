<?php

declare(strict_types=1);

namespace App\Modules\Hr\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Hr\Requests\StoreEmployeeRequest;
use App\Modules\Hr\Resources\EmployeeResource;
use App\Modules\Hr\Services\EmployeeService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmployeeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly EmployeeService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(): AnonymousResourceCollection
    {
        return EmployeeResource::collection($this->service->paginate());
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreEmployeeRequest $request): EmployeeResource
    {
        return new EmployeeResource($this->service->create($request->validated()));
    }
}


