<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Enums\RoomStatus;
use App\Modules\FrontDesk\Requests\StoreRoomRequest;
use App\Modules\FrontDesk\Requests\UpdateRoomRequest;
use App\Modules\FrontDesk\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly RoomService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'type', 'floor']);
        $rooms = $this->service->paginate($filters);
        $statistics = $this->service->getStatistics(currentHotel()->id);
        
        return Inertia::render('FrontDesk/Rooms/Index', [
            'rooms' => $rooms,
            'statistics' => $statistics,
            'filters' => $filters,
            'statuses' => array_column(RoomStatus::cases(), 'value'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        $room = $this->service->findOrFail($id, ['hotel', 'reservations']);
        
        return Inertia::render('FrontDesk/Rooms/Show', [
            'room' => $room,
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $room = $this->service->create($request->validated());

        return redirect()
            ->route('front-desk.rooms.show', $room->id)
            ->with('success', 'Room created successfully.');
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateRoomRequest $request, int $id): RedirectResponse
    {
        $room = $this->service->update($id, $request->validated());

        return redirect()
            ->route('front-desk.rooms.show', $room->id)
            ->with('success', 'Room updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()
            ->route('front-desk.rooms.index')
            ->with('success', 'Room deleted successfully.');
    }

    /**
     * Update room status.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:available,occupied,dirty,out_of_order'],
        ]);
        
        $status = RoomStatus::from($validated['status']);
        $room = $this->service->updateStatus($id, $status);

        return redirect()
            ->route('front-desk.rooms.show', $room->id)
            ->with('success', 'Room status updated successfully.');
    }

    /**
     * Display room grid.
     */
    public function grid(Request $request): Response
    {
        $floor = $request->input('floor');
        $hotelId = currentHotel()->id;
        
        $rooms = $floor 
            ? $this->service->getByFloor($hotelId, (int) $floor)
            : $this->service->getByHotel($hotelId);
        
        $floors = $this->service->getFloors($hotelId);
        $types = $this->service->getRoomTypes($hotelId);
        
        return Inertia::render('FrontDesk/Rooms/Grid', [
            'rooms' => $rooms,
            'floors' => $floors,
            'types' => $types,
            'selectedFloor' => $floor,
        ]);
    }
}
