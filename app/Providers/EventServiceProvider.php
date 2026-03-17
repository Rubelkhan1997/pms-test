<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Booking\Events\OtaSyncCompleted;
use App\Modules\Booking\Listeners\LogOtaSyncCompleted;
use App\Modules\FrontDesk\Events\ReservationCheckedIn;
use App\Modules\FrontDesk\Events\ReservationCheckedOut;
use App\Modules\FrontDesk\Events\ReservationCreated;
use App\Modules\FrontDesk\Listeners\SendReservationCheckedInNotification;
use App\Modules\FrontDesk\Listeners\SendReservationCheckedOutNotification;
use App\Modules\FrontDesk\Listeners\SendReservationCreatedNotification;
use App\Modules\Guest\Events\GuestProfileCreated;
use App\Modules\Guest\Listeners\SendGuestWelcomeNotification;
use App\Modules\Housekeeping\Events\HousekeepingTaskCreated;
use App\Modules\Housekeeping\Listeners\DispatchHousekeepingStaffAlert;
use App\Modules\Hr\Events\EmployeeCreated;
use App\Modules\Hr\Listeners\SendEmployeeOnboardingNotification;
use App\Modules\Mobile\Events\MobileTaskCreated;
use App\Modules\Mobile\Listeners\PushMobileTaskNotification;
use App\Modules\Pos\Events\PosOrderPlaced;
use App\Modules\Pos\Listeners\SendPosOrderToKitchen;
use App\Modules\Reports\Events\ReportSnapshotGenerated;
use App\Modules\Reports\Listeners\CacheReportSnapshot;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ReservationCreated::class => [SendReservationCreatedNotification::class],
        ReservationCheckedIn::class => [SendReservationCheckedInNotification::class],
        ReservationCheckedOut::class => [SendReservationCheckedOutNotification::class],
        OtaSyncCompleted::class => [LogOtaSyncCompleted::class],
        GuestProfileCreated::class => [SendGuestWelcomeNotification::class],
        HousekeepingTaskCreated::class => [DispatchHousekeepingStaffAlert::class],
        PosOrderPlaced::class => [SendPosOrderToKitchen::class],
        ReportSnapshotGenerated::class => [CacheReportSnapshot::class],
        MobileTaskCreated::class => [PushMobileTaskNotification::class],
        EmployeeCreated::class => [SendEmployeeOnboardingNotification::class],
    ];
}
