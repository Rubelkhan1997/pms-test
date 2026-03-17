<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Enums\ReservationStatus;
use App\Modules\FrontDesk\Requests\StoreReservationRequest;
use App\Modules\FrontDesk\Requests\UpdateReservationRequest;
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
        $filters = $request->only(['status', 'check_in_date', 'check_out_date']);
        
        return Inertia::render('FrontDesk/Reservations/Index', [
            'reservations' => $this->service->paginate($filters),
            'statuses' => array_column(ReservationStatus::cases(), 'value'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        $reservation = $this->service->findOrFail($id, ['room', 'guestProfile', 'createdBy']);
        
        return Inertia::render('FrontDesk/Reservations/Show', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $reservation = $this->service->create($request->validated());

        return redirect()
            ->route('front-desk.reservations.show', $reservation->id)
            ->with('success', 'Reservation created successfully.');
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateReservationRequest $request, int $id): RedirectResponse
    {
        $reservation = $this->service->update($id, $request->validated());

        return redirect()
            ->route('front-desk.reservations.show', $reservation->id)
            ->with('success', 'Reservation updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()
            ->route('front-desk.reservations.index')
            ->with('success', 'Reservation deleted successfully.');
    }

    /**
     * Check in a reservation.
     */
    public function checkIn(int $id): RedirectResponse
    {
        try {
            $reservation = $this->service->checkIn($id);
            
            return redirect()
                ->route('front-desk.reservations.show', $reservation->id)
                ->with('success', 'Guest checked in successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Check out a reservation.
     */
    public function checkOut(int $id): RedirectResponse
    {
        try {
            $reservation = $this->service->checkOut($id);
            
            return redirect()
                ->route('front-desk.reservations.show', $reservation->id)
                ->with('success', 'Guest checked out successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(int $id): RedirectResponse
    {
        try {
            $reservation = $this->service->cancel($id);
            
            return redirect()
                ->route('front-desk.reservations.show', $reservation->id)
                ->with('success', 'Reservation cancelled successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
