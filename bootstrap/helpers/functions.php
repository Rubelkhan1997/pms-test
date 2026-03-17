<?php

declare(strict_types=1);

use App\Models\Hotel;
use Illuminate\Support\Facades\Session;

if (!function_exists('currentHotel')) {
    /**
     * Get the current hotel from session
     */
    function currentHotel(): ?Hotel
    {
        $hotelId = Session::get('current_hotel_id');
        
        if (!$hotelId) {
            // Try to get first hotel for user
            $hotelId = auth()->user()?->hotels?->first()?->id;
            
            if (!$hotelId) {
                return null;
            }
            
            Session::put('current_hotel_id', $hotelId);
        }
        
        return Hotel::find($hotelId);
    }
}

if (!function_exists('setCurrentHotel')) {
    /**
     * Set the current hotel in session
     */
    function setCurrentHotel(int $hotelId): bool
    {
        $hotel = Hotel::find($hotelId);
        
        if (!$hotel) {
            return false;
        }
        
        Session::put('current_hotel_id', $hotelId);
        
        return true;
    }
}

if (!function_exists('forgetCurrentHotel')) {
    /**
     * Clear the current hotel from session
     */
    function forgetCurrentHotel(): void
    {
        Session::forget('current_hotel_id');
    }
}
