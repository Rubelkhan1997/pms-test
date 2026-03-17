<?php

declare(strict_types=1);

namespace App\Modules\Hr\Services;

use App\Base\BaseService;
use App\Modules\Hr\Models\Payroll;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class PayrollService extends BaseService
{
    public function __construct(
        private Payroll $model
    ) {
        parent::setModel($model);
    }

    /**
     * Create payroll record.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Payroll
    {
        // Calculate net amount if not provided
        if (!isset($payload['net_amount']) && isset($payload['gross_amount']) && isset($payload['deduction_amount'])) {
            $payload['net_amount'] = $payload['gross_amount'] - $payload['deduction_amount'];
        }
        
        $payload['status'] = $payload['status'] ?? 'draft';
        
        return $this->model->create($payload);
    }

    /**
     * Update payroll record.
     *
     * @param array<string, mixed> $payload
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $payload): Payroll
    {
        $payroll = $this->findOrFail($id);
        
        // Recalculate net amount if gross or deduction changed
        if (isset($payload['gross_amount']) || isset($payload['deduction_amount'])) {
            $gross = $payload['gross_amount'] ?? $payroll->gross_amount;
            $deduction = $payload['deduction_amount'] ?? $payroll->deduction_amount;
            $payload['net_amount'] = $gross - $deduction;
        }
        
        $payroll->update($payload);
        
        return $payroll->fresh(['employee']);
    }

    /**
     * Approve payroll.
     *
     * @throws ModelNotFoundException
     */
    public function approve(int $id): Payroll
    {
        $payroll = $this->findOrFail($id);
        $payroll->update(['status' => 'approved']);
        
        return $payroll->fresh(['employee']);
    }

    /**
     * Mark payroll as paid.
     *
     * @throws ModelNotFoundException
     */
    public function markPaid(int $id): Payroll
    {
        $payroll = $this->findOrFail($id);
        $payroll->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        
        return $payroll->fresh(['employee']);
    }

    /**
     * Get payroll for employee by period.
     */
    public function getByPeriod(int $employeeId, string $periodStart, string $periodEnd): ?Payroll
    {
        return $this->model->where('employee_id', $employeeId)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->first();
    }

    /**
     * Get payroll for employee by month.
     *
     * @return Collection<int, Payroll>
     */
    public function getByMonth(int $employeeId, string $month): Collection
    {
        $startDate = now()->parse($month)->startOfMonth()->toDateString();
        $endDate = now()->parse($month)->endOfMonth()->toDateString();
        
        return $this->model->where('employee_id', $employeeId)
            ->whereBetween('period_start', [$startDate, $endDate])
            ->orderBy('period_start', 'desc')
            ->get();
    }

    /**
     * Get pending payroll for hotel.
     *
     * @return Collection<int, Payroll>
     */
    public function getPending(int $hotelId): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('status', 'draft')
            ->orderBy('period_end', 'desc')
            ->get();
    }

    /**
     * Get payroll statistics for hotel.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(int $hotelId): array
    {
        $total = $this->model->where('hotel_id', $hotelId)->count();
        $pending = $this->model->where('hotel_id', $hotelId)->where('status', 'draft')->count();
        $approved = $this->model->where('hotel_id', $hotelId)->where('status', 'approved')->count();
        $paid = $this->model->where('hotel_id', $hotelId)->where('status', 'paid')->count();
        
        $totalAmount = $this->model->where('hotel_id', $hotelId)
            ->where('status', 'paid')
            ->sum('net_amount');
        
        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'paid' => $paid,
            'total_paid_amount' => $totalAmount,
        ];
    }
}
