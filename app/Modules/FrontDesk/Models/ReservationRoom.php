<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use App\Modules\RateAvailability\Models\MealPlan;
use App\Modules\RateAvailability\Models\RatePlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationRoom extends Model
{
    use HasFactory;

    protected $table = 'reservation_rooms';

    protected $fillable = [
        'reservation_id', 'property_id', 'room_type_id', 'room_id',
        'rate_plan_id', 'meal_plan_id',
        'check_in_date', 'check_out_date', 'nights',
        'adults', 'children', 'rate_amount', 'total_amount',
        'status', 'actual_check_in_at', 'actual_check_out_at',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date'       => 'date',
            'check_out_date'      => 'date',
            'rate_amount'         => 'decimal:2',
            'total_amount'        => 'decimal:2',
            'actual_check_in_at'  => 'datetime',
            'actual_check_out_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class);
    }
}
