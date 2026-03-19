<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HrDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Front Office', 'code' => 'FO'],
            ['name' => 'Housekeeping', 'code' => 'HK'],
            ['name' => 'Food & Beverage', 'code' => 'FB'],
            ['name' => 'Maintenance', 'code' => 'MAINT'],
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Accounting', 'code' => 'ACC'],
            ['name' => 'Sales & Marketing', 'code' => 'SM'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['code' => $department['code']],
                array_merge($department, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $designations = [
            ['name' => 'General Manager', 'code' => 'GM', 'level' => 10],
            ['name' => 'Department Manager', 'code' => 'MGR', 'level' => 8],
            ['name' => 'Supervisor', 'code' => 'SUP', 'level' => 5],
            ['name' => 'Senior Staff', 'code' => 'SR', 'level' => 3],
            ['name' => 'Staff', 'code' => 'STF', 'level' => 2],
            ['name' => 'Intern', 'code' => 'INT', 'level' => 1],
        ];

        foreach ($designations as $designation) {
            DB::table('designations')->updateOrInsert(
                ['code' => $designation['code']],
                array_merge($designation, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $shifts = [
            ['name' => 'Morning Shift', 'code' => 'MOR', 'start_time' => '06:00:00', 'end_time' => '14:00:00', 'is_night_shift' => false],
            ['name' => 'Afternoon Shift', 'code' => 'AFT', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'is_night_shift' => false],
            ['name' => 'Night Shift', 'code' => 'NGT', 'start_time' => '22:00:00', 'end_time' => '06:00:00', 'is_night_shift' => true],
        ];

        foreach ($shifts as $shift) {
            DB::table('shifts')->updateOrInsert(
                ['code' => $shift['code']],
                array_merge($shift, [
                    'break_minutes' => 60,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $leaveTypes = [
            ['name' => 'Annual Leave', 'code' => 'AL', 'max_days_per_year' => 30, 'is_paid' => true, 'requires_approval' => true, 'carry_forward_allowed' => true, 'max_carry_forward_days' => 15],
            ['name' => 'Sick Leave', 'code' => 'SL', 'max_days_per_year' => 14, 'is_paid' => true, 'requires_approval' => true, 'carry_forward_allowed' => false, 'max_carry_forward_days' => 0],
            ['name' => 'Casual Leave', 'code' => 'CL', 'max_days_per_year' => 7, 'is_paid' => true, 'requires_approval' => true, 'carry_forward_allowed' => false, 'max_carry_forward_days' => 0],
            ['name' => 'Maternity Leave', 'code' => 'ML', 'max_days_per_year' => 90, 'is_paid' => true, 'requires_approval' => true, 'carry_forward_allowed' => false, 'max_carry_forward_days' => 0],
            ['name' => 'Paternity Leave', 'code' => 'PL', 'max_days_per_year' => 7, 'is_paid' => true, 'requires_approval' => true, 'carry_forward_allowed' => false, 'max_carry_forward_days' => 0],
            ['name' => 'Unpaid Leave', 'code' => 'UL', 'max_days_per_year' => null, 'is_paid' => false, 'requires_approval' => true, 'carry_forward_allowed' => false, 'max_carry_forward_days' => 0],
        ];

        foreach ($leaveTypes as $leaveType) {
            DB::table('leave_types')->updateOrInsert(
                ['code' => $leaveType['code']],
                array_merge($leaveType, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

