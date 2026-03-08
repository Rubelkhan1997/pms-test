<?php

declare(strict_types=1);

namespace App\Modules\Guest\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Guest\Requests\StoreGuestProfileRequest;
use App\Modules\Guest\Resources\GuestProfileResource;
use App\Modules\Guest\Services\GuestProfileService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GuestProfileController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly GuestProfileService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(): AnonymousResourceCollection
    {
        return GuestProfileResource::collection($this->service->paginate());
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreGuestProfileRequest $request): GuestProfileResource
    {
        return new GuestProfileResource($this->service->create($request->validated()));
    }
}


