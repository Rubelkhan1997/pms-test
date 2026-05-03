<?php

declare(strict_types=1);

namespace App\Modules\Guest\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestDocument extends Model
{
    use HasFactory;

    protected $table = 'guest_documents';

    protected $fillable = [
        'guest_id', 'type', 'document_number',
        'issuing_country', 'expiry_date', 'scan_path',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
