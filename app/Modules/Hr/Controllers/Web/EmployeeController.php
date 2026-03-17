<?php

declare(strict_types=1);

namespace App\Modules\Hr\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Hr\Requests\StoreEmployeeRequest;
use App\Modules\Hr\Requests\UpdateEmployeeRequest;
use App\Modules\Hr\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function index(Request $request): Response
    {
        $filters = $request->only(['department', 'status']);
        $employees = $this->service->paginate($filters);
        $departments = $this->service->getDepartments(currentHotel()->id);
        $statistics = $this->service->getStatistics(currentHotel()->id);
        
        return Inertia::render('Hr/Employees/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'statistics' => $statistics,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        $employee = $this->service->findOrFail($id);
        
        return Inertia::render('Hr/Employees/Show', [
            'employee' => $employee,
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = $this->service->create($request->validated());

        return redirect()
            ->route('hr.employees.show', $employee->id)
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateEmployeeRequest $request, int $id): RedirectResponse
    {
        $employee = $this->service->update($id, $request->validated());

        return redirect()
            ->route('hr.employees.show', $employee->id)
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()
            ->route('hr.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}
