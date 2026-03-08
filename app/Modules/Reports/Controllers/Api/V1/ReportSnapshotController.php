<?php

declare(strict_types=1);

namespace App\Modules\Reports\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Requests\StoreReportSnapshotRequest;
use App\Modules\Reports\Resources\ReportSnapshotResource;
use App\Modules\Reports\Services\ReportSnapshotService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportSnapshotController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly ReportSnapshotService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(): AnonymousResourceCollection
    {
        return ReportSnapshotResource::collection($this->service->paginate());
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreReportSnapshotRequest $request): ReportSnapshotResource
    {
        return new ReportSnapshotResource($this->service->create($request->validated()));
    }
}


