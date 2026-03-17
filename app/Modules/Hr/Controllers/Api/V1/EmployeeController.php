<?php

declare(strict_types=1);

namespace App\Modules\Hr\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Hr\Requests\StoreEmployeeRequest;
use App\Modules\Hr\Requests\UpdateEmployeeRequest;
use App\Modules\Hr\Resources\EmployeeResource;
use App\Modules\Hr\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly EmployeeService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['department', 'status', 'hotel_id']);
        
        return EmployeeResource::collection(
            $this->service->paginate($filters)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResource
    {
        $employee = $this->service->findOrFail($id);
        
        return new EmployeeResource($employee);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreEmployeeRequest $request): JsonResource
    {
        $employee = $this->service->create($request->validated());
        
        return (new EmployeeResource($employee->fresh(['hotel', 'user'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateEmployeeRequest $request, int $id): JsonResource
    {
        $employee = $this->service->update($id, $request->validated());
        
        return new EmployeeResource($employee);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        
        return response()->json([
            'message' => 'Employee deleted successfully.',
        ], 200);
    }

    /**
     * Get employees by department.
     */
    public function byDepartment(Request $request, int $hotelId): AnonymousResourceCollection
    {
        $department = $request->input('department');
        $employees = $this->service->getByDepartment($hotelId, $department);
        
        return EmployeeResource::collection($employees);
    }

    /**
     * Get active employees.
     */
    public function active(int $hotelId): AnonymousResourceCollection
    {
        $employees = $this->service->getActive($hotelId);
        
        return EmployeeResource::collection($employees);
    }

    /**
     * Get employee statistics.
     */
    public function statistics(int $hotelId): JsonResponse
    {
        $stats = $this->service->getStatistics($hotelId);
        
        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Get departments.
     */
    public function departments(int $hotelId): JsonResponse
    {
        $departments = $this->service->getDepartments($hotelId);
        
        return response()->json([
            'data' => $departments,
        ]);
    }
}
