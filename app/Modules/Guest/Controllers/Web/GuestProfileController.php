<?php

declare(strict_types=1);

namespace App\Modules\Guest\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Guest\Requests\StoreGuestProfileRequest;
use App\Modules\Guest\Requests\UpdateGuestProfileRequest;
use App\Modules\Guest\Services\GuestProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuestProfileController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly GuestProfileService $service)
    {
    }

    /**
     * Display a listing page.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'hotel_id']);
        
        return Inertia::render('Guest/Profiles/Index', [
            'guestProfiles' => $this->service->paginate($filters),
            'filters' => $filters,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        $guest = $this->service->findOrFail($id, ['hotel', 'agent', 'creator', 'reservations']);
        $stayHistory = $this->service->getStayHistory($id);
        
        return Inertia::render('Guest/Profiles/Show', [
            'guest' => $guest,
            'stayHistory' => $stayHistory,
        ]);
    }

    /**
     * Store a newly created resource.
     */
    public function store(StoreGuestProfileRequest $request): RedirectResponse
    {
        $guest = $this->service->create($request->validated());

        return redirect()
            ->route('guests.profiles.show', $guest->id)
            ->with('success', 'Guest profile created successfully.');
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateGuestProfileRequest $request, int $id): RedirectResponse
    {
        $guest = $this->service->update($id, $request->validated());

        return redirect()
            ->route('guests.profiles.show', $guest->id)
            ->with('success', 'Guest profile updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->service->delete($id);

        return redirect()
            ->route('guests.profiles.index')
            ->with('success', 'Guest profile deleted successfully.');
    }

    /**
     * Search guests.
     */
    public function search(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);
        $guests = $this->service->search($filters);
        
        return Inertia::render('Guest/Profiles/Search', [
            'guests' => $guests,
            'filters' => $filters,
        ]);
    }

    /**
     * Get VIP guests.
     */
    public function vip(): Response
    {
        return Inertia::render('Guest/Profiles/Vip', [
            'vipGuests' => $this->service->getVipGuests(),
        ]);
    }
}
