<?php

// File: app/Http/Controllers/VesselConfigurationController.php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Version;
use App\Models\VesselConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VesselConfigurationController extends Controller
{
    /**
     * Display a listing of vessel configurations
     */
    public function index(Request $request): View
    {
        $query = VesselConfiguration::with(['version.product'])->withCount('performanceData');

        // Apply filters
        if ($request->filled('product_id')) {
            $query->whereHas('version', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        if ($request->filled('version_id')) {
            $query->where('version_id', $request->version_id);
        }

        if ($request->filled('capacity_min')) {
            $query->where('capacity', '>=', $request->capacity_min);
        }

        if ($request->filled('capacity_max')) {
            $query->where('capacity', '<=', $request->capacity_max);
        }

        if ($request->filled('capacity_unit')) {
            $query->where('capacity_unit', $request->capacity_unit);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('version', function ($versionQuery) use ($search) {
                        $versionQuery->where('model_number', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        // Order by product, then version, then capacity
        $configurations = $query->join('versions', 'vessel_configurations.version_id', '=', 'versions.id')
            ->join('products', 'versions.product_id', '=', 'products.id')
            ->orderBy('products.name')
            ->orderBy('versions.model_number')
            ->orderBy('vessel_configurations.capacity')
            ->select('vessel_configurations.*')
            ->get();

        // Get filter options
        $products = Product::where('has_vessel_options', true)->orderBy('name')->get();
        $versions = $request->filled('product_id')
            ? Version::where('product_id', $request->product_id)
                ->where('has_vessel_options', true)
                ->orderBy('model_number')
                ->get()
            : collect();
        $capacityUnits = VesselConfiguration::distinct()->pluck('capacity_unit');

        // Get statistics
        $stats = [
            'total_configurations' => VesselConfiguration::count(),
            'used_configurations' => VesselConfiguration::has('performanceData')->count(),
            'capacity_range' => [
                'min' => VesselConfiguration::min('capacity'),
                'max' => VesselConfiguration::max('capacity'),
            ],
            'products_with_vessels' => Product::where('has_vessel_options', true)->count(),
        ];

        return view('vessel-configurations.index', compact(
            'configurations',
            'products',
            'versions',
            'capacityUnits',
            'stats'
        ));
    }

    /**
     * Store a newly created vessel configuration
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'version_id' => 'required|exists:versions,id',
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|numeric|min:0|max:999999.99',
            'capacity_unit' => 'required|string|max:10',
            'description' => 'nullable|string',
            'spec_keys' => 'nullable|array',
            'spec_values' => 'nullable|array',
        ]);

        // Verify the version supports vessel configurations
        $version = Version::findOrFail($validated['version_id']);
        if (!$version->has_vessel_options) {
            return back()->withErrors(['version_id' => 'Selected version does not support vessel configurations.'])->withInput();
        }

        // Check for duplicate name within the same version
        $existing = VesselConfiguration::where('version_id', $validated['version_id'])
            ->where('name', $validated['name'])
            ->first();
        if ($existing) {
            return back()->withErrors(['name' => 'A vessel configuration with this name already exists for this version.'])->withInput();
        }

        // Process specifications
        $specifications = null;
        if (!empty($validated['spec_keys']) && !empty($validated['spec_values'])) {
            $keys = $validated['spec_keys'];
            $values = $validated['spec_values'];
            $specifications = [];
            
            for ($i = 0; $i < count($keys); $i++) {
                $key = trim($keys[$i]);
                $value = isset($values[$i]) ? trim($values[$i]) : '';
                
                if (!empty($key) && !empty($value)) {
                    $specifications[$key] = $value;
                }
            }
            
            // If no valid specifications, set to null
            if (empty($specifications)) {
                $specifications = null;
            }
        }

        // Remove specification-related fields from validated data and add processed specifications
        unset($validated['spec_keys'], $validated['spec_values']);
        $validated['specifications'] = $specifications;

        $configuration = VesselConfiguration::create($validated);

        return redirect()->route('vessel-configurations.show', $configuration)
            ->with('success', 'Vessel configuration created successfully.');
    }

    /**
     * Show the form for creating a new vessel configuration
     */
    public function create(Request $request): View
    {
        $products = Product::where('has_vessel_options', true)->orderBy('name')->get();

        // If version_id is provided in query string, pre-select it
        $selectedVersion = null;
        if ($request->filled('version_id')) {
            $selectedVersion = Version::find($request->version_id);
        }

        $versions = Version::where('has_vessel_options', true)->with('product')->orderBy('model_number')->get();

        return view('vessel-configurations.create', compact('products', 'versions', 'selectedVersion'));
    }

    /**
     * Display the specified vessel configuration
     */
    public function show(VesselConfiguration $vesselConfiguration): View
    {
        $vesselConfiguration->load(['version.product', 'performanceData.temperatureProfile']);

        // Get performance data grouped by temperature profile
        $performanceByProfile = $vesselConfiguration->performanceData
            ->groupBy('temperature_profile_id')
            ->map(function ($data) {
                return [
                    'profile' => $data->first()->temperatureProfile,
                    'data' => $data->first()
                ];
            });

        // Get performance statistics
        $performanceStats = null;
        if ($vesselConfiguration->performanceData->count() > 0) {
            $performanceStats = $vesselConfiguration->performanceData()
                ->selectRaw('
                    COUNT(*) as total_records,
                    MIN(heat_input_kw) as min_heat,
                    MAX(heat_input_kw) as max_heat,
                    AVG(heat_input_kw) as avg_heat,
                    MIN(pressure_drop_kpa) as min_pressure,
                    MAX(pressure_drop_kpa) as max_pressure,
                    AVG(pressure_drop_kpa) as avg_pressure
                ')
                ->first();
        }

        return view('vessel-configurations.show', compact(
            'vesselConfiguration',
            'performanceByProfile',
            'performanceStats'
        ));
    }

    /**
     * Show the form for editing the specified vessel configuration
     */
    public function edit(VesselConfiguration $vesselConfiguration): View
    {
        $vesselConfiguration->load(['version.product']);
        $products = Product::where('has_vessel_options', true)->orderBy('name')->get();
        $versions = Version::where('has_vessel_options', true)->with('product')->orderBy('model_number')->get();

        return view('vessel-configurations.edit', compact('vesselConfiguration', 'products', 'versions'));
    }

    /**
     * Update the specified vessel configuration
     */
    public function update(Request $request, VesselConfiguration $vesselConfiguration): RedirectResponse
    {
        $validated = $request->validate([
            'version_id' => 'required|exists:versions,id',
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|numeric|min:0|max:999999.99',
            'capacity_unit' => 'required|string|max:10',
            'description' => 'nullable|string',
            'spec_keys' => 'nullable|array',
            'spec_values' => 'nullable|array',
        ]);

        // Verify the version supports vessel configurations
        $version = Version::findOrFail($validated['version_id']);
        if (!$version->has_vessel_options) {
            return back()->withErrors(['version_id' => 'Selected version does not support vessel configurations.'])->withInput();
        }

        // Check for duplicate name within the same version (excluding current configuration)
        $existing = VesselConfiguration::where('version_id', $validated['version_id'])
            ->where('name', $validated['name'])
            ->where('id', '!=', $vesselConfiguration->id)
            ->first();
        if ($existing) {
            return back()->withErrors(['name' => 'A vessel configuration with this name already exists for this version.'])->withInput();
        }

        // Process specifications
        $specifications = null;
        if (!empty($validated['spec_keys']) && !empty($validated['spec_values'])) {
            $keys = $validated['spec_keys'];
            $values = $validated['spec_values'];
            $specifications = [];
            
            for ($i = 0; $i < count($keys); $i++) {
                $key = trim($keys[$i]);
                $value = isset($values[$i]) ? trim($values[$i]) : '';
                
                if (!empty($key) && !empty($value)) {
                    $specifications[$key] = $value;
                }
            }
            
            // If no valid specifications, set to null
            if (empty($specifications)) {
                $specifications = null;
            }
        }

        // Remove specification-related fields from validated data and add processed specifications
        unset($validated['spec_keys'], $validated['spec_values']);
        $validated['specifications'] = $specifications;

        $vesselConfiguration->update($validated);

        return redirect()->route('vessel-configurations.show', $vesselConfiguration)
            ->with('success', 'Vessel configuration updated successfully.');
    }

    /**
     * Remove the specified vessel configuration
     */
    public function destroy(VesselConfiguration $vesselConfiguration): RedirectResponse
    {
        // Check if configuration is in use
        if ($vesselConfiguration->performanceData()->count() > 0) {
            return back()->withErrors(['delete' => 'Cannot delete vessel configuration because it has associated performance data.']);
        }

        $vesselConfiguration->delete();

        return redirect()->route('vessel-configurations.index')
            ->with('success', 'Vessel configuration deleted successfully.');
    }


    /**
     * Bulk operations on vessel configurations
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:delete',
            'configuration_ids' => 'required|array',
            'configuration_ids.*' => 'exists:vessel_configurations,id'
        ]);

        $configurations = VesselConfiguration::whereIn('id', $validated['configuration_ids']);

        switch ($validated['action']) {
            case 'delete':
                // Check if any configurations are in use
                $inUse = $configurations->has('performanceData')->count();
                if ($inUse > 0) {
                    return back()->withErrors(['delete' => "{$inUse} vessel configurations cannot be deleted because they have associated performance data."]);
                }

                $count = $configurations->count();
                $configurations->delete();
                $message = "{$count} vessel configurations have been deleted.";
                break;
        }

        return redirect()->route('vessel-configurations.index')
            ->with('success', $message);
    }

    /**
     * Duplicate vessel configurations to another version
     */
    public function duplicate(Request $request, VesselConfiguration $vesselConfiguration): RedirectResponse
    {
        $validated = $request->validate([
            'target_version_id' => 'required|exists:versions,id',
            'copy_specifications' => 'boolean',
        ]);

        $targetVersion = Version::findOrFail($validated['target_version_id']);

        if (!$targetVersion->has_vessel_options) {
            return back()->withErrors(['target_version_id' => 'Target version does not support vessel configurations.']);
        }

        // Check if configuration with same name already exists
        $existing = VesselConfiguration::where('version_id', $validated['target_version_id'])
            ->where('name', $vesselConfiguration->name)
            ->first();
        if ($existing) {
            return back()->withErrors(['target_version_id' => 'A vessel configuration with this name already exists for the target version.']);
        }

        // Create duplicate
        $newConfiguration = VesselConfiguration::create([
            'version_id' => $validated['target_version_id'],
            'name' => $vesselConfiguration->name,
            'capacity' => $vesselConfiguration->capacity,
            'capacity_unit' => $vesselConfiguration->capacity_unit,
            'description' => $vesselConfiguration->description,
            'specifications' => $validated['copy_specifications'] ? $vesselConfiguration->specifications : null,
        ]);

        return redirect()->route('vessel-configurations.show', $newConfiguration)
            ->with('success', "Vessel configuration duplicated to {$targetVersion->name} ({$targetVersion->model_number}).");
    }

    /**
     * Export vessel configurations
     */
    public function export(Request $request)
    {
        $query = VesselConfiguration::with(['version.product']);

        // Apply same filters as index
        if ($request->filled('product_id')) {
            $query->whereHas('version', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        if ($request->filled('version_id')) {
            $query->where('version_id', $request->version_id);
        }

        $configurations = $query->get();

        $csvData = "Product,Version,Model Number,Vessel Name,Capacity,Unit,Description,Performance Records\n";

        foreach ($configurations as $config) {
            $csvData .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%d\n",
                $config->version->product->name,
                $config->version->name ?? '',
                $config->version->model_number,
                $config->name,
                $config->capacity ?? '',
                $config->capacity_unit,
                str_replace(["\r", "\n", ","], [" ", " ", ";"], $config->description ?? ''),
                $config->performanceData()->count()
            );
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="vessel-configurations-' . now()->format('Y-m-d') . '.csv"');
    }
}
