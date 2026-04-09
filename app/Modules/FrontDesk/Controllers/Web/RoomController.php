<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\FrontDesk\Models\Hotel;
use App\Modules\FrontDesk\Resources\RoomResource;
use App\Modules\FrontDesk\Services\RoomService;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

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
    public function index(): Response
    {
        return Inertia::render('FrontDesk/Room/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        try {
            $hotels = Hotel::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();

            return Inertia::render('FrontDesk/Room/Create', [
                'hotels' => $hotels,
            ]);
        } catch (Throwable $e) {
            logger()->error('Error loading room create form: ' . $e->getMessage());
            abort(500, 'Failed to load form');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        try {
            $room = $this->service->find($id);

            return Inertia::render('FrontDesk/Room/Show', [
                'room' => new RoomResource($room),
            ]);
        } catch (Throwable $e) {
            logger()->error('Error loading room: ' . $e->getMessage());
            abort(404, 'Room not found');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        try {
            $room = $this->service->find($id);
            $hotels = Hotel::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();

            return Inertia::render('FrontDesk/Room/Edit', [
                'room' => new RoomResource($room),
                'hotels' => $hotels,
            ]);
        } catch (Throwable $e) {
            logger()->error('Error loading room edit form: ' . $e->getMessage());
            abort(404, 'Room not found');
        }
    }
}
