<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'properties';

    protected $fillable = [
        'name', 'slug', 'type', 'star_rating', 'description',
        'logo_path', 'featured_image_path', 'gallery_paths', 'number_of_rooms',
        'country', 'state', 'city', 'area', 'street', 'postal_code',
        'latitude', 'longitude', 'phone', 'email', 'website', 'tax_id',
        'timezone', 'currency', 'check_in_time', 'check_out_time',
        'child_policy', 'pet_policy', 'status', 'business_date',
    ];

    protected function casts(): array
    {
        return [
            'gallery_paths' => 'array',
            'child_policy'  => 'array',
            'pet_policy'    => 'array',
            'latitude'      => 'decimal:8',
            'longitude'     => 'decimal:8',
            'business_date' => 'date',
            'star_rating'   => 'integer',
        ];
    }

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
