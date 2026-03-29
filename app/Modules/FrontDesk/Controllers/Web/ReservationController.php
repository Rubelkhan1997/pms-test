<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Models\Hotel;
use App\Modules\FrontDesk\Services\ReservationService;
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
        // try {
            $hotels = Hotel::all();
            $guests = \App\Modules\Guest\Models\GuestProfile::orderBy('first_name')->get();
            $rooms = \App\Modules\FrontDesk\Models\Room::where('status', 'available')->orderBy('number')->get();

            return Inertia::render('Reservations/Create', [
                'hotels' => $hotels,
                'guests' => $guests,
                'rooms' => $rooms,
            ]);
        // } catch (Throwable $e) {
        //     logger()->error('Error loading create form: ' . $e->getMessage());
        //     abort(500, 'Failed to load form');
        // }
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
}


