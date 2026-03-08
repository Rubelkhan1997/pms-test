<?php

declare(strict_types=1);

namespace App\Modules\Booking\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Requests\StoreOtaSyncRequest;
use App\Modules\Booking\Resources\OtaSyncResource;
use App\Modules\Booking\Services\OtaSyncService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OtaSyncController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly OtaSyncService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(): AnonymousResourceCollection
    {
        return OtaSyncResource::collection($this->service->paginate());
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreOtaSyncRequest $request): OtaSyncResource
    {
        return new OtaSyncResource($this->service->create($request->validated()));
    }
}


