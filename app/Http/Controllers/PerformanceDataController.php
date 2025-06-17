<?php

// File: app/Http/Controllers/PerformanceDataController.php
namespace App\Http\Controllers;

use App\Models\PerformanceData;
use App\Models\Product;
use App\Models\TemperatureProfile;
use App\Models\Version;
use App\Models\VesselConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceDataController extends Controller
{
    /**
     * Display a listing of performance data with advanced filtering
     */
    public function index(Request $request): View
    {
        $query = PerformanceData::with([
            'version.product',
            'version.category',
            'temperatureProfile',
            'vesselConfiguration'
        ]);

        // Apply filters
        if ($request->filled('product_id')) {
            $query->whereHas('version', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        if ($request->filled('version_id')) {
            $query->where('version_id', $request->version_id);
        }

        if ($request->filled('temperature_profile_id')) {
            $query->where('temperature_profile_id', $request->temperature_profile_id);
        }

        if ($request->filled('vessel_configuration_id')) {
            $query->where('vessel_configuration_id', $request->vessel_configuration_id);
        }

        if ($request->filled('heat_min')) {
            $query->where('heat_input_kw', '>=', $request->heat_min);
        }

        if ($request->filled('heat_max')) {
            $query->where('heat_input_kw', '<=', $request->heat_max);
        }

        if ($request->filled('pressure_min')) {
            $query->where('pressure_drop_kpa', '>=', $request->pressure_min);
        }

        if ($request->filled('pressure_max')) {
            $query->where('pressure_drop_kpa', '<=', $request->pressure_max);
        }

        if ($request->filled('has_dhw')) {
            if ($request->has_dhw === '1') {
                $query->whereNotNull('first_hour_dhw_supply');
            } else {
                $query->whereNull('first_hour_dhw_supply');
            }
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('version', function ($vq) use ($search) {
                    $vq->where('model_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($pq) use ($search) {
                            $pq->where('name', 'like', "%{$search}%");
                        });
                })
                    ->orWhereHas('temperatureProfile', function ($tq) use ($search) {
                        $tq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'heat_input_kw');
        $sortDirection = $request->get('direction', 'asc');

        if (in_array($sortField, ['heat_input_kw', 'primary_flow_rate_ls', 'secondary_flow_rate_ls', 'pressure_drop_kpa', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $performanceData = $query->get();

        // Get filter options
        $products = Product::orderBy('name')->get();
        $versions = $request->filled('product_id')
            ? Version::where('product_id', $request->product_id)->orderBy('model_number')->get()
            : collect();
        $temperatureProfiles = TemperatureProfile::orderBy('name')->get();
        $vesselConfigurations = VesselConfiguration::with('version')->get();

        // Get statistics
        $stats = $this->getPerformanceStats($request);

        return view('performance-data.index', compact(
            'performanceData',
            'products',
            'versions',
            'temperatureProfiles',
            'vesselConfigurations',
            'stats'
        ));
    }

    /**
     * Get performance statistics
     */
    private function getPerformanceStats($request)
    {
        $query = PerformanceData::query();

        // Apply same filters as main query
        if ($request->filled('product_id')) {
            $query->whereHas('version', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        // Get heat input range
        $heatStats = $query->selectRaw('MIN(heat_input_kw) as min_heat, MAX(heat_input_kw) as max_heat')->first();
        $pressureStats = $query->selectRaw('MIN(pressure_drop_kpa) as min_pressure, MAX(pressure_drop_kpa) as max_pressure')->first();

        return [
            'total_records' => $query->count(),
            'avg_heat_input' => $query->avg('heat_input_kw') ?: 0,
            'max_heat_input' => $query->max('heat_input_kw') ?: 0,
            'avg_pressure_drop' => $query->avg('pressure_drop_kpa') ?: 0,
            'dhw_records' => $query->whereNotNull('first_hour_dhw_supply')->count(),
            'unique_versions' => $query->distinct('version_id')->count(),
            'unique_profiles' => $query->distinct('temperature_profile_id')->count(),
            'heat_range' => [
                'min' => $heatStats->min_heat ?: 0,
                'max' => $heatStats->max_heat ?: 0,
            ],
            'pressure_range' => [
                'min' => $pressureStats->min_pressure ?: 0,
                'max' => $pressureStats->max_pressure ?: 0,
            ],
        ];
    }

    /**
     * Store newly created performance data
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'version_id' => 'required|exists:versions,id',
            'temperature_profile_id' => 'nullable|exists:temperature_profiles,id',
            'vessel_configuration_id' => 'nullable|exists:vessel_configurations,id',
            'heat_input_kw' => 'required|numeric|min:0|max:999999',
            'primary_flow_rate_ls' => 'required|numeric|min:0|max:9999',
            'secondary_flow_rate_ls' => 'required|numeric|min:0|max:9999',
            'pressure_drop_kpa' => 'required|numeric|min:0|max:9999',
            'first_hour_dhw_supply' => 'nullable|numeric|min:0|max:999999',
            'subsequent_hour_dhw_supply' => 'nullable|numeric|min:0|max:999999',
            'additional_metrics' => 'nullable|array',
        ]);

        // Validate vessel configuration belongs to version
        if ($validated['vessel_configuration_id']) {
            $vesselConfig = VesselConfiguration::find($validated['vessel_configuration_id']);
            if ($vesselConfig->version_id !== $validated['version_id']) {
                return back()->withErrors(['vessel_configuration_id' => 'Vessel configuration does not belong to the selected version.'])->withInput();
            }
        }

        // Check for duplicate performance data
        $existing = PerformanceData::where([
            'version_id' => $validated['version_id'],
            'temperature_profile_id' => $validated['temperature_profile_id'],
            'vessel_configuration_id' => $validated['vessel_configuration_id'],
        ])->first();

        if ($existing) {
            return back()->withErrors(['version_id' => 'Performance data already exists for this combination of version, temperature profile, and vessel configuration.'])->withInput();
        }

        $performanceData = PerformanceData::create($validated);

        return redirect()->route('performance-data.show', $performanceData)
            ->with('success', 'Performance data created successfully.');
    }

    /**
     * Show the form for creating new performance data
     */
    public function create(Request $request): View
    {
        $products = Product::orderBy('name')->get();
        $temperatureProfiles = TemperatureProfile::where('is_active', true)->orderBy('name')->get();

        // Pre-select version if provided
        $selectedVersion = $request->filled('version_id')
            ? Version::with(['product', 'vesselConfigurations'])->find($request->version_id)
            : null;

        return view('performance-data.create', compact('products', 'temperatureProfiles', 'selectedVersion'));
    }

    /**
     * Display the specified performance data
     */
    public function show($id): View
    {
        // Find the performance data record
        $performanceData = PerformanceData::with([
            'version.product',
            'version.category',
            'temperatureProfile',
            'vesselConfiguration'
        ])->find($id);

        // Check if record exists
        if (!$performanceData) {
            abort(404, 'Performance data record not found');
        }

        // Check if version exists
        if (!$performanceData->version) {
            abort(404, 'Performance data has no associated version');
        }

        // Get related performance data for comparison
        $relatedData = PerformanceData::where('version_id', $performanceData->version_id)
            ->where('id', '!=', $performanceData->id)
            ->with(['temperatureProfile', 'vesselConfiguration'])
            ->limit(5)
            ->get();

        // Get efficiency calculations (with null safety)
        $efficiencyMetrics = $this->calculateEfficiencyMetrics($performanceData);

        return view('performance-data.show', compact('performanceData', 'relatedData', 'efficiencyMetrics'));
    }

    /**
     * Calculate efficiency metrics
     */
    private function calculateEfficiencyMetrics($performanceData)
    {
        $heatInput = $performanceData->heat_input_kw ?: 0;
        $primaryFlow = $performanceData->primary_flow_rate_ls ?: 0;
        $secondaryFlow = $performanceData->secondary_flow_rate_ls ?: 0;
        $pressureDrop = $performanceData->pressure_drop_kpa ?: 0;

        return [
            'heat_transfer_efficiency' => ($primaryFlow > 0) ? ($heatInput / ($primaryFlow * 60)) : 0,
            'pressure_efficiency' => ($pressureDrop > 0) ? ($heatInput / $pressureDrop) : 0,
            'flow_ratio' => ($primaryFlow > 0) ? ($secondaryFlow / $primaryFlow) : 0,
            'specific_heat_transfer' => (($primaryFlow + $secondaryFlow) > 0) ? ($heatInput / ($primaryFlow + $secondaryFlow)) : 0,
        ];
    }

    /**
     * Show the form for editing performance data
     */
    /**
     * Show the form for editing performance data
     */
    public function edit($id): View
    {
        // Find the performance data record with relationships
        $performanceData = PerformanceData::with([
            'version.product',
            'version.vesselConfigurations',
            'temperatureProfile'
        ])->find($id);

        // Check if record exists
        if (!$performanceData) {
            abort(404, 'Performance data record not found');
        }

        // Check if version exists
        if (!$performanceData->version) {
            abort(404, 'Performance data has no associated version');
        }

        $products = Product::orderBy('name')->get();
        $temperatureProfiles = TemperatureProfile::where('is_active', true)->orderBy('name')->get();

        // Safely get vessel configurations
        $vesselConfigurations = $performanceData->version->vesselConfigurations ?? collect();

        return view('performance-data.edit', compact(
            'performanceData',
            'products',
            'temperatureProfiles',
            'vesselConfigurations'
        ));
    }

    /**
     * Update the specified performance data
     */
    public function update(Request $request, PerformanceData $performanceData): RedirectResponse
    {
        $validated = $request->validate([
            'version_id' => 'required|exists:versions,id',
            'temperature_profile_id' => 'nullable|exists:temperature_profiles,id',
            'vessel_configuration_id' => 'nullable|exists:vessel_configurations,id',
            'heat_input_kw' => 'required|numeric|min:0|max:999999',
            'primary_flow_rate_ls' => 'required|numeric|min:0|max:9999',
            'secondary_flow_rate_ls' => 'required|numeric|min:0|max:9999',
            'pressure_drop_kpa' => 'required|numeric|min:0|max:9999',
            'first_hour_dhw_supply' => 'nullable|numeric|min:0|max:999999',
            'subsequent_hour_dhw_supply' => 'nullable|numeric|min:0|max:999999',
            'additional_metrics' => 'nullable|array',
        ]);

        // Validate vessel configuration belongs to version
        if ($validated['vessel_configuration_id']) {
            $vesselConfig = VesselConfiguration::find($validated['vessel_configuration_id']);
            if ($vesselConfig->version_id !== $validated['version_id']) {
                return back()->withErrors(['vessel_configuration_id' => 'Vessel configuration does not belong to the selected version.'])->withInput();
            }
        }

        // Check for duplicate performance data (excluding current record)
        $existing = PerformanceData::where([
            'version_id' => $validated['version_id'],
            'temperature_profile_id' => $validated['temperature_profile_id'],
            'vessel_configuration_id' => $validated['vessel_configuration_id'],
        ])->where('id', '!=', $performanceData->id)->first();

        if ($existing) {
            return back()->withErrors(['version_id' => 'Performance data already exists for this combination of version, temperature profile, and vessel configuration.'])->withInput();
        }

        $performanceData->update($validated);

        return redirect()->route('performance-data.show', $performanceData)
            ->with('success', 'Performance data updated successfully.');
    }

    /**
     * Remove the specified performance data
     */
    public function destroy(PerformanceData $performanceData): RedirectResponse
    {
        $performanceData->delete();

        return redirect()->route('performance-data.index')
            ->with('success', 'Performance data deleted successfully.');
    }

    /**
     * Bulk operations on performance data
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,export',
            'performance_data_ids' => 'required|array',
            'performance_data_ids.*' => 'exists:performance_data,id'
        ]);

        $performanceData = PerformanceData::whereIn('id', $validated['performance_data_ids']);

        switch ($validated['action']) {
            case 'delete':
                $count = $performanceData->count();
                $performanceData->delete();
                $message = "{$count} performance data records have been deleted.";
                break;

            case 'export':
                return $this->exportPerformanceData($performanceData->get());
        }

        return redirect()->route('performance-data.index')
            ->with('success', $message ?? 'Bulk action completed successfully.');
    }

    /**
     * Export performance data to Excel/CSV
     */
    private function exportPerformanceData($data)
    {
        $csvData = "Product,Version,Model Number,Temperature Profile,Vessel,Heat Input (kW),Primary Flow (l/s),Secondary Flow (l/s),Pressure Drop (kPa),First Hour DHW (L),Subsequent DHW (L),Efficiency Ratio,Created At\n";

        foreach ($data as $record) {
            $csvData .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $record->version->product->name,
                $record->version->name ?: '',
                $record->version->model_number,
                $record->temperatureProfile->name ?? '',
                $record->vesselConfiguration->name ?? '',
                $record->heat_input_kw,
                $record->primary_flow_rate_ls,
                $record->secondary_flow_rate_ls,
                $record->pressure_drop_kpa,
                $record->first_hour_dhw_supply ?? '',
                $record->subsequent_hour_dhw_supply ?? '',
                $record->efficiency_ratio,
                $record->created_at->format('Y-m-d H:i:s')
            );
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="performance-data-export-' . now()->format('Y-m-d') . '.csv"');
    }

    /**
     * Get versions for a specific product (AJAX)
     */
    public function getVersionsForProduct(Request $request)
    {
        $productId = $request->product_id;

        $versions = Version::where('product_id', $productId)
            ->where('status', true)
            ->orderBy('model_number')
            ->get(['id', 'model_number', 'name', 'has_vessel_options']);

        return response()->json($versions);
    }

    /**
     * Get vessel configurations for a specific version (AJAX)
     */
    public function getVesselConfigurations(Request $request)
    {
        $versionId = $request->version_id;

        $vesselConfigs = VesselConfiguration::where('version_id', $versionId)
            ->orderBy('capacity')
            ->get(['id', 'name', 'capacity', 'capacity_unit']);

        return response()->json($vesselConfigs);
    }

    /**
     * Compare performance data
     */
    public function compare(Request $request): View
    {
        $performanceDataIds = $request->input('ids', []);

        if (count($performanceDataIds) < 2) {
            return redirect()->route('performance-data.index')
                ->with('error', 'Please select at least 2 performance data records to compare.');
        }

        $performanceData = PerformanceData::whereIn('id', $performanceDataIds)
            ->with(['version.product', 'temperatureProfile', 'vesselConfiguration'])
            ->get();

        $comparisonMetrics = $this->generateComparisonMetrics($performanceData);

        return view('performance-data.compare', compact('performanceData', 'comparisonMetrics'));
    }

    /**
     * Generate comparison metrics
     */
    private function generateComparisonMetrics($performanceData)
    {
        $metrics = [];
        $fields = ['heat_input_kw', 'primary_flow_rate_ls', 'secondary_flow_rate_ls', 'pressure_drop_kpa'];

        foreach ($fields as $field) {
            $values = $performanceData->pluck($field)->filter();
            $metrics[$field] = [
                'min' => $values->min(),
                'max' => $values->max(),
                'avg' => $values->avg(),
                'range' => $values->max() - $values->min(),
            ];
        }

        return $metrics;
    }

    /**
     * Analytics dashboard for performance data
     */
    public function analytics(Request $request): View
    {
        $filters = $request->only(['product_id', 'date_from', 'date_to']);

        $analytics = [
            'overview' => $this->getAnalyticsOverview($filters),
            'trends' => $this->getPerformanceTrends($filters),
            'efficiency' => $this->getEfficiencyAnalysis($filters),
            'distributions' => $this->getDataDistributions($filters),
        ];

        $products = Product::orderBy('name')->get();

        return view('performance-data.analytics', compact('analytics', 'products'));
    }

    /**
     * Get analytics overview
     */
    private function getAnalyticsOverview($filters)
    {
        // Implementation for analytics overview
        return [];
    }

    /**
     * Get performance trends
     */
    private function getPerformanceTrends($filters)
    {
        // Implementation for performance trends
        return [];
    }

    /**
     * Get efficiency analysis
     */
    private function getEfficiencyAnalysis($filters)
    {
        // Implementation for efficiency analysis
        return [];
    }

    /**
     * Get data distributions
     */
    private function getDataDistributions($filters)
    {
        // Implementation for data distributions
        return [];
    }

    /**
     * Import performance data from Excel
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'product_id' => 'required|exists:products,id'
        ]);

        // This would typically use a package like Laravel Excel
        // Implementation would parse Excel and create performance data records

        return redirect()->route('performance-data.index')
            ->with('success', 'Performance data imported successfully.');
    }
}
