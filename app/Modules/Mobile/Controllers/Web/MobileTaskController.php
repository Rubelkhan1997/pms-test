<?php

declare(strict_types=1);

namespace App\Modules\Mobile\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Mobile\Requests\StoreMobileTaskRequest;
use App\Modules\Mobile\Services\MobileTaskService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MobileTaskController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly MobileTaskService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(): Response
    {
        return Inertia::render('Mobile/Index', [
            'items' => $this->service->paginate(),
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreMobileTaskRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back();
    }
}


