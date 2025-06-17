<?php

use App\Http\Controllers\PerformanceDataController;
use App\Http\Controllers\TemperatureProfileController;
use App\Http\Controllers\VersionController;
use App\Http\Controllers\VesselConfigurationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('login'));
});

Auth::routes();

Route::resource('users', App\Http\Controllers\UserController::class);
Route::resource('products', App\Http\Controllers\ProductController::class);
Route::resource('versions', VersionController::class);

Route::resource('temperature-profiles', TemperatureProfileController::class);
Route::resource('temperature-profiles', TemperatureProfileController::class);
Route::post('temperature-profiles/bulk-action', [TemperatureProfileController::class, 'bulkAction'])->name('temperature-profiles.bulk-action');

Route::resource('vessel-configurations', VesselConfigurationController::class);
Route::post('vessel-configurations/bulk-action', [VesselConfigurationController::class, 'bulkAction'])->name('vessel-configurations.bulk-action');
Route::post('vessel-configurations/{vesselConfiguration}/duplicate', [VesselConfigurationController::class, 'duplicate'])->name('vessel-configurations.duplicate');
Route::get('vessel-configurations/export', [VesselConfigurationController::class, 'export'])->name('vessel-configurations.export');

Route::resource('performance-data', PerformanceDataController::class);
Route::post('performance-data/bulk-action', [PerformanceDataController::class, 'bulkAction'])->name('performance-data.bulk-action');
Route::get('performance-data/compare', [PerformanceDataController::class, 'compare'])->name('performance-data.compare');
Route::get('performance-data/analytics', [PerformanceDataController::class, 'analytics'])->name('performance-data.analytics');
Route::get('performance-data/export', [PerformanceDataController::class, 'exportPerformanceData'])->name('performance-data.export');
Route::get('api/versions-for-product', [PerformanceDataController::class, 'getVersionsForProduct']);
Route::get('api/vessel-configurations', [PerformanceDataController::class, 'getVesselConfigurations']);

Route::get('versions/{version}/performance', [VersionController::class, 'performance'])->name('versions.performance');
Route::post('versions/bulk-action', [VersionController::class, 'bulkAction'])->name('versions.bulk-action');
Route::get('versions/export', [VersionController::class, 'export'])->name('versions.export');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
