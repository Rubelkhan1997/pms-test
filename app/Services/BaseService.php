<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class BaseService
 * 
 * Base service class providing common CRUD operations and utilities.
 * Extend this class for module-specific services.
 * 
 * @template T of Model
 */
abstract class BaseService
{
    /**
     * The model instance
     * 
     * @var T
     */
    protected Model $model;
    
    /**
     * Set the model instance
     * 
     * @param T $model
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }
    
    /**
     * Get paginated results with optional filters
     * 
     * @param array<string, mixed> $filters
     * @param array<string> $relations
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(
        array $filters = [],
        array $relations = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model->query();
        
        // Eager load relationships
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        // Apply filters
        $this->applyFilters($query, $filters);
        
        return $query->latest()->paginate($perPage);
    }
    
    /**
     * Get all records
     * 
     * @param array<string> $relations
     * @return Collection<int, T>
     */
    public function all(array $relations = []): Collection
    {
        return $this->model->with($relations)->get();
    }
    
    /**
     * Find a record by ID
     * 
     * @param int|string $id
     * @param array<string> $relations
     * @return T|null
     */
    public function find(int|string $id, array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id);
    }
    
    /**
     * Find a record by ID or throw exception
     * 
     * @param int|string $id
     * @param array<string> $relations
     * @return T
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int|string $id, array $relations = []): Model
    {
        return $this->model->with($relations)->findOrFail($id);
    }
    
    /**
     * Find by specific column
     * 
     * @param string $column
     * @param mixed $value
     * @param array<string> $relations
     * @return T|null
     */
    public function findBy(string $column, mixed $value, array $relations = []): ?Model
    {
        return $this->model->with($relations)->where($column, $value)->first();
    }
    
    /**
     * Create a new record
     * 
     * @param array<string, mixed> $data
     * @return T
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }
    
    /**
     * Update an existing record
     * 
     * @param int|string $id
     * @param array<string, mixed> $data
     * @return T
     */
    public function update(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        
        return $record->fresh();
    }
    
    /**
     * Delete a record
     * 
     * @param int|string $id
     * @return bool|null
     */
    public function delete(int|string $id): ?bool
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }
    
    /**
     * Restore a soft-deleted record
     * 
     * @param int|string $id
     * @return bool
     */
    public function restore(int|string $id): bool
    {
        $record = $this->model->withTrashed()->findOrFail($id);
        return $record->restore();
    }
    
    /**
     * Permanently delete a record
     * 
     * @param int|string $id
     * @return bool|null
     */
    public function forceDelete(int|string $id): ?bool
    {
        $record = $this->model->withTrashed()->findOrFail($id);
        return $record->forceDelete();
    }
    
    /**
     * Count records
     * 
     * @param array<string, mixed> $filters
     * @return int
     */
    public function count(array $filters = []): int
    {
        $query = $this->model->query();
        $this->applyFilters($query, $filters);
        
        return $query->count();
    }
    
    /**
     * Check if record exists
     * 
     * @param array<string, mixed> $conditions
     * @return bool
     */
    public function exists(array $conditions): bool
    {
        $query = $this->model->query();
        
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }
        
        return $query->exists();
    }
    
    /**
     * Apply filters to query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array<string, mixed> $filters
     * @return void
     */
    protected function applyFilters($query, array $filters): void
    {
        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                $query->where($column, $value);
            }
        }
    }
    
    /**
     * Get status badge color
     * 
     * @param string $status
     * @return string
     */
    protected function statusBadge(string $status): string
    {
        return match (strtolower($status)) {
            'draft' => 'gray',
            'confirmed' => 'blue',
            'checked_in' => 'green',
            'checked_out' => 'teal',
            'cancelled' => 'red',
            'pending' => 'yellow',
            'in_progress' => 'indigo',
            'completed' => 'green',
            'blocked' => 'purple',
            'available' => 'green',
            'occupied' => 'red',
            'dirty' => 'orange',
            'out_of_order' => 'gray',
            default => 'gray',
        };
    }
}
