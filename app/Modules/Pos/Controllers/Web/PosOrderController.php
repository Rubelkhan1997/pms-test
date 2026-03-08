<?php

declare(strict_types=1);

namespace App\Modules\Pos\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Pos\Requests\StorePosOrderRequest;
use App\Modules\Pos\Services\PosOrderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PosOrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly PosOrderService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(): Response
    {
        return Inertia::render('Pos/Index', [
            'items' => $this->service->paginate(),
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StorePosOrderRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back();
    }
}


