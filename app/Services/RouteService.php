<?php

namespace App\Services;

use App\Models\Route;
use App\Repositories\RouteRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;
use App\Models\Stop;

class RouteService
{
    /**
     * Route Repository.
     */
    protected RouteRepository $routeRepository;

    /**
     * Constructor.
     */
    public function __construct(RouteRepository $routeRepository)
    {
        $this->routeRepository = $routeRepository;
    }

    /**
     * Get all routes.
     *
     * Supports:
     * - Pagination
     * - Search
     * - Filtering
     * - Sorting
     *
     * @param Request $request
     * @return LengthAwarePaginator|Collection
     */
    public function getAllRoutes(Request $request): LengthAwarePaginator|Collection
    {
        try {

            return $this->routeRepository->getAll($request);

        } catch (Throwable $e) {

            Log::error('RouteService::getAllRoutes()', [
                'message' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Find a single route.
     *
     * Loads:
     * - Area
     * - Municipality
     * - Depot
     * - Stops
     * - Fares
     * - Schedules
     *
     * @param string $id
     * @return Route
     *
     * @throws ModelNotFoundException
     */
    public function findRoute(string $id): Route
    {
        $route = $this->routeRepository->find($id);

        if (!$route) {
            throw new ModelNotFoundException(
                "Route not found."
            );
        }

        return $route;
    }

    /**
     * Create a new route.
     *
     * Business Rules
     * ---------------------
     * Route Code must be unique.
     * Route Name must be unique.
     * Municipality must exist.
     * Area must exist.
     * Depot must exist.
     *
     * @param array $data
     * @return Route
     *
     * @throws ValidationException
     */
    public function createRoute(array $data): Route
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Business Rule:
            | Route Code must be unique.
            |--------------------------------------------------------------------------
            */

            if (
                $this->routeRepository
                    ->routeCodeExists($data['route_code'])
            ) {

                throw ValidationException::withMessages([
                    'route_code' => [
                        'Route code already exists.'
                    ]
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Business Rule:
            | Route Name must be unique.
            |--------------------------------------------------------------------------
            */

            if (
                $this->routeRepository
                    ->routeNameExists($data['route_name'])
            ) {

                throw ValidationException::withMessages([
                    'route_name' => [
                        'Route name already exists.'
                    ]
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Default Values
            |--------------------------------------------------------------------------
            */

            $data['active'] ??= true;

            /*
            |--------------------------------------------------------------------------
            | Create Route
            |--------------------------------------------------------------------------
            */

            $route = $this->routeRepository->create($data);

            DB::commit();

            return $route;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('RouteService::createRoute()', [

                'payload' => $data,

                'message' => $e->getMessage()

            ]);

            throw $e;
        }
    }

        /**
     * Update an existing route.
     *
     * Business Rules
     * ---------------------
     * - Route must exist.
     * - Route Code must remain unique.
     * - Route Name must remain unique.
     *
     * @param string $id
     * @param array $data
     * @return Route
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function updateRoute(string $id, array $data): Route
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Verify Route Exists
            |--------------------------------------------------------------------------
            */

            $route = $this->findRoute($id);

            /*
            |--------------------------------------------------------------------------
            | Route Code Validation
            |--------------------------------------------------------------------------
            */

            if (
                isset($data['route_code']) &&
                $this->routeRepository->routeCodeExists(
                    $data['route_code'],
                    $id
                )
            ) {

                throw ValidationException::withMessages([
                    'route_code' => [
                        'Route code already exists.'
                    ]
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Route Name Validation
            |--------------------------------------------------------------------------
            */

            if (
                isset($data['route_name']) &&
                $this->routeRepository->routeNameExists(
                    $data['route_name'],
                    $id
                )
            ) {

                throw ValidationException::withMessages([
                    'route_name' => [
                        'Route name already exists.'
                    ]
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Update Route
            |--------------------------------------------------------------------------
            */

            $updatedRoute = $this->routeRepository->update(
                $route,
                $data
            );

            DB::commit();

            return $updatedRoute;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('RouteService::updateRoute()', [

                'route_id' => $id,

                'payload' => $data,

                'message' => $e->getMessage()

            ]);

            throw $e;
        }
    }

    /**
     * Delete a Route.
     *
     * Business Rules
     * ---------------------
     * - Route must exist.
     * - Route cannot be deleted if it has:
     *      • Schedules
     *      • Tickets
     *      • Bus Cards
     *      • Validation Logs
     *
     * SoftDeletes are recommended.
     *
     * @param string $id
     * @return bool
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function deleteRoute(string $id): bool
    {
        DB::beginTransaction();

        try {

            $route = $this->findRoute($id);

            /*
            |--------------------------------------------------------------------------
            | Prevent deleting routes currently in use
            |--------------------------------------------------------------------------
            */

            if ($route->schedules()->exists()) {

                throw ValidationException::withMessages([
                    'route' => [
                        'This route has schedules and cannot be deleted.'
                    ]
                ]);
            }

            if ($route->tickets()->exists()) {

                throw ValidationException::withMessages([
                    'route' => [
                        'This route has ticket history and cannot be deleted.'
                    ]
                ]);
            }

            if ($route->busCards()->exists()) {

                throw ValidationException::withMessages([
                    'route' => [
                        'This route has active bus cards and cannot be deleted.'
                    ]
                ]);
            }

            if ($route->validationLogs()->exists()) {

                throw ValidationException::withMessages([
                    'route' => [
                        'This route has validation history and cannot be deleted.'
                    ]
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Delete Route
            |--------------------------------------------------------------------------
            */

            $this->routeRepository->delete($route);

            DB::commit();

            return true;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('RouteService::deleteRoute()', [

                'route_id' => $id,

                'message' => $e->getMessage()

            ]);

            throw $e;
        }
    }

        /**
     * Toggle Route Status.
     *
     * Business Rules
     * ----------------------------------------------------
     * • Route must exist.
     * • Active becomes Inactive.
     * • Inactive becomes Active.
     * • Future enhancement:
     *      Prevent deactivation if active schedules exist.
     *
     * @param string $id
     * @return Route
     */
    public function toggleStatus(string $id): Route
    {
        DB::beginTransaction();

        try {

            $route = $this->findRoute($id);

            /*
            |--------------------------------------------------------------------------
            | Future Business Rule
            |--------------------------------------------------------------------------
            |
            | Uncomment once schedules are implemented.
            |
            | if (
            |     $route->active &&
            |     $route->schedules()
            |         ->where('status', 'active')
            |         ->exists()
            | ) {
            |     throw ValidationException::withMessages([
            |         'route' => [
            |             'Cannot deactivate a route with active schedules.'
            |         ]
            |     ]);
            | }
            |
            */

            $route = $this->routeRepository->update(
                $route,
                [
                    'active' => !$route->active
                ]
            );

            DB::commit();

            return $route;

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('RouteService::toggleStatus()', [

                'route_id' => $id,

                'message' => $e->getMessage()

            ]);

            throw $e;
        }
    }

    /**
     * Search Routes.
     *
     * Supports:
     * ----------------------------------------------------
     * route_code
     * route_name
     * area_id
     * municipality_id
     * depot_id
     * active
     * stop_name
     *
     * @param Request $request
     * @return LengthAwarePaginator|Collection
     */
    public function searchRoutes(
        Request $request
    ): LengthAwarePaginator|Collection
    {
        try {

            return $this->routeRepository
                ->search(
                    $request->only([
                        'route_code',
                        'route_name',
                        'area_id',
                        'municipality_id',
                        'depot_id',
                        'active',
                        'stop_name',
                        'per_page',
                        'sort_by',
                        'sort_direction'
                    ])
                );

        } catch (Throwable $e) {

            Log::error('RouteService::searchRoutes()', [

                'filters' => $request->all(),

                'message' => $e->getMessage()

            ]);

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Determine if a route can be deleted.
     *
     * @param Route $route
     * @return bool
     */
    private function canDelete(Route $route): bool
    {
        return
            !$route->schedules()->exists() &&
            !$route->tickets()->exists() &&
            !$route->busCards()->exists() &&
            !$route->validationLogs()->exists();
    }

    /**
     * Ensure stop order values are unique.
     *
     * @param array $stops
     * @return void
     *
     * @throws ValidationException
     */
    private function validateUniqueStopOrder(array $stops): void
    {
        $orders = array_column($stops, 'stop_order');

        if (count($orders) !== count(array_unique($orders))) {

            throw ValidationException::withMessages([
                'stop_order' => [
                    'Duplicate stop order detected.'
                ]
            ]);
        }
    }

    /**
     * Ensure route code is unique.
     *
     * @param string $routeCode
     * @param string|null $ignoreId
     * @return void
     */
    private function validateRouteCode(
        string $routeCode,
        ?string $ignoreId = null
    ): void {

        if (
            $this->routeRepository
                ->routeCodeExists(
                    $routeCode,
                    $ignoreId
                )
        ) {

            throw ValidationException::withMessages([
                'route_code' => [
                    'Route code already exists.'
                ]
            ]);
        }
    }

    /**
     * Ensure route name is unique.
     *
     * @param string $routeName
     * @param string|null $ignoreId
     * @return void
     */
    private function validateRouteName(
        string $routeName,
        ?string $ignoreId = null
    ): void {

        if (
            $this->routeRepository
                ->routeNameExists(
                    $routeName,
                    $ignoreId
                )
        ) {

            throw ValidationException::withMessages([
                'route_name' => [
                    'Route name already exists.'
                ]
            ]);
        }
    }

}