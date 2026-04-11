<?php

namespace App\Modules\[MODULE]\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\[MODULE]\Models\[MODEL];
use App\Modules\[MODULE]\Services\[SERVICE];
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\[MODULE]\Http\Requests\[MODEL]StoreRequest;
use App\Modules\[MODULE]\Http\Requests\[MODEL]UpdateRequest;

// FILE: app/Modules/[MODULE]/Controllers/Api/V1/[CONTROLLER]Controller.php

class [CONTROLLER]Controller extends Controller
{
    public function __construct(
        protected [SERVICE] $[service]
    ) {}

    /**
     * Display a listing with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'status', 'from_date', 'to_date']);
            $perPage = $request->integer('per_page', 15);

            $paginator = $this->[service]->index($filters, $perPage);

            return response()->json([
                'status' => 1,
                'data' => [
                    'items' => $paginator->items(),
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                        'last_page' => $paginator->lastPage(),
                    ],
                ],
                'message' => '[MODEL]s retrieved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Failed to retrieve [MODEL]s: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created record.
     */
    public function store([MODEL]StoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $model = $this->[service]->store($data);

            return response()->json([
                'status' => 1,
                'data' => $model,
                'message' => '[MODEL] created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Failed to create [MODEL]: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified record.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $model = $this->[service]->show($id);

            return response()->json([
                'status' => 1,
                'data' => $model,
                'message' => '[MODEL] retrieved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Failed to retrieve [MODEL]: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified record.
     */
    public function update([MODEL]UpdateRequest $request, int $id): JsonResponse
    {
        try {
            $model = $this->[service]->show($id);
            $data = $request->validated();
            $updated = $this->[service]->update($model, $data);

            return response()->json([
                'status' => 1,
                'data' => $updated,
                'message' => '[MODEL] updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Failed to update [MODEL]: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified record.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $model = $this->[service]->show($id);
            $this->[service]->destroy($model);

            return response()->json([
                'status' => 1,
                'data' => null,
                'message' => '[MODEL] deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'data' => null,
                'message' => 'Failed to delete [MODEL]: ' . $e->getMessage(),
            ], 500);
        }
    }
}
