<?php

declare(strict_types=1);

namespace App\Modules\Pos\Services;

use App\Base\BaseService;
use App\Modules\Pos\Models\PosMenuItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class MenuItemService extends BaseService
{
    public function __construct(
        private PosMenuItem $model
    ) {
        parent::setModel($model);
    }

    /**
     * Paginate menu items with filters.
     *
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['hotel'])
            ->applyFilters($filters)
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20);
    }

    /**
     * Get menu for hotel.
     *
     * @return Collection<int, PosMenuItem>
     */
    public function getMenu(int $hotelId, ?string $category = null): Collection
    {
        $query = $this->model->where('hotel_id', $hotelId)
            ->where('is_active', true);
        
        if ($category) {
            $query->where('category', $category);
        }
        
        return $query->orderBy('category')->orderBy('name')->get();
    }

    /**
     * Get categories for hotel.
     *
     * @return array<string>
     */
    public function getCategories(int $hotelId): array
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->toArray();
    }

    /**
     * Create menu item.
     *
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): PosMenuItem
    {
        return $this->model->create($payload);
    }

    /**
     * Update menu item.
     *
     * @param array<string, mixed> $payload
     *
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $payload): PosMenuItem
    {
        $item = $this->findOrFail($id);
        $item->update($payload);
        
        return $item->fresh(['hotel']);
    }

    /**
     * Delete menu item.
     *
     * @throws ModelNotFoundException
     */
    public function delete(int $id): bool
    {
        $item = $this->findOrFail($id);
        return $item->delete();
    }

    /**
     * Toggle menu item active status.
     *
     * @throws ModelNotFoundException
     */
    public function toggleActive(int $id): PosMenuItem
    {
        $item = $this->findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        
        return $item->fresh();
    }

    /**
     * Get items by category.
     *
     * @return Collection<int, PosMenuItem>
     */
    public function getByCategory(int $hotelId, string $category): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('category', $category)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Search menu items.
     *
     * @return Collection<int, PosMenuItem>
     */
    public function search(int $hotelId, string $search): Collection
    {
        return $this->model->where('hotel_id', $hotelId)
            ->where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();
    }
}
