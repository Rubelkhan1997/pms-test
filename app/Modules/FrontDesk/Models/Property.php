<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'type', 'description', 'logo_path', 'featured_image_path',
        'gallery_paths', 'number_of_rooms', 'country', 'state', 'city', 'area', 'street',
        'postal_code', 'latitude', 'longitude', 'phone', 'email', 'timezone', 'currency',
        'check_in_time', 'check_out_time', 'child_policy', 'pet_policy', 'status', 'business_date',
    ];

    protected $casts = [
        'gallery_paths' => 'array',
        'child_policy' => 'array',
        'pet_policy' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'check_in_time' => 'datetime:H:i:s',
        'check_out_time' => 'datetime:H:i:s',
        'business_date' => 'date',
    ];

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}