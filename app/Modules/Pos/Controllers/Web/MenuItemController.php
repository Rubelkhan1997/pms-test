<?php

declare(strict_types=1);

namespace App\Modules\Pos\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Pos\Requests\StoreMenuItemRequest;
use App\Modules\Pos\Services\MenuItemService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MenuItemController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly MenuItemService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(): Response
    {
        $hotelId = currentHotel()->id;
        $menu = $this->service->getMenu($hotelId);
        $categories = $this->service->getCategories($hotelId);
        
        return Inertia::render('Pos/Menu/Index', [
            'menu' => $menu,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreMenuItemRequest $request): RedirectResponse
    {
        $item = $this->service->create($request->validated());

        return redirect()
            ->route('pos.menu.index')
            ->with('success', 'Menu item created successfully.');
    }

    /**
     * Update the specified resource.
     */
    public function update(StoreMenuItemRequest $request, int $id): RedirectResponse
    {
        $item = $this->service->update($id, $request->validated());

        return redirect()
            ->route('pos.menu.index')
            ->with('success', 'Menu item updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()
            ->route('pos.menu.index')
            ->with('success', 'Menu item deleted successfully.');
    }

    /**
     * Toggle menu item active status.
     */
    public function toggleActive(int $id): RedirectResponse
    {
        $item = $this->service->toggleActive($id);

        return redirect()
            ->route('pos.menu.index')
            ->with('success', 'Menu item status updated successfully.');
    }
}
