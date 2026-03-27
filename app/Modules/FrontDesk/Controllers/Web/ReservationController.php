<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Requests\StoreReservationRequest;
use App\Modules\FrontDesk\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly ReservationService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Reservations/Index', [
            'reservations' => $this->service->paginate(
                filters: $request->only(['status', 'check_in_date', 'check_out_date', 'search']),
                page: (int) $request->get('page', 1),
                perPage: 15
            ),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        $reservation = $this->service->find($id);
        
        return Inertia::render('Reservations/Show', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $guests = \App\Modules\Guest\Models\GuestProfile::orderBy('first_name')->get();
        $rooms = \App\Modules\FrontDesk\Models\Room::where('status', 'available')->orderBy('number')->get();

        return Inertia::render('Reservations/Create', [
            'guests' => $guests,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        $reservation = $this->service->find($id);
        $guests = \App\Modules\Guest\Models\GuestProfile::orderBy('first_name')->get();
        $rooms = \App\Modules\FrontDesk\Models\Room::orderBy('number')->get();

        return Inertia::render('Reservations/Edit', [
            'reservation' => $reservation,
            'guests' => $guests,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Update the specified resource.
     */
    public function update(StoreReservationRequest $request, int $id): RedirectResponse
    {
        $this->service->update($id, $request->validated());

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return back()->with('success', 'Reservation deleted successfully.');
    }

    /**
     * Check in guest.
     */
    public function checkIn(int $id): RedirectResponse
    {
        $this->service->checkIn($id);

        return back()->with('success', 'Guest checked in successfully.');
    }

    /**
     * Check out guest.
     */
    public function checkOut(int $id, Request $request): RedirectResponse
    {
        $this->service->checkOut($id, $request->only(['paid_amount', 'payment_method']));

        return back()->with('success', 'Guest checked out successfully.');
    }

    /**
     * Cancel reservation.
     */
    public function cancel(int $id): RedirectResponse
    {
        $this->service->cancel($id);

        return back()->with('success', 'Reservation cancelled successfully.');
    }
}


