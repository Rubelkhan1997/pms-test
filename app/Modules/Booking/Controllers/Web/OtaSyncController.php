<?php

declare(strict_types=1);

namespace App\Modules\Booking\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Requests\StoreOtaSyncRequest;
use App\Modules\Booking\Services\OtaSyncService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OtaSyncController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly OtaSyncService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(): Response
    {
        return Inertia::render('Booking/Index', [
            'items' => $this->service->paginate(),
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreOtaSyncRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back();
    }
}


