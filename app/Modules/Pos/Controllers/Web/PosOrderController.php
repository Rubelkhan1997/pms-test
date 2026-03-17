<?php

declare(strict_types=1);

namespace App\Modules\Pos\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Pos\Requests\StorePosOrderRequest;
use App\Modules\Pos\Requests\UpdatePosOrderRequest;
use App\Modules\Pos\Services\PosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PosOrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly PosService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'outlet']);
        $orders = $this->service->paginate($filters);
        $statistics = $this->service->getStatistics(currentHotel()->id);
        
        return Inertia::render('Pos/Orders/Index', [
            'orders' => $orders,
            'statistics' => $statistics,
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        $order = $this->service->findOrFail($id, ['hotel', 'createdBy', 'reservation']);
        
        return Inertia::render('Pos/Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StorePosOrderRequest $request): RedirectResponse
    {
        $order = $this->service->create($request->validated());

        return redirect()
            ->route('pos.orders.show', $order->id)
            ->with('success', 'POS order created successfully.');
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdatePosOrderRequest $request, int $id): RedirectResponse
    {
        $order = $this->service->update($id, $request->validated());

        return redirect()
            ->route('pos.orders.show', $order->id)
            ->with('success', 'POS order updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()
            ->route('pos.orders.index')
            ->with('success', 'POS order deleted successfully.');
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,submitted,served,settled,cancelled'],
        ]);
        
        $status = \App\Modules\Pos\Enums\POSOrderStatus::from($validated['status']);
        $order = $this->service->updateStatus($id, $status);

        return redirect()
            ->route('pos.orders.show', $order->id)
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Charge order to room.
     */
    public function chargeToRoom(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
        ]);
        
        $order = $this->service->chargeToRoom($id, (int) $validated['reservation_id']);

        return redirect()
            ->route('pos.orders.show', $order->id)
            ->with('success', 'Order charged to room successfully.');
    }

    /**
     * Get today's orders.
     */
    public function today(): Response
    {
        $orders = $this->service->getTodayOrders(currentHotel()->id);
        
        return Inertia::render('Pos/Orders/Today', [
            'orders' => $orders,
        ]);
    }
}
