<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Booking\Models\OtaSync;
use App\Modules\Booking\Observers\OtaSyncObserver;
use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Observers\ReservationObserver;
use App\Modules\Guest\Models\GuestProfile;
use App\Modules\Guest\Observers\GuestProfileObserver;
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
        Reservation::observe(ReservationObserver::class);
        OtaSync::observe(OtaSyncObserver::class);
        GuestProfile::observe(GuestProfileObserver::class);
    }
}
