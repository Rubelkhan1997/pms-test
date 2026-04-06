<?php

declare(strict_types=1);

namespace App\Modules\Guest\Services;

use App\Modules\Guest\Actions\CreateGuestProfileAction;
use App\Modules\Guest\Data\GuestProfileData;
use App\Modules\Guest\Models\GuestProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class GuestProfileService
{
    /**
     * Create a new service instance.
     */
    public function __construct(private CreateGuestProfileAction $createAction)
    {
    }

    /**
     * Return a paginated list.
     */
    public function paginate(): LengthAwarePaginator
    {
        return GuestProfile::query()->latest('id')->paginate(15);
    }

    /**
     * Store a new record.
     *
     * @param array<string, mixed> $payload
     */
    public function create(GuestProfileData $payload): GuestProfile
    {
        return ($this->createAction)($payload);
    }
}

