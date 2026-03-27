<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Models\Hotel;
use App\Modules\FrontDesk\Requests\StoreReservationRequest;
use App\Modules\FrontDesk\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

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
        try {
            return Inertia::render('Reservations/Index', [
                'reservations' => $this->service->paginate(
                    filters: $request->only(['status', 'check_in_date', 'check_out_date', 'search']),
                    page: (int) $request->get('page', 1),
                    perPage: 15
                ),
            ]);
        } catch (Throwable $e) {
            logger()->error('Error loading reservations: ' . $e->getMessage());
            return Inertia::render('Reservations/Index', [
                'reservations' => [],
                'error' => 'Failed to load reservations. Please try again.',
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        try {
            $reservation = $this->service->find($id);

            if (!$reservation) {
                abort(404, 'Reservation not found');
            }

            return Inertia::render('Reservations/Show', [
                'reservation' => $reservation,
            ]);
        } catch (Throwable $e) {
            logger()->error('Error loading reservation: ' . $e->getMessage());
            abort(500, 'Failed to load reservation');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        try {
            $hotels = Hotel::all();
            $guests = \App\Modules\Guest\Models\GuestProfile::orderBy('first_name')->get();
            $rooms = \App\Modules\FrontDesk\Models\Room::where('status', 'available')->orderBy('number')->get();

            return Inertia::render('Reservations/Create', [
                'hotels' => $hotels,
                'guests' => $guests,
                'rooms' => $rooms,
            ]);
        } catch (Throwable $e) {
            logger()->error('Error loading create form: ' . $e->getMessage());
            abort(500, 'Failed to load form');
        }
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            
            // Add hotel_id from authenticated user or default hotel
            $validated['hotel_id'] = $request->user()?->currentHotelId() ?? Hotel::first()?->id;
            
            if (!$validated['hotel_id']) {
                return back()->withErrors(['hotel_id' => 'No hotel available']);
            }
            
            $this->service->create($validated);

            return redirect()->route('reservations.index')
                ->with('success', 'Reservation created successfully.');
        } catch (Throwable $e) {
            logger()->error('Error creating reservation: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create reservation: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        try {
            $reservation = $this->service->find($id);

            if (!$reservation) {
                abort(404, 'Reservation not found');
            }

            $hotels = Hotel::all();
            $guests = \App\Modules\Guest\Models\GuestProfile::orderBy('first_name')->get();
            $rooms = \App\Modules\FrontDesk\Models\Room::orderBy('number')->get();

            return Inertia::render('Reservations/Edit', [
                'reservation' => $reservation,
                'hotels' => $hotels,
                'guests' => $guests,
                'rooms' => $rooms,
            ]);
        } catch (Throwable $e) {
            logger()->error('Error loading edit form: ' . $e->getMessage());
            abort(500, 'Failed to load form');
        }
    }

    /**
     * Update the specified resource.
     */
    public function update(StoreReservationRequest $request, int $id): RedirectResponse
    {
        try {
            $validated = $request->validated();
            
            // Remove hotel_id from update (shouldn't change)
            unset($validated['hotel_id']);
            
            $this->service->update($id, $validated);

            return redirect()->route('reservations.index')
                ->with('success', 'Reservation updated successfully.');
        } catch (Throwable $e) {
            logger()->error('Error updating reservation: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update reservation: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->service->delete($id);

            return back()->with('success', 'Reservation deleted successfully.');
        } catch (Throwable $e) {
            logger()->error('Error deleting reservation: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete reservation: ' . $e->getMessage()]);
        }
    }

    /**
     * Check in guest.
     */
    public function checkIn(int $id): RedirectResponse
    {
        try {
            $this->service->checkIn($id);

            return back()->with('success', 'Guest checked in successfully.');
        } catch (Throwable $e) {
            logger()->error('Error checking in guest: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to check in guest: ' . $e->getMessage()]);
        }
    }

    /**
     * Check out guest.
     */
    public function checkOut(int $id, Request $request): RedirectResponse
    {
        try {
            $this->service->checkOut($id, $request->only(['paid_amount', 'payment_method']));

            return back()->with('success', 'Guest checked out successfully.');
        } catch (Throwable $e) {
            logger()->error('Error checking out guest: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to check out guest: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel reservation.
     */
    public function cancel(int $id): RedirectResponse
    {
        try {
            $this->service->cancel($id);

            return back()->with('success', 'Reservation cancelled successfully.');
        } catch (Throwable $e) {
            logger()->error('Error cancelling reservation: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to cancel reservation: ' . $e->getMessage()]);
        }
    }
}


