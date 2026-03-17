<?php

declare(strict_types=1);

namespace App\Modules\Hr\Services;

use App\Base\BaseService;
use App\Modules\Hr\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class AttendanceService extends BaseService
{
    public function __construct(
        private Attendance $model
    ) {
        parent::setModel($model);
    }

    /**
     * Create attendance record.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): Attendance
    {
        return $this->model->create($payload);
    }

    /**
     * Update attendance record.
     *
     * @param array<string, mixed> $payload
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $payload): Attendance
    {
        $attendance = $this->findOrFail($id);
        $attendance->update($payload);
        
        return $attendance->fresh(['employee']);
    }

    /**
     * Get attendance for employee by date.
     */
    public function getByDate(int $employeeId, string $date): ?Attendance
    {
        return $this->model->where('employee_id', $employeeId)
            ->where('attendance_date', $date)
            ->first();
    }

    /**
     * Get attendance for date range.
     *
     * @return Collection<int, Attendance>
     */
    public function getByDateRange(int $employeeId, string $startDate, string $endDate): Collection
    {
        return $this->model->where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->orderBy('attendance_date', 'desc')
            ->get();
    }

    /**
     * Get today's attendance.
     *
     * @return Collection<int, Attendance>
     */
    public function getToday(int $hotelId): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('attendance_date', today())
            ->with(['employee'])
            ->get();
    }

    /**
     * Mark check-out for employee.
     *
     * @throws ModelNotFoundException
     */
    public function checkOut(int $attendanceId): Attendance
    {
        $attendance = $this->findOrFail($attendanceId);
        $attendance->update([
            'check_out' => now(),
        ]);
        
        return $attendance->fresh(['employee']);
    }

    /**
     * Get attendance statistics for employee.
     *
     * @return array<string, mixed>
     */
    public function getEmployeeStatistics(int $employeeId, string $month): array
    {
        $startDate = now()->parse($month)->startOfMonth()->toDateString();
        $endDate = now()->parse($month)->endOfMonth()->toDateString();
        
        $attendances = $this->getByDateRange($employeeId, $startDate, $endDate);
        
        return [
            'total_days' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
            'total_hours' => $attendances->sum(fn ($a) => $a->hours_worked ?? 0),
        ];
    }
}
