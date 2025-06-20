<?php

use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\PerformanceDataController;
use App\Http\Controllers\TemperatureProfileController;
use App\Http\Controllers\VersionCategoryController;
use App\Http\Controllers\VersionController;
use App\Http\Controllers\VesselConfigurationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('login'));
});

Auth::routes();

Route::group(['middleware' => ['auth']], function () {

    Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('logout', App\Http\Controllers\LogoutController::class)->name('logout');

    Route::prefix('documentation')->name('documentation.')->group(function () {
        Route::get('/', [DocumentationController::class, 'index'])->name('index');
        Route::get('/search', [DocumentationController::class, 'search'])->name('search');
        Route::get('/{section}', [DocumentationController::class, 'section'])->name('section');
    });


    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('products', App\Http\Controllers\ProductController::class);
    Route::resource('versions', VersionController::class);

    // Attachment routes
    Route::delete('attachments/{attachment}', [App\Http\Controllers\AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::get('attachments/{attachment}/preview', [App\Http\Controllers\AttachmentController::class, 'preview'])->name('attachments.preview');

    Route::resource('temperature-profiles', TemperatureProfileController::class);
    Route::resource('temperature-profiles', TemperatureProfileController::class);
    Route::post('temperature-profiles/bulk-action', [TemperatureProfileController::class, 'bulkAction'])->name('temperature-profiles.bulk-action');

    Route::resource('version-categories', VersionCategoryController::class);

// Additional Version Category endpoints
    Route::post('version-categories/bulk-action', [VersionCategoryController::class, 'bulkAction'])->name('version-categories.bulk-action');
    Route::post('version-categories/{versionCategory}/assign-versions', [VersionCategoryController::class, 'assignVersions'])->name('version-categories.assign-versions');
    Route::patch('version-categories/update-sort-order', [VersionCategoryController::class, 'updateSortOrder'])->name('version-categories.update-sort-order');
    Route::get('api/categories-for-product', [VersionCategoryController::class, 'getCategoriesForProduct']);

    Route::resource('vessel-configurations', VesselConfigurationController::class);
    Route::post('vessel-configurations/bulk-action', [VesselConfigurationController::class, 'bulkAction'])->name('vessel-configurations.bulk-action');
    Route::post('vessel-configurations/{vesselConfiguration}/duplicate', [VesselConfigurationController::class, 'duplicate'])->name('vessel-configurations.duplicate');
    Route::get('vessel-configurations/export', [VesselConfigurationController::class, 'export'])->name('vessel-configurations.export');

    Route::resource('performance-data', PerformanceDataController::class);

    Route::resource('performance-data', PerformanceDataController::class);

// AJAX Calculation Endpoints
    Route::post('api/calculate-heat-input', [PerformanceDataController::class, 'calculateHeatInput']);
    Route::post('api/calculate-flow-rate', [PerformanceDataController::class, 'calculateFlowRate']);
    Route::post('api/validate-heat-flow', [PerformanceDataController::class, 'validateHeatFlowRelationship']);

// AJAX Data Endpoints
    Route::get('api/versions-for-product', [PerformanceDataController::class, 'getVersionsForProduct']);
    Route::get('api/vessel-configurations', [PerformanceDataController::class, 'getVesselConfigurations']);

// Auto-correction Routes
    Route::patch('performance-data/{performanceData}/auto-correct-heat', [PerformanceDataController::class, 'autoCorrectHeat'])->name('performance-data.auto-correct-heat');
    Route::patch('performance-data/{performanceData}/auto-correct-flow', [PerformanceDataController::class, 'autoCorrectFlow'])->name('performance-data.auto-correct-flow');

// Bulk Operations
    Route::post('performance-data/bulk-auto-correct', [PerformanceDataController::class, 'bulkAutoCorrect'])->name('performance-data.bulk-auto-correct');

// Data Quality
    Route::get('performance-data-quality', [PerformanceDataController::class, 'dataQuality'])->name('performance-data.data-quality');


    Route::post('performance-data/bulk-action', [PerformanceDataController::class, 'bulkAction'])->name('performance-data.bulk-action');
    Route::get('performance-data/compare', [PerformanceDataController::class, 'compare'])->name('performance-data.compare');
    Route::get('performance-data/analytics', [PerformanceDataController::class, 'analytics'])->name('performance-data.analytics');
    Route::get('performance-data/export', [PerformanceDataController::class, 'exportPerformanceData'])->name('performance-data.export');
    Route::get('api/versions-for-product', [PerformanceDataController::class, 'getVersionsForProduct']);
    Route::get('api/vessel-configurations', [PerformanceDataController::class, 'getVesselConfigurations']);
    // In web.php
    Route::post('api/calculate-heat-input', [PerformanceDataController::class, 'calculateHeatInput']);
    Route::post('api/calculate-flow-rate', [PerformanceDataController::class, 'calculateFlowRate']);
    Route::post('api/validate-heat-flow', [PerformanceDataController::class, 'validateHeatFlowRelationship']);

    Route::get('versions/{version}/performance', [VersionController::class, 'performance'])->name('versions.performance');
    Route::post('versions/bulk-action', [VersionController::class, 'bulkAction'])->name('versions.bulk-action');
    Route::get('versions/export', [VersionController::class, 'export'])->name('versions.export');
    Route::get('api/versions-by-product/{productId}', [VersionController::class, 'getVersionsByProduct']);

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

});
