<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Resources\HotelResource;
use App\Modules\FrontDesk\Services\HotelService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class HotelController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly HotelService $service) {}

    /**
     * Display a listing page.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('FrontDesk/Hotel/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('FrontDesk/Hotel/Create');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        try {
            $hotel = $this->service->find($id);

            return Inertia::render('FrontDesk/Hotel/Show', [
                'hotel' => new HotelResource($hotel),
            ]);
        } catch (Throwable $e) {
            logger()->error('Error loading hotel: ' . $e->getMessage());
            abort(404, 'Hotel not found');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        try {
            $hotel = $this->service->find($id);

            return Inertia::render('FrontDesk/Hotel/Edit', [
                'hotel' => new HotelResource($hotel),
            ]);
        } catch (Throwable $e) {
            logger()->error('Error loading hotel: ' . $e->getMessage());
            abort(404, 'Hotel not found');
        }
    }
}
