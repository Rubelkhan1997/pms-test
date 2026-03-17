<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Booking\Models\OtaSync;
use App\Modules\Booking\Observers\OtaSyncObserver;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Observers\ReservationObserver;
use App\Modules\Guest\Models\GuestProfile;
use App\Modules\Guest\Observers\GuestProfileObserver;
use App\Modules\Housekeeping\Models\HousekeepingTask;
use App\Modules\Housekeeping\Observers\HousekeepingTaskObserver;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Observers\EmployeeObserver;
use App\Modules\Mobile\Models\MobileTask;
use App\Modules\Mobile\Observers\MobileTaskObserver;
use App\Modules\Pos\Models\PosOrder;
use App\Modules\Pos\Observers\PosOrderObserver;
use App\Modules\Reports\Models\ReportSnapshot;
use App\Modules\Reports\Observers\ReportSnapshotObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        Reservation::observe(ReservationObserver::class);
        OtaSync::observe(OtaSyncObserver::class);
        GuestProfile::observe(GuestProfileObserver::class);
        HousekeepingTask::observe(HousekeepingTaskObserver::class);
        PosOrder::observe(PosOrderObserver::class);
        ReportSnapshot::observe(ReportSnapshotObserver::class);
        MobileTask::observe(MobileTaskObserver::class);
        Employee::observe(EmployeeObserver::class);
    }
}
