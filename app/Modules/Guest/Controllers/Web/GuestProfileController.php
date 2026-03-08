<?php

declare(strict_types=1);

namespace App\Modules\Guest\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Guest\Requests\StoreGuestProfileRequest;
use App\Modules\Guest\Services\GuestProfileService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GuestProfileController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly GuestProfileService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(): Response
    {
        return Inertia::render('Guest/Index', [
            'items' => $this->service->paginate(),
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreGuestProfileRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back();
    }
}


