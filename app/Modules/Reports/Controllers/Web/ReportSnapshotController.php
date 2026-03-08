<?php

declare(strict_types=1);

namespace App\Modules\Reports\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Requests\StoreReportSnapshotRequest;
use App\Modules\Reports\Services\ReportSnapshotService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReportSnapshotController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly ReportSnapshotService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(): Response
    {
        return Inertia::render('Reports/Index', [
            'items' => $this->service->paginate(),
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreReportSnapshotRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back();
    }
}


