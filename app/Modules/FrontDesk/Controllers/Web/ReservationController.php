<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Requests\StoreReservationRequest;
use App\Modules\FrontDesk\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly ReservationService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(): Response
    {
        return Inertia::render('FrontDesk/Index', [
            'items' => $this->service->paginate(),
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back();
    }
}


