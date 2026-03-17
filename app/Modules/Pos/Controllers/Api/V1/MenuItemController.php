<?php

declare(strict_types=1);

namespace App\Modules\Pos\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Pos\Requests\StoreMenuItemRequest;
use App\Modules\Pos\Resources\MenuItemResource;
use App\Modules\Pos\Services\MenuItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly MenuItemService $service)
    {
    }

    /**
     * Display a paginated listing.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['category', 'hotel_id']);
        $hotelId = $request->input('hotel_id', currentHotel()->id);
        
        return MenuItemResource::collection(
            $this->service->paginate($filters)
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResource
    {
        $item = $this->service->findOrFail($id, ['hotel']);
        
        return new MenuItemResource($item);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreMenuItemRequest $request): JsonResource
    {
        $item = $this->service->create($request->validated());
        
        return (new MenuItemResource($item->fresh(['hotel'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified resource.
     */
    public function update(StoreMenuItemRequest $request, int $id): JsonResource
    {
        $item = $this->service->update($id, $request->validated());
        
        return new MenuItemResource($item);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        
        return response()->json([
            'message' => 'Menu item deleted successfully.',
        ], 200);
    }

    /**
     * Toggle menu item active status.
     */
    public function toggleActive(int $id): JsonResource
    {
        $item = $this->service->toggleActive($id);
        
        return new MenuItemResource($item);
    }

    /**
     * Get menu for hotel.
     */
    public function menu(Request $request, int $hotelId): AnonymousResourceCollection
    {
        $category = $request->input('category');
        $items = $this->service->getMenu($hotelId, $category);
        
        return MenuItemResource::collection($items);
    }

    /**
     * Get categories for hotel.
     */
    public function categories(int $hotelId): JsonResponse
    {
        $categories = $this->service->getCategories($hotelId);
        
        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Search menu items.
     */
    public function search(Request $request, int $hotelId): AnonymousResourceCollection
    {
        $search = $request->input('search', '');
        $items = $this->service->search($hotelId, $search);
        
        return MenuItemResource::collection($items);
    }
}
