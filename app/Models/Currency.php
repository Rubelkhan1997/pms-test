<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'flag',
        'conversion_rate',
        'is_active',
        'is_default',
        'created_by',
        'updated_by',
    ];
}
