<?php

declare(strict_types=1);

namespace App\Modules\Hr\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Hr\Requests\StoreEmployeeRequest;
use App\Modules\Hr\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly EmployeeService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(): Response
    {
        return Inertia::render('Hr/Index', [
            'items' => $this->service->paginate(),
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back();
    }
}


