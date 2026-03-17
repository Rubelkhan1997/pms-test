<?php

declare(strict_types=1);

namespace App\Modules\Guest\Services;

use App\Base\BaseService;
use App\Modules\Guest\Models\GuestProfile;
use App\Services\ReferenceGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class GuestProfileService extends BaseService
{
    public function __construct(
        private GuestProfile $model,
        private ReferenceGenerator $referenceGenerator
    ) {
        parent::setModel($model);
    }

    /**
     * Paginate guest profiles with filters.
     *
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['hotel', 'agent', 'creator'])
            ->applyFilters($filters)
            ->latest('id')
            ->paginate(15);
    }

    /**
     * Find guest by ID with relationships.
     */
    public function find(int $id, array $relations = ['hotel', 'agent', 'creator', 'reservations']): ?GuestProfile
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Find guest by ID or throw exception.
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id, array $relations = ['hotel', 'agent', 'creator', 'reservations']): GuestProfile
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    /**
     * Create guest profile with reference number.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): GuestProfile
    {
        // Generate unique reference
        $payload['reference'] = $this->generateUniqueReference();
        $payload['status'] = $payload['status'] ?? 'active';
        
        return $this->model->create($payload);
    }

    /**
     * Update guest profile.
     *
     * @param array<string, mixed> $payload
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $payload): GuestProfile
    {
        $guest = $this->findOrFail($id);
        $guest->update($payload);
        
        return $guest->fresh(['hotel', 'agent', 'creator']);
    }

    /**
     * Delete guest profile.
     *
     * @throws ModelNotFoundException
     */
    public function delete(int $id): bool
    {
        $guest = $this->findOrFail($id);
        return $guest->delete();
    }

    /**
     * Search guests by name or email.
     *
     * @param array<string, mixed> $filters
     *
     * @return Collection<int, GuestProfile>
     */
    public function search(array $filters = []): Collection
    {
        $query = $this->model->query()
            ->with(['hotel', 'agent']);
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['hotel_id'])) {
            $query->where('hotel_id', $filters['hotel_id']);
        }
        
        return $query->latest('id')->get();
    }

    /**
     * Get guest by email.
     */
    public function findByEmail(string $email, int $hotelId): ?GuestProfile
    {
        return $this->model->where('email', $email)
            ->where('hotel_id', $hotelId)
            ->first();
    }

    /**
     * Check if guest email exists.
     */
    public function emailExists(string $email, int $hotelId, ?int $excludeId = null): bool
    {
        $query = $this->model->where('email', $email)
            ->where('hotel_id', $hotelId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Get guest stay history.
     *
     * @return Collection<int, \App\Modules\FrontDesk\Models\Reservation>
     */
    public function getStayHistory(int $guestId): Collection
    {
        $guest = $this->findOrFail($guestId, ['reservations']);
        
        return $guest->reservations()
            ->with(['room', 'hotel'])
            ->latest('check_in_date')
            ->get();
    }

    /**
     * Get VIP guests.
     *
     * @return Collection<int, GuestProfile>
     */
    public function getVipGuests(): Collection
    {
        return $this->model->whereJsonContains('meta->vip', true)
            ->with(['hotel', 'agent'])
            ->latest('id')
            ->get();
    }

    /**
     * Generate unique reference number.
     */
    private function generateUniqueReference(): string
    {
        $maxAttempts = 10;
        $attempt = 0;
        
        do {
            $reference = $this->referenceGenerator->generate('GST');
            $attempt++;
            
            if (!$this->model->where('reference', $reference)->exists()) {
                return $reference;
            }
        } while ($attempt < $maxAttempts);
        
        return $this->referenceGenerator->generate('GST') . '-' . strtoupper(\Str::random(4));
    }
}
