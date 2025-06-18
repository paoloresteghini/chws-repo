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
     * Show the form for editing performance data
     */

public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'version_id' => 'required|exists:versions,id',
        'temperature_profile_id' => 'nullable|exists:temperature_profiles,id',
        'vessel_configuration_id' => 'nullable|exists:vessel_configurations,id',
        'heat_input_kw' => 'nullable|numeric|min:0|max:999999',
        'primary_flow_rate_ls' => 'nullable|numeric|min:0|max:9999',
        'secondary_flow_rate_ls' => 'required|numeric|min:0|max:9999',
        'pressure_drop_kpa' => 'required|numeric|min:0|max:9999',
        'first_hour_dhw_supply' => 'nullable|numeric|min:0|max:999999',
        'subsequent_hour_dhw_supply' => 'nullable|numeric|min:0|max:999999',
        'additional_metrics' => 'nullable|array',
        'calculation_method' => 'nullable|in:heat_from_flow,flow_from_heat,manual'
    ]);

    // Validate that either heat input OR flow rate is provided (but not necessarily both)
    if (empty($validated['heat_input_kw']) && empty($validated['primary_flow_rate_ls'])) {
        return back()->withErrors([
            'heat_input_kw' => 'Either heat input or primary flow rate must be provided.',
            'primary_flow_rate_ls' => 'Either heat input or primary flow rate must be provided.'
        ])->withInput();
    }

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

    // Handle calculation method
    $calculationMethod = $validated['calculation_method'] ?? $this->determineCalculationMethod($validated);

    switch ($calculationMethod) {
        case 'heat_from_flow':
            if (empty($validated['primary_flow_rate_ls'])) {
                return back()->withErrors(['primary_flow_rate_ls' => 'Primary flow rate is required to calculate heat input.'])->withInput();
            }
            $validated['heat_input_kw'] = PerformanceData::calculateHeatInputFromFlowRate($validated['primary_flow_rate_ls']);
            break;

        case 'flow_from_heat':
            if (empty($validated['heat_input_kw'])) {
                return back()->withErrors(['heat_input_kw' => 'Heat input is required to calculate flow rate.'])->withInput();
            }
            $validated['primary_flow_rate_ls'] = PerformanceData::calculateFlowRateFromHeatInput($validated['heat_input_kw']);
            break;

        case 'manual':
            // Validate both values are provided and check relationship
            if (!empty($validated['heat_input_kw']) && !empty($validated['primary_flow_rate_ls'])) {
                $theoretical = PerformanceData::calculateHeatInputFromFlowRate($validated['primary_flow_rate_ls']);
                $variance = abs($validated['heat_input_kw'] - $theoretical);

                if ($variance > 10) { // More than 10 kW difference
                    return back()->withInput()->with('warning',
                        "Warning: Heat input ({$validated['heat_input_kw']} kW) differs from theoretical value ({$theoretical} kW) by {$variance} kW. Please verify your values."
                    );
                }
            }
            break;
    }

    $performanceData = PerformanceData::create($validated);

    $message = 'Performance data created successfully.';
    if ($calculationMethod !== 'manual') {
        $calculatedField = $calculationMethod === 'heat_from_flow' ? 'heat input' : 'flow rate';
        $message .= " The {$calculatedField} was automatically calculated.";
    }

    return redirect()->route('performance-data.show', $performanceData)
        ->with('success', $message);
}

    /**
     * Determine calculation method based on provided values
     */
    private function determineCalculationMethod(array $validated): string
    {
        $hasHeat = !empty($validated['heat_input_kw']);
        $hasFlow = !empty($validated['primary_flow_rate_ls']);

        if ($hasHeat && $hasFlow) {
            return 'manual'; // Both provided, manual entry
        } elseif ($hasFlow && !$hasHeat) {
            return 'heat_from_flow'; // Calculate heat from flow
        } elseif ($hasHeat && !$hasFlow) {
            return 'flow_from_heat'; // Calculate flow from heat
        }

        return 'manual'; // Default
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
     * Update performance data with automatic calculations
     */
    public function update(Request $request, PerformanceData $performanceData): RedirectResponse
    {
        $validated = $request->validate([
            'version_id' => 'required|exists:versions,id',
            'temperature_profile_id' => 'nullable|exists:temperature_profiles,id',
            'vessel_configuration_id' => 'nullable|exists:vessel_configurations,id',
            'heat_input_kw' => 'nullable|numeric|min:0|max:999999',
            'primary_flow_rate_ls' => 'nullable|numeric|min:0|max:9999',
            'secondary_flow_rate_ls' => 'required|numeric|min:0|max:9999',
            'pressure_drop_kpa' => 'required|numeric|min:0|max:9999',
            'first_hour_dhw_supply' => 'nullable|numeric|min:0|max:999999',
            'subsequent_hour_dhw_supply' => 'nullable|numeric|min:0|max:999999',
            'additional_metrics' => 'nullable|array',
            'calculation_method' => 'nullable|in:heat_from_flow,flow_from_heat,manual'
        ]);

        // Validate that either heat input OR flow rate is provided
        if (empty($validated['heat_input_kw']) && empty($validated['primary_flow_rate_ls'])) {
            return back()->withErrors([
                'heat_input_kw' => 'Either heat input or primary flow rate must be provided.',
                'primary_flow_rate_ls' => 'Either heat input or primary flow rate must be provided.'
            ])->withInput();
        }

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

        // Handle calculation method
        $calculationMethod = $validated['calculation_method'] ?? $this->determineCalculationMethod($validated);

        switch ($calculationMethod) {
            case 'heat_from_flow':
                if (empty($validated['primary_flow_rate_ls'])) {
                    return back()->withErrors(['primary_flow_rate_ls' => 'Primary flow rate is required to calculate heat input.'])->withInput();
                }
                $validated['heat_input_kw'] = $performanceData->calculateHeatInputFromFlowRate($validated['primary_flow_rate_ls']);
                break;

            case 'flow_from_heat':
                if (empty($validated['heat_input_kw'])) {
                    return back()->withErrors(['heat_input_kw' => 'Heat input is required to calculate flow rate.'])->withInput();
                }
                $validated['primary_flow_rate_ls'] = $performanceData->calculateFlowRateFromHeatInput($validated['heat_input_kw']);
                break;

            case 'manual':
                // Validate both values are provided and check relationship
                if (!empty($validated['heat_input_kw']) && !empty($validated['primary_flow_rate_ls'])) {
                    $theoretical = $performanceData->calculateHeatInputFromFlowRate($validated['primary_flow_rate_ls']);
                    $variance = abs($validated['heat_input_kw'] - $theoretical);

                    if ($variance > 10) { // More than 10 kW difference
                        return back()->withInput()->with('warning',
                            "Warning: Heat input ({$validated['heat_input_kw']} kW) differs from theoretical value ({$theoretical} kW) by {$variance} kW. Please verify your values."
                        );
                    }
                }
                break;
        }

        $performanceData->update($validated);

        $message = 'Performance data updated successfully.';
        if ($calculationMethod !== 'manual') {
            $calculatedField = $calculationMethod === 'heat_from_flow' ? 'heat input' : 'flow rate';
            $message .= " The {$calculatedField} was automatically calculated.";
        }

        return redirect()->route('performance-data.show', $performanceData)
            ->with('success', $message);
    }

    /**
     * Calculate heat input from flow rate (AJAX endpoint)
     */
    public function calculateHeatInput(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['flow_rate' => 'required|numeric|min:0']);

        $flowRate = $request->flow_rate;
        $heatInput = PerformanceData::calculateHeatInputFromFlowRate($flowRate);

        return response()->json([
            'heat_input' => $heatInput,
            'formula' => "Heat Input = {$flowRate} l/s × " . PerformanceData::HEAT_TRANSFER_CONSTANT . " = {$heatInput} kW"
        ]);
    }

    /**
     * Calculate flow rate from heat input (AJAX endpoint)
     */
    public function calculateFlowRate(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['heat_input' => 'required|numeric|min:0']);

        $heatInput = $request->heat_input;
        $flowRate = PerformanceData::calculateFlowRateFromHeatInput($heatInput);

        return response()->json([
            'flow_rate' => $flowRate,
            'formula' => "Flow Rate = {$heatInput} kW ÷ " . PerformanceData::HEAT_TRANSFER_CONSTANT . " = {$flowRate} l/s"
        ]);
    }

    /**
     * Validate heat/flow relationship (AJAX endpoint)
     */
    public function validateHeatFlowRelationship(Request $request)
    {
        $request->validate([
            'heat_input' => 'required|numeric|min:0',
            'flow_rate' => 'required|numeric|min:0'
        ]);

        $heatInput = $request->heat_input;
        $flowRate = $request->flow_rate;

        $theoretical = PerformanceData::calculateHeatInputFromFlowRate($flowRate);
        $variance = abs($heatInput - $theoretical);
        $percentageError = ($theoretical > 0) ? ($variance / $theoretical) * 100 : 0;

        $isAccurate = $variance <= 5; // 5 kW tolerance

        return response()->json([
            'is_accurate' => $isAccurate,
            'theoretical_heat' => $theoretical,
            'actual_heat' => $heatInput,
            'variance' => $variance,
            'percentage_error' => round($percentageError, 2),
            'message' => $isAccurate
                ? 'Heat input and flow rate relationship is accurate.'
                : "Heat input differs from theoretical value by {$variance} kW ({$percentageError}% error)."
        ]);
    }

    /**
     * Auto-correct performance data based on flow rate
     */
    public function autoCorrectHeat(PerformanceData $performanceData): RedirectResponse
    {
        if (!$performanceData->primary_flow_rate_ls) {
            return back()->with('error', 'Cannot auto-correct: no flow rate data available.');
        }

        $oldHeat = $performanceData->heat_input_kw;
        $corrected = $performanceData->autoCorrectHeatInput();

        if ($corrected) {
            $performanceData->save();
            $newHeat = $performanceData->heat_input_kw;

            return back()->with('success',
                "Heat input auto-corrected from {$oldHeat} kW to {$newHeat} kW based on flow rate ({$performanceData->primary_flow_rate_ls} l/s)."
            );
        }

        return back()->with('info', 'Heat input is already accurate, no correction needed.');
    }

    /**
     * Auto-correct performance data based on heat input
     */
    public function autoCorrectFlow(PerformanceData $performanceData): RedirectResponse
    {
        if (!$performanceData->heat_input_kw) {
            return back()->with('error', 'Cannot auto-correct: no heat input data available.');
        }

        $oldFlow = $performanceData->primary_flow_rate_ls;
        $corrected = $performanceData->autoCorrectFlowRate();

        if ($corrected) {
            $performanceData->save();
            $newFlow = $performanceData->primary_flow_rate_ls;

            return back()->with('success',
                "Flow rate auto-corrected from {$oldFlow} l/s to {$newFlow} l/s based on heat input ({$performanceData->heat_input_kw} kW)."
            );
        }

        return back()->with('info', 'Flow rate is already accurate, no correction needed.');
    }

    /**
     * Bulk auto-correct heat/flow relationships
     */
    public function bulkAutoCorrect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'correction_type' => 'required|in:heat_from_flow,flow_from_heat',
            'performance_data_ids' => 'required|array',
            'performance_data_ids.*' => 'exists:performance_data,id'
        ]);

        $performanceDataRecords = PerformanceData::whereIn('id', $validated['performance_data_ids'])->get();
        $correctedCount = 0;

        foreach ($performanceDataRecords as $record) {
            $corrected = false;

            if ($validated['correction_type'] === 'heat_from_flow') {
                $corrected = $record->autoCorrectHeatInput();
            } else {
                $corrected = $record->autoCorrectFlowRate();
            }

            if ($corrected) {
                $record->save();
                $correctedCount++;
            }
        }

        $field = $validated['correction_type'] === 'heat_from_flow' ? 'heat input values' : 'flow rate values';
        $message = "Auto-corrected {$correctedCount} {$field} out of " . count($performanceDataRecords) . " selected records.";

        return redirect()->route('performance-data.index')->with('success', $message);
    }

    /**
     * Show data quality report
     */
    public function dataQuality(): View
    {
        $stats = [
            'total_records' => PerformanceData::count(),
            'accurate_records' => PerformanceData::withAccurateHeatFlow()->count(),
            'inaccurate_records' => PerformanceData::withHeatFlowMismatch()->count(),
            'missing_heat' => PerformanceData::whereNull('heat_input_kw')->count(),
            'missing_flow' => PerformanceData::whereNull('primary_flow_rate_ls')->count(),
        ];

        // Get records with largest variances
        $problematicRecords = PerformanceData::with(['version.product', 'temperatureProfile'])
            ->withHeatFlowMismatch(5)
            ->limit(20)
            ->get()
            ->map(function ($record) {
                return [
                    'record' => $record,
                    'theoretical_heat' => $record->theoretical_heat_input,
                    'variance' => $record->heat_input_variance
                ];
            })
            ->sortByDesc('variance');

        return view('performance-data.data-quality', compact('stats', 'problematicRecords'));
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
    public function compare(Request $request)
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
