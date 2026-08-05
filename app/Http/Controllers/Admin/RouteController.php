<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Route\StoreRouteRequest;
use App\Http\Requests\Route\UpdateRouteRequest;
use App\Http\Resources\RouteResource;
use App\Models\Route;
use App\Services\RouteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Uncomment when Policies are implemented
        // $this->authorizeResource(Route::class, 'route');
    }

    /**
     * Display all routes.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $routes = $this->routeService->getAllRoutes($request);

            return response()->json([
                'success' => true,
                'message' => 'Routes retrieved successfully.',
                'data' => RouteResource::collection($routes)
            ]);

        } catch (Throwable $e) {

            Log::error('Failed retrieving routes.', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve routes.'
            ],500);
        }
    }

    /**
     * Display a single route.
     */
    public function show(string $id): JsonResponse
    {
        try {

            $route = $this->routeService->findRoute($id);

            return response()->json([
                'success' => true,
                'data' => new RouteResource($route)
            ]);

        } catch (Throwable $e) {

            Log::error('Route lookup failed.',[
                'route'=>$id,
                'error'=>$e->getMessage()
            ]);

            return response()->json([
                'success'=>false,
                'message'=>'Route not found.'
            ],404);

        }
    }

    /**
     * Store a newly created Route.
     */
    public function store(StoreRouteRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {

            $route = $this->routeService->createRoute(
                $request->validated()
            );

            DB::commit();

            return response()->json([
                'success'=>true,
                'message'=>'Route created successfully.',
                'data'=>new RouteResource($route)
            ],201);

        } catch (Throwable $e){

            DB::rollBack();

            Log::error('Route creation failed.',[
                'error'=>$e->getMessage()
            ]);

            return response()->json([
                'success'=>false,
                'message'=>'Unable to create route.'
            ],500);

        }
    }

    /**
     * Update an existing Route.
     */
    public function update(UpdateRouteRequest $request,string $id): JsonResponse
    {
        DB::beginTransaction();

        try{

            $route = $this->routeService->updateRoute(
                $id,
                $request->validated()
            );

            DB::commit();

            return response()->json([
                'success'=>true,
                'message'=>'Route updated successfully.',
                'data'=>new RouteResource($route)
            ]);

        }catch(Throwable $e){

            DB::rollBack();

            Log::error('Route update failed.',[
                'route'=>$id,
                'error'=>$e->getMessage()
            ]);

            return response()->json([
                'success'=>false,
                'message'=>'Unable to update route.'
            ],500);

        }
    }

    /**
     * Delete a Route.
     */
    public function destroy(string $id): JsonResponse
    {
        DB::beginTransaction();

        try{

            $this->routeService->deleteRoute($id);

            DB::commit();

            return response()->json([
                'success'=>true,
                'message'=>'Route deleted successfully.'
            ]);

        }catch(Throwable $e){

            DB::rollBack();

            Log::error('Route deletion failed.',[
                'route'=>$id,
                'error'=>$e->getMessage()
            ]);

            return response()->json([
                'success'=>false,
                'message'=>'Unable to delete route.'
            ],500);

        }
    }

        /**
     * Assign stops to a route.
     *
     * Expected Payload:
     * [
     *     "stops" => [
     *          [
     *              "stop_id" => "...",
     *              "stop_order" => 1,
     *              "distance_from_start" => 0
     *          ]
     *     ]
     * ]
     */
    public function assignStops(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'stops' => ['required', 'array', 'min:1'],
            'stops.*.stop_id' => ['required', 'exists:stops,id'],
            'stops.*.stop_order' => ['required', 'integer', 'min:1'],
            'stops.*.distance_from_start' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {

            $route = $this->routeService->assignStops(
                $id,
                $validated['stops']
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stops assigned successfully.',
                'data' => new RouteResource($route),
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Failed assigning stops.', [
                'route' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to assign stops.',
            ], 500);
        }
    }

    /**
     * Remove a stop from a route.
     */
    public function removeStop(string $routeId, string $stopId): JsonResponse
    {
        DB::beginTransaction();

        try {

            $this->routeService->removeStop(
                $routeId,
                $stopId
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stop removed successfully.',
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Failed removing stop.', [
                'route' => $routeId,
                'stop' => $stopId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to remove stop.',
            ], 500);
        }
    }

    /**
     * Activate / Deactivate Route.
     */
    public function toggleStatus(string $id): JsonResponse
    {
        DB::beginTransaction();

        try {

            $route = $this->routeService->toggleStatus($id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Route status updated successfully.',
                'data' => new RouteResource($route),
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Failed toggling route status.', [
                'route' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update route status.',
            ], 500);
        }
    }

    /**
     * Search Routes.
     *
     * Supports:
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
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed.',
            ], 500);
        }
    }
}
