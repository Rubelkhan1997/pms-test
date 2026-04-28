<?php

declare(strict_types=1);

namespace App\Modules\FrontDesk\Services;

use App\Modules\FrontDesk\Actions\CreatePropertyAction;
use App\Modules\FrontDesk\Data\PropertyData;
use App\Modules\FrontDesk\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class PropertyService
{
    public function __construct(
        private CreatePropertyAction $createAction,
    ) {}

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Property::query();

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('city', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findOrFail(int $id): Property
    {
        return Property::findOrFail($id);
    }

    public function create(PropertyData $data): Property
    {
        return $this->createAction->execute($data);
    }

    public function update(Property $property, PropertyData $data): Property
    {
        $property->update([
            'name'         => $data->name,
            'type'        => $data->type,
            'description' => $data->description,
            'phone'       => $data->phone,
            'email'       => $data->email,
            'country'     => $data->country,
            'state'       => $data->state,
            'city'        => $data->city,
            'area'        => $data->area,
            'street'      => $data->street,
            'postal_code' => $data->postalCode,
            'timezone'    => $data->timezone,
            'currency'   => $data->currency,
        ]);

        return $property->fresh();
    }

    public function delete(Property $property): void
    {
        $property->delete();
    }

    public function count(): int
    {
        return Property::count();
    }
}