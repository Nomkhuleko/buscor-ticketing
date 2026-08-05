<?php

namespace App\Repositories;

use App\Models\Route;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class RouteRepository
{
    /**
     * Get all routes.
     */
    public function getAll(Request $request): LengthAwarePaginator|Collection
    {
        $query = Route::query()
            ->with([
                'area',
                'municipality',
                'depot',
                'stops'
            ]);

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('municipality_id')) {
            $query->where('municipality_id', $request->municipality_id);
        }

        if ($request->filled('depot_id')) {
            $query->where('depot_id', $request->depot_id);
        }

        if ($request->filled('route_code')) {
            $query->where('route_code', 'like', "%{$request->route_code}%");
        }

        if ($request->filled('route_name')) {
            $query->where('route_name', 'like', "%{$request->route_name}%");
        }

        $sortBy = $request->get('sort_by', 'route_code');
        $sortDirection = $request->get('sort_direction', 'asc');

        $query->orderBy($sortBy, $sortDirection);

        if ($request->filled('per_page')) {
            return $query->paginate($request->per_page);
        }

        return $query->get();
    }

    /**
     * Find a route by ID.
     */
    public function find(string $id): ?Route
    {
        return Route::with([
            'area',
            'municipality',
            'depot',
            'stops',
            'fares',
            'schedules'
        ])->find($id);
    }

    /**
     * Create a route.
     */
    public function create(array $data): Route
    {
        return Route::create($data);
    }

    /**
     * Update a route.
     */
    public function update(Route $route, array $data): Route
    {
        $route->update($data);

        return $route->fresh([
            'area',
            'municipality',
            'depot',
            'stops'
        ]);
    }

    /**
     * Delete a route.
     */
    public function delete(Route $route): bool
    {
        return $route->delete();
    }

    /**
     * Search routes.
     */
    public function search(array $filters): LengthAwarePaginator|Collection
    {
        $query = Route::query()
            ->with([
                'area',
                'municipality',
                'depot',
                'stops'
            ]);

        if (!empty($filters['route_code'])) {
            $query->where('route_code', 'like', '%' . $filters['route_code'] . '%');
        }

        if (!empty($filters['route_name'])) {
            $query->where('route_name', 'like', '%' . $filters['route_name'] . '%');
        }

        if (!empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }

        if (!empty($filters['municipality_id'])) {
            $query->where('municipality_id', $filters['municipality_id']);
        }

        if (!empty($filters['depot_id'])) {
            $query->where('depot_id', $filters['depot_id']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (!empty($filters['stop_name'])) {
            $query->whereHas('stops', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['stop_name'] . '%');
            });
        }

        $sortBy = $filters['sort_by'] ?? 'route_code';
        $sortDirection = $filters['sort_direction'] ?? 'asc';

        $query->orderBy($sortBy, $sortDirection);

        if (!empty($filters['per_page'])) {
            return $query->paginate($filters['per_page']);
        }

        return $query->get();
    }

    /**
     * Check whether a route code already exists.
     */
    public function routeCodeExists(
        string $routeCode,
        ?string $ignoreId = null
    ): bool {

        $query = Route::where('route_code', $routeCode);

        if ($ignoreId) {
            $query->where('id', '<>', $ignoreId);
        }

        return $query->exists();
    }

    /**
     * Check whether a route name already exists.
     */
    public function routeNameExists(
        string $routeName,
        ?string $ignoreId = null
    ): bool {

        $query = Route::where('route_name', $routeName);

        if ($ignoreId) {
            $query->where('id', '<>', $ignoreId);
        }

        return $query->exists();
    }
}