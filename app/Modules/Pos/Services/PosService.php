<?php

declare(strict_types=1);

namespace App\Modules\Pos\Services;

use App\Base\BaseService;
use App\Modules\Pos\Enums\POSOrderStatus;
use App\Modules\Pos\Models\PosOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class PosService extends BaseService
{
    public function __construct(
        private PosOrder $model
    ) {
        parent::setModel($model);
    }

    /**
     * Paginate POS orders with filters.
     *
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['hotel', 'createdBy', 'reservation'])
            ->applyFilters($filters)
            ->latest('scheduled_at')
            ->paginate(15);
    }

    /**
     * Find order by ID with relationships.
     */
    public function find(int $id, array $relations = ['hotel', 'createdBy', 'reservation']): ?PosOrder
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find order by ID or throw exception.
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id, array $relations = ['hotel', 'createdBy', 'reservation']): PosOrder
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    /**
     * Create POS order with reference number.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): PosOrder
    {
        $payload['reference'] = $this->generateReference();
        $payload['status'] = $payload['status'] ?? POSOrderStatus::Draft->value;
        
        return $this->model->create($payload);
    }

    /**
     * Update POS order.
     *
     * @param array<string, mixed> $payload
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $payload): PosOrder
    {
        $order = $this->findOrFail($id);
        $order->update($payload);
        
        return $order->fresh(['hotel', 'createdBy']);
    }

    /**
     * Delete POS order.
     *
     * @throws ModelNotFoundException
     */
    public function delete(int $id): bool
    {
        $order = $this->findOrFail($id);
        return $order->delete();
    }

    /**
     * Update order status.
     *
     * @throws ModelNotFoundException
     */
    public function updateStatus(int $id, POSOrderStatus $status): PosOrder
    {
        $order = $this->findOrFail($id);
        $order->update(['status' => $status->value]);
        
        if ($status === POSOrderStatus::Settled) {
            $order->update(['settled_at' => now()]);
        } elseif ($status === POSOrderStatus::Served) {
            $order->update(['served_at' => now()]);
        }
        
        return $order->fresh(['hotel', 'createdBy']);
    }

    /**
     * Charge order to room (add to reservation folio).
     *
     * @throws ModelNotFoundException
     */
    public function chargeToRoom(int $orderId, int $reservationId): PosOrder
    {
        $order = $this->findOrFail($orderId);
        $reservation = \App\Modules\FrontDesk\Models\Reservation::findOrFail($reservationId);
        
        $order->update([
            'reservation_id' => $reservationId,
            'guest_name' => $reservation->guestProfile->full_name ?? 'N/A',
            'room_number' => $reservation->room->number ?? 'N/A',
        ]);
        
        // Add to reservation folio
        $reservation->update([
            'total_amount' => $reservation->total_amount + $order->total_amount,
        ]);
        
        // Update order status to submitted
        $order->update(['status' => POSOrderStatus::Submitted->value]);
        
        return $order->fresh(['reservation']);
    }

    /**
     * Get orders by outlet.
     *
     * @return Collection<int, PosOrder>
     */
    public function getByOutlet(int $hotelId, string $outlet): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('outlet', $outlet)
            ->whereDate('scheduled_at', today())
            ->latest('scheduled_at')
            ->get();
    }

    /**
     * Get today's orders for hotel.
     *
     * @return Collection<int, PosOrder>
     */
    public function getTodayOrders(int $hotelId): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->whereDate('scheduled_at', today())
            ->latest('scheduled_at')
            ->get();
    }

    /**
     * Get orders by status.
     *
     * @return Collection<int, PosOrder>
     */
    public function getByStatus(int $hotelId, POSOrderStatus $status): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('status', $status->value)
            ->latest('scheduled_at')
            ->get();
    }

    /**
     * Get order statistics for hotel.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(int $hotelId): array
    {
        $total = $this->model->where('hotel_id', $hotelId)->count();
        $todayRevenue = $this->model->where('hotel_id', $hotelId)
            ->whereDate('created_at', today())
            ->where('status', POSOrderStatus::Settled->value)
            ->sum('total_amount');
        
        return [
            'total' => $total,
            'draft' => $this->getByStatus($hotelId, POSOrderStatus::Draft)->count(),
            'submitted' => $this->getByStatus($hotelId, POSOrderStatus::Submitted)->count(),
            'served' => $this->getByStatus($hotelId, POSOrderStatus::Served)->count(),
            'settled' => $this->getByStatus($hotelId, POSOrderStatus::Settled)->count(),
            'today_revenue' => $todayRevenue,
        ];
    }

    /**
     * Generate unique reference number.
     */
    private function generateReference(): string
    {
        return 'POS-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
