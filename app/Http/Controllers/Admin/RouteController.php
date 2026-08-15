<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteRequest;
use App\Http\Resources\RouteResource;
use App\Models\Route;
use App\Services\RouteService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class RouteController extends Controller
{
    /**
     * Route Service Instance.
     *
     * @var RouteService
     */
    protected RouteService $routeService;

    /**
     * Constructor.
     */
    public function __construct(RouteService $routeService)
    {
        $this->routeService = $routeService;

        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        |
        | Uncomment this once RoutePolicy has been implemented.
        |
        */

        // $this->authorizeResource(Route::class, 'route');
    }

    /**
     * Display a listing of all routes.
     *
     * GET /api/admin/routes
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $routes = $this->routeService->getAllRoutes($request);

            return response()->json([
                'success' => true,
                'message' => 'Routes retrieved successfully.',
                'data' => RouteResource::collection($routes),
            ]);

        } catch (Throwable $e) {

            Log::error('Failed retrieving routes.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve routes.',
            ], 500);
        }
    }

    /**
     * Display a single route.
     *
     * GET /api/admin/routes/{id}
     */
    public function show(string $id): JsonResponse
    {
        try {

            $route = $this->routeService->findRoute($id);

            return response()->json([
                'success' => true,
                'message' => 'Route retrieved successfully.',
                'data' => new RouteResource($route),
            ]);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Route not found.',
            ], 404);

        } catch (Throwable $e) {

            Log::error('Route lookup failed.', [
                'route' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve route.',
            ], 500);
        }
    }

    /**
     * Store a newly created route.
     *
     * POST /api/admin/routes
     */
    public function store(RouteRequest $request): JsonResponse
    {
        try {

            $route = $this->routeService->createRoute(
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Route created successfully.',
                'data' => new RouteResource($route),
            ], 201);

        } catch (Throwable $e) {

            Log::error('Route creation failed.', [
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create route.',
            ], 500);
        }
    }

    /**
     * Update an existing route.
     *
     * PUT/PATCH /api/admin/routes/{id}
     */
    public function update(
        RouteRequest $request,
        string $id
    ): JsonResponse {
        try {

            $route = $this->routeService->updateRoute(
                $id,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Route updated successfully.',
                'data' => new RouteResource($route),
            ]);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Route not found.',
            ], 404);

        } catch (Throwable $e) {

            Log::error('Route update failed.', [
                'route' => $id,
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update route.',
            ], 500);
        }
    }

    /**
     * Delete a route.
     *
     * DELETE /api/admin/routes/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {

            $this->routeService->deleteRoute($id);

            return response()->json([
                'success' => true,
                'message' => 'Route deleted successfully.',
            ]);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Route not found.',
            ], 404);

        } catch (Throwable $e) {

            Log::error('Route deletion failed.', [
                'route' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to delete route.',
            ], 500);
        }
    }

    /**
     * Assign stops to a route.
     *
     * POST /api/admin/routes/{id}/stops
     *
     * Expected payload:
     *
     * {
     *     "stops": [
     *         {
     *             "stop_id": "UUID",
     *             "stop_order": 1,
     *             "distance_from_start": 0
     *         }
     *     ]
     * }
     */
    public function assignStops(
        Request $request,
        string $id
    ): JsonResponse {
        $validated = $request->validate([
            'stops' => [
                'required',
                'array',
                'min:1',
            ],

            'stops.*.stop_id' => [
                'required',
                'exists:stops,id',
            ],

            'stops.*.stop_order' => [
                'required',
                'integer',
                'min:1',
            ],

            'stops.*.distance_from_start' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        try {

            $route = $this->routeService->assignStops(
                $id,
                $validated['stops']
            );

            return response()->json([
                'success' => true,
                'message' => 'Stops assigned successfully.',
                'data' => new RouteResource($route),
            ]);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Route or stop not found.',
            ], 404);

        } catch (Throwable $e) {

            Log::error('Failed assigning stops.', [
                'route' => $id,
                'stops' => $validated['stops'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to assign stops.',
            ], 500);
        }
    }

    /**
     * Remove a stop from a route.
     *
     * DELETE /api/admin/routes/{routeId}/stops/{stopId}
     */
    public function removeStop(
        string $routeId,
        string $stopId
    ): JsonResponse {
        try {

            $this->routeService->removeStop(
                $routeId,
                $stopId
            );

            return response()->json([
                'success' => true,
                'message' => 'Stop removed successfully.',
            ]);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Route or stop not found.',
            ], 404);

        } catch (Throwable $e) {

            Log::error('Failed removing stop.', [
                'route' => $routeId,
                'stop' => $stopId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to remove stop.',
            ], 500);
        }
    }

    /**
     * Activate or deactivate a route.
     *
     * PATCH /api/admin/routes/{id}/toggle-status
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {

            $route = $this->routeService->toggleStatus($id);

            return response()->json([
                'success' => true,
                'message' => 'Route status updated successfully.',
                'data' => new RouteResource($route),
            ]);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Route not found.',
            ], 404);

        } catch (Throwable $e) {

            Log::error('Failed toggling route status.', [
                'route' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update route status.',
            ], 500);
        }
    }

    /**
     * Search routes.
     *
     * GET /api/admin/routes/search
     *
     * Supported filters:
     *
     * - route_code
     * - route_name
     * - area_id
     * - municipality_id
     * - depot_id
     * - status
     * - stop_name
     */
    public function search(Request $request): JsonResponse
    {
        try {

            $routes = $this->routeService->searchRoutes($request);

            return response()->json([
                'success' => true,
                'message' => 'Search completed successfully.',
                'data' => RouteResource::collection($routes),
            ]);

        } catch (Throwable $e) {

            Log::error('Route search failed.', [
                'filters' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed.',
            ], 500);
        }
    }
}