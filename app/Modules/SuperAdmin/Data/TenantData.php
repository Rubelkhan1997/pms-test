<?php

declare(strict_types=1);

namespace App\Modules\SuperAdmin\Data;

use Spatie\LaravelData\Data;

class TenantData extends Data
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $domain,
        public readonly string  $status,
        public readonly ?string $contact_name  = null,
        public readonly ?string $contact_email = null,
        public readonly ?string $contact_phone = null,
        public readonly ?int    $plan_id       = null,
    ) {}
}