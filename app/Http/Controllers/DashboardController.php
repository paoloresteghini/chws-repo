<?php

// File: app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\PerformanceData;
use App\Models\Product;
use App\Models\TemperatureProfile;
use App\Models\Version;
use App\Models\VersionCategory;
use App\Models\VesselConfiguration;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard
     */
    public function index(): View
    {
        $stats = $this->getSystemStats();
        $charts = $this->getChartData();
        $recentActivity = $this->getRecentActivity();
        $systemHealth = $this->getSystemHealth();

        return view('dashboard.index', compact('stats', 'charts', 'recentActivity', 'systemHealth'));
    }

    /**
     * Get comprehensive system statistics
     */
    private function getSystemStats(): array
    {
        return [
            'products' => [
                'total' => Product::count(),
                'with_temperature_profiles' => Product::where('has_temperature_profiles', true)->count(),
                'with_vessel_options' => Product::where('has_vessel_options', true)->count(),
                'by_type' => Product::select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray(),
            ],
            'versions' => [
                'total' => Version::count(),
                'active' => Version::where('status', true)->count(),
                'inactive' => Version::where('status', false)->count(),
                'with_performance_data' => Version::has('performanceData')->count(),
                'with_vessel_configs' => Version::has('vesselConfigurations')->count(),
                'by_product' => Version::join('products', 'versions.product_id', '=', 'products.id')
                    ->select('products.name', DB::raw('count(*) as count'))
                    ->groupBy('products.name')
                    ->pluck('count', 'name')
                    ->toArray(),
            ],
            'categories' => [
                'total' => VersionCategory::count(),
                'with_versions' => VersionCategory::has('versions')->count(),
                'avg_versions_per_category' => round(Version::whereNotNull('category_id')->count() /
                    max(VersionCategory::has('versions')->count(), 1), 1),
            ],
            'temperature_profiles' => [
                'total' => TemperatureProfile::count(),
                'active' => TemperatureProfile::where('is_active', true)->count(),
                'in_use' => TemperatureProfile::has('performanceData')->count(),
                'temp_range' => [
                    'primary_min' => TemperatureProfile::min('primary_flow_temp'),
                    'primary_max' => TemperatureProfile::max('primary_flow_temp'),
                    'secondary_min' => TemperatureProfile::min('secondary_flow_temp'),
                    'secondary_max' => TemperatureProfile::max('secondary_flow_temp'),
                ],
            ],
            'vessel_configurations' => [
                'total' => VesselConfiguration::count(),
                'capacity_range' => [
                    'min' => VesselConfiguration::min('capacity'),
                    'max' => VesselConfiguration::max('capacity'),
                ],
                'by_product' => VesselConfiguration::join('versions', 'vessel_configurations.version_id', '=', 'versions.id')
                    ->join('products', 'versions.product_id', '=', 'products.id')
                    ->select('products.name', DB::raw('count(*) as count'))
                    ->groupBy('products.name')
                    ->pluck('count', 'name')
                    ->toArray(),
            ],
            'performance_data' => [
                'total' => PerformanceData::count(),
                'with_dhw_data' => PerformanceData::whereNotNull('first_hour_dhw_supply')->count(),
                'heat_range' => [
                    'min' => PerformanceData::min('heat_input_kw'),
                    'max' => PerformanceData::max('heat_input_kw'),
                    'avg' => round(PerformanceData::avg('heat_input_kw'), 1),
                ],
                'pressure_range' => [
                    'min' => PerformanceData::min('pressure_drop_kpa'),
                    'max' => PerformanceData::max('pressure_drop_kpa'),
                    'avg' => round(PerformanceData::avg('pressure_drop_kpa'), 1),
                ],
                'by_product' => PerformanceData::join('versions', 'performance_data.version_id', '=', 'versions.id')
                    ->join('products', 'versions.product_id', '=', 'products.id')
                    ->select('products.name', DB::raw('count(*) as count'))
                    ->groupBy('products.name')
                    ->pluck('count', 'name')
                    ->toArray(),
            ],
        ];
    }

    /**
     * Get data for dashboard charts
     */
    private function getChartData(): array
    {
        return [
            'versions_by_product' => Version::join('products', 'versions.product_id', '=', 'products.id')
                ->select('products.name as product', DB::raw('count(*) as count'))
                ->groupBy('products.name')
                ->get(),

            'performance_by_month' => PerformanceData::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->get(),

            'heat_distribution' => PerformanceData::select(
                DB::raw('CASE
                        WHEN heat_input_kw < 100 THEN "< 100 kW"
                        WHEN heat_input_kw < 250 THEN "100-250 kW"
                        WHEN heat_input_kw < 500 THEN "250-500 kW"
                        ELSE "> 500 kW"
                    END as heat_range'),
                DB::raw('count(*) as count')
            )
                ->groupBy('heat_range')
                ->get(),

            'temperature_profile_usage' => TemperatureProfile::select('name', DB::raw('count(performance_data.id) as usage_count'))
                ->leftJoin('performance_data', 'temperature_profiles.id', '=', 'performance_data.temperature_profile_id')
                ->groupBy('temperature_profiles.id', 'name')
                ->orderBy('usage_count', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Get recent activity data
     */
    private function getRecentActivity(): array
    {
        $recentVersions = Version::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($version) {
                return [
                    'type' => 'version_created',
                    'title' => "New version {$version->model_number} created",
                    'subtitle' => $version->product->name,
                    'time' => $version->created_at,
                    'url' => route('versions.show', $version->id),
                ];
            });

        $recentPerformance = PerformanceData::with(['version.product', 'temperatureProfile'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($performance) {
                return [
                    'type' => 'performance_added',
                    'title' => "Performance data added for {$performance->version->model_number}",
                    'subtitle' => "{$performance->temperatureProfile->name} - {$performance->heat_input_kw} kW",
                    'time' => $performance->created_at,
                    'url' => route('versions.show', $performance->version->id),
                ];
            });

        $recentCategories = VersionCategory::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($category) {
                return [
                    'type' => 'category_created',
                    'title' => "Category '{$category->name}' created",
                    'subtitle' => $category->product->name,
                    'time' => $category->created_at,
                    'url' => route('version-categories.show', $category->id),
                ];
            });

        return $recentVersions->concat($recentPerformance)
            ->concat($recentCategories)
            ->sortByDesc('time')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Get system health indicators
     */
    private function getSystemHealth(): array
    {
        $totalVersions = Version::count();
        $versionsWithPerformance = Version::has('performanceData')->count();
        $totalProfiles = TemperatureProfile::count();
        $profilesInUse = TemperatureProfile::has('performanceData')->count();

        return [
            'data_completeness' => [
                'versions_with_performance' => [
                    'percentage' => $totalVersions > 0 ? round(($versionsWithPerformance / $totalVersions) * 100, 1) : 0,
                    'count' => $versionsWithPerformance,
                    'total' => $totalVersions,
                    'status' => $this->getHealthStatus(($versionsWithPerformance / max($totalVersions, 1)) * 100),
                ],
                'profiles_utilization' => [
                    'percentage' => $totalProfiles > 0 ? round(($profilesInUse / $totalProfiles) * 100, 1) : 0,
                    'count' => $profilesInUse,
                    'total' => $totalProfiles,
                    'status' => $this->getHealthStatus(($profilesInUse / max($totalProfiles, 1)) * 100),
                ],
            ],
            'data_consistency' => [
                'uncategorized_versions' => Version::whereNull('category_id')->count(),
                'inactive_versions' => Version::where('status', false)->count(),
                'unused_profiles' => TemperatureProfile::doesntHave('performanceData')->count(),
                'empty_vessel_configs' => VesselConfiguration::doesntHave('performanceData')->count(),
            ],
            'recent_additions' => [
                'versions_last_week' => Version::where('created_at', '>=', now()->subWeek())->count(),
                'performance_last_week' => PerformanceData::where('created_at', '>=', now()->subWeek())->count(),
                'profiles_last_month' => TemperatureProfile::where('created_at', '>=', now()->subMonth())->count(),
            ],
        ];
    }

    /**
     * Get health status based on percentage
     */
    private function getHealthStatus(float $percentage): string
    {
        if ($percentage >= 80) return 'excellent';
        if ($percentage >= 60) return 'good';
        if ($percentage >= 40) return 'fair';
        return 'poor';
    }

    /**
     * Get quick actions for dashboard
     */
    public function getQuickActions(): array
    {
        return [
            [
                'title' => 'Add New Version',
                'description' => 'Create a new product version',
                'icon' => 'ki-plus',
                'url' => route('versions.create'),
                'color' => 'primary',
            ],
            [
                'title' => 'Import Performance Data',
                'description' => 'Upload Excel files with performance data',
                'icon' => 'ki-file-up',
                'url' => '#', // Would link to import page
                'color' => 'success',
            ],
            [
                'title' => 'Create Temperature Profile',
                'description' => 'Add new temperature configurations',
                'icon' => 'ki-thermometer',
                'url' => route('temperature-profiles.create'),
                'color' => 'info',
            ],
            [
                'title' => 'Manage Categories',
                'description' => 'Organize product versions',
                'icon' => 'ki-category',
                'url' => route('version-categories.index'),
                'color' => 'warning',
            ],
        ];
    }
}
