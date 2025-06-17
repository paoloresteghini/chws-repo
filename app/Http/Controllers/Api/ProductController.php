<?php

// File: app/Http/Controllers/API/ProductController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductPerformanceResource;
use App\Http\Resources\ProductResource;
use App\Models\PerformanceData;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     *
     * GET /api/products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['versions' => function($q) {
            $q->where('status', true);
        }]);

        // Optional filtering
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('has_vessel_options')) {
            $query->where('has_vessel_options', $request->boolean('has_vessel_options'));
        }

        if ($request->filled('has_temperature_profiles')) {
            $query->where('has_temperature_profiles', $request->boolean('has_temperature_profiles'));
        }

        $products = $query->orderBy('name')->get();

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'total' => $products->count(),
                'timestamp' => now()->toISOString(),
            ]
        ]);
    }

    /**
     * Display the specified product with performance data
     *
     * GET /api/products/{id}
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        // Load all performance data with related models
        $performanceData = PerformanceData::with([
            'version',
            'temperatureProfile',
            'vesselConfiguration'
        ])
            ->whereHas('version', function($query) use ($product) {
                $query->where('product_id', $product->id)
                    ->where('status', true);
            });

        // Optional filtering by temperature profile
        if ($request->filled('temperature_profile')) {
            $performanceData->whereHas('temperatureProfile', function($query) use ($request) {
                $query->where('name', $request->temperature_profile);
            });
        }

        // Optional filtering by version/model
        if ($request->filled('model')) {
            $performanceData->whereHas('version', function($query) use ($request) {
                $query->where('model_number', $request->model);
            });
        }

        // Optional filtering by vessel capacity
        if ($request->filled('vessel_capacity')) {
            $performanceData->whereHas('vesselConfiguration', function($query) use ($request) {
                $query->where('capacity', $request->vessel_capacity);
            });
        }

        // Optional filtering by heat input range
        if ($request->filled('min_heat_input')) {
            $performanceData->where('heat_input_kw', '>=', $request->min_heat_input);
        }

        if ($request->filled('max_heat_input')) {
            $performanceData->where('heat_input_kw', '<=', $request->max_heat_input);
        }

        // Order by model number, then vessel capacity, then heat input
        $performanceData = $performanceData->join('versions', 'performance_data.version_id', '=', 'versions.id')
            ->leftJoin('vessel_configurations', 'performance_data.vessel_configuration_id', '=', 'vessel_configurations.id')
            ->orderBy('versions.model_number')
            ->orderBy('vessel_configurations.capacity')
            ->orderBy('performance_data.heat_input_kw')
            ->select('performance_data.*')
            ->get();

        // Return the exact array format requested - just the performance data array
        return response()->json(ProductPerformanceResource::collection($performanceData));
    }

    /**
     * Get product statistics
     *
     * GET /api/products/{id}/stats
     */
    public function stats(Product $product): JsonResponse
    {
        $product->load(['versions.performanceData.temperatureProfile', 'versions.vesselConfigurations']);

        $stats = [
            'product_info' => [
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type,
                'has_vessel_options' => $product->has_vessel_options,
                'has_temperature_profiles' => $product->has_temperature_profiles,
            ],
            'versions' => [
                'total' => $product->versions->count(),
                'active' => $product->versions->where('status', true)->count(),
                'with_performance_data' => $product->versions->filter(function($version) {
                    return $version->performanceData->count() > 0;
                })->count(),
            ],
            'performance_data' => [
                'total_records' => $product->versions->sum(function($version) {
                    return $version->performanceData->count();
                }),
                'temperature_profiles' => $product->versions->flatMap->performanceData
                    ->pluck('temperatureProfile.name')
                    ->filter()
                    ->unique()
                    ->count(),
                'heat_input_range' => [
                    'min' => $product->versions->flatMap->performanceData->min('heat_input_kw'),
                    'max' => $product->versions->flatMap->performanceData->max('heat_input_kw'),
                ],
            ],
        ];

        if ($product->has_vessel_options) {
            $stats['vessel_configurations'] = [
                'total' => $product->versions->sum->vesselConfigurations->count(),
                'capacity_range' => [
                    'min' => $product->versions->flatMap->vesselConfigurations->min('capacity'),
                    'max' => $product->versions->flatMap->vesselConfigurations->max('capacity'),
                ],
            ];
        }

        return response()->json([
            'data' => $stats,
            'meta' => [
                'timestamp' => now()->toISOString(),
            ]
        ]);
    }

    /**
     * Get available temperature profiles for a product
     *
     * GET /api/products/{id}/temperature-profiles
     */
    public function temperatureProfiles(Product $product): JsonResponse
    {
        $profiles = $product->versions()
            ->with(['performanceData.temperatureProfile'])
            ->whereHas('performanceData.temperatureProfile')
            ->get()
            ->flatMap->performanceData
            ->pluck('temperatureProfile')
            ->filter()
            ->unique('id')
            ->map(function($profile) {
                return [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'primary_flow_temp' => $profile->primary_flow_temp,
                    'primary_return_temp' => $profile->primary_return_temp,
                    'secondary_flow_temp' => $profile->secondary_flow_temp,
                    'secondary_return_temp' => $profile->secondary_return_temp,
                    'display_name' => $profile->display_name,
                ];
            })
            ->values();

        return response()->json([
            'data' => $profiles,
            'meta' => [
                'total' => $profiles->count(),
                'timestamp' => now()->toISOString(),
            ]
        ]);
    }

    /**
     * Get available models/versions for a product
     *
     * GET /api/products/{id}/models
     */
    public function models(Product $product): JsonResponse
    {
        $models = $product->versions()
            ->where('status', true)
            ->with(['vesselConfigurations', 'performanceData'])
            ->orderBy('model_number')
            ->get()
            ->map(function($version) {
                return [
                    'id' => $version->id,
                    'model_number' => $version->model_number,
                    'name' => $version->name,
                    'has_vessel_options' => $version->has_vessel_options,
                    'vessel_configurations' => $version->vesselConfigurations->map(function($vessel) {
                        return [
                            'id' => $vessel->id,
                            'name' => $vessel->name,
                            'capacity' => $vessel->capacity,
                            'capacity_unit' => $vessel->capacity_unit,
                        ];
                    }),
                    'performance_records_count' => $version->performanceData->count(),
                ];
            });

        return response()->json([
            'data' => $models,
            'meta' => [
                'total' => $models->count(),
                'timestamp' => now()->toISOString(),
            ]
        ]);
    }

    /**
     * Get available filter options for a product
     */
    private function getAvailableFilters(Product $product): array
    {
        $performanceData = PerformanceData::with([
            'version',
            'temperatureProfile',
            'vesselConfiguration'
        ])->whereHas('version', function($query) use ($product) {
            $query->where('product_id', $product->id)->where('status', true);
        })->get();

        return [
            'temperature_profiles' => $performanceData->pluck('temperatureProfile.name')
                ->filter()
                ->unique()
                ->sort()
                ->values(),
            'models' => $performanceData->pluck('version.model_number')
                ->filter()
                ->unique()
                ->sort()
                ->values(),
            'vessel_capacities' => $performanceData->pluck('vesselConfiguration.capacity')
                ->filter()
                ->unique()
                ->sort()
                ->values(),
            'heat_input_range' => [
                'min' => $performanceData->min('heat_input_kw'),
                'max' => $performanceData->max('heat_input_kw'),
            ],
        ];
    }
}
