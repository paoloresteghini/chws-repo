<?php

// File: routes/api.php
use App\Http\Controllers\API\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API versioning (optional but recommended)
Route::prefix('v1')->group(function () {

    // Product endpoints
    Route::apiResource('products', ProductController::class)->only(['index', 'show']);

    // Additional product endpoints
    Route::prefix('products/{product}')->group(function () {
        Route::get('stats', [ProductController::class, 'stats']);
        Route::get('temperature-profiles', [ProductController::class, 'temperatureProfiles']);
        Route::get('models', [ProductController::class, 'models']);
    });

    // Health check and docs inside v1 as well
    Route::get('health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    });

    Route::get('docs', function () {
        return response()->json([
            'api_version' => '1.0.0',
            'base_url' => url('/api/v1'),
            'endpoints' => [
                'GET /api/v1/products' => 'List all products with optional filtering',
                'GET /api/v1/products/{id}' => 'Get product performance data with optional filtering',
                'GET /api/v1/products/{id}/stats' => 'Get product statistics',
                'GET /api/v1/products/{id}/temperature-profiles' => 'Get available temperature profiles for product',
                'GET /api/v1/products/{id}/models' => 'Get available models/versions for product',
                'GET /api/v1/health' => 'API health check',
                'GET /api/v1/docs' => 'This documentation',
            ],
            'filters' => [
                'products' => [
                    'type' => 'Filter by product type (heat_exchanger, dhw_system, etc.)',
                    'has_vessel_options' => 'Filter by vessel support (true/false)',
                    'has_temperature_profiles' => 'Filter by temperature profile support (true/false)',
                ],
                'products/{id}' => [
                    'temperature_profile' => 'Filter by temperature profile name (e.g., 80-60,10-60)',
                    'model' => 'Filter by model number',
                    'vessel_capacity' => 'Filter by vessel capacity',
                    'min_heat_input' => 'Minimum heat input (kW)',
                    'max_heat_input' => 'Maximum heat input (kW)',
                ]
            ],
            'example_urls' => [
                url('/api/v1/products') => 'Get all products',
                url('/api/v1/products?type=dhw_system') => 'Get DHW products only',
                url('/api/v1/products/1') => 'Get all performance data for product 1',
                url('/api/v1/products/1?temperature_profile=80-60,10-60') => 'Get performance data for specific temperature profile',
                url('/api/v1/products/1?model=200&vessel_capacity=196') => 'Get performance data for specific model and vessel',
            ]
        ]);
    });

});

// API Documentation endpoint (works at both /api/docs and /api/v1/docs)
Route::get('docs', function () {
    return response()->json([
        'api_version' => '1.0.0',
        'base_url' => url('/api/v1'),
        'endpoints' => [
            'GET /api/v1/products' => 'List all products with optional filtering',
            'GET /api/v1/products/{id}' => 'Get product performance data with optional filtering',
            'GET /api/v1/products/{id}/stats' => 'Get product statistics',
            'GET /api/v1/products/{id}/temperature-profiles' => 'Get available temperature profiles for product',
            'GET /api/v1/products/{id}/models' => 'Get available models/versions for product',
            'GET /api/v1/health' => 'API health check',
            'GET /api/v1/docs' => 'This documentation',
        ],
        'filters' => [
            'products' => [
                'type' => 'Filter by product type (heat_exchanger, dhw_system, etc.)',
                'has_vessel_options' => 'Filter by vessel support (true/false)',
                'has_temperature_profiles' => 'Filter by temperature profile support (true/false)',
            ],
            'products/{id}' => [
                'temperature_profile' => 'Filter by temperature profile name (e.g., 80-60,10-60)',
                'model' => 'Filter by model number',
                'vessel_capacity' => 'Filter by vessel capacity',
                'min_heat_input' => 'Minimum heat input (kW)',
                'max_heat_input' => 'Maximum heat input (kW)',
            ]
        ],
        'example_urls' => [
            url('/api/v1/products') => 'Get all products',
            url('/api/v1/products?type=dhw_system') => 'Get DHW products only',
            url('/api/v1/products/1') => 'Get all performance data for product 1',
            url('/api/v1/products/1?temperature_profile=80-60,10-60') => 'Get performance data for specific temperature profile',
            url('/api/v1/products/1?model=200&vessel_capacity=196') => 'Get performance data for specific model and vessel',
        ]
    ]);
});
