<?php

declare(strict_types=1);

namespace App\Modules\Pos\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Pos\Requests\StorePosOrderRequest;
use App\Modules\Pos\Resources\PosOrderResource;
use App\Modules\Pos\Services\PosOrderService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PosOrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly PosOrderService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(): AnonymousResourceCollection
    {
        return PosOrderResource::collection($this->service->paginate());
    }

    /**
     * Store a newly created resource.
     */
    public function store(StorePosOrderRequest $request): PosOrderResource
    {
        return new PosOrderResource($this->service->create($request->validated()));
    }
}


