<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\FrontDesk\Models\Reservation;
use App\Modules\FrontDesk\Models\Room;
use App\Modules\FrontDesk\Observers\ReservationObserver;
use App\Modules\FrontDesk\Observers\RoomObserver;
use App\Modules\Guest\Models\GuestProfile;
use App\Modules\Guest\Observers\GuestProfileObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('login', static function (Request $request): Limit {
            $email = (string) $request->input('email', 'guest');

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        Reservation::observe(ReservationObserver::class);
        Room::observe(RoomObserver::class);
        GuestProfile::observe(GuestProfileObserver::class);
    }
}
