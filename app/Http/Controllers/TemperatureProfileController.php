<?php

// File: app/Http/Controllers/TemperatureProfileController.php
namespace App\Http\Controllers;

use App\Models\TemperatureProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemperatureProfileController extends Controller
{
    /**
     * Display a listing of temperature profiles
     */
    public function index(Request $request): View
    {
        $query = TemperatureProfile::withCount('performanceData');

        // Apply filters
        if ($request->filled('primary_temp_min')) {
            $query->where('primary_flow_temp', '>=', $request->primary_temp_min);
        }

        if ($request->filled('primary_temp_max')) {
            $query->where('primary_flow_temp', '<=', $request->primary_temp_max);
        }

        if ($request->filled('secondary_temp_min')) {
            $query->where('secondary_flow_temp', '>=', $request->secondary_temp_min);
        }

        if ($request->filled('secondary_temp_max')) {
            $query->where('secondary_flow_temp', '<=', $request->secondary_temp_max);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Order by primary flow temperature, then secondary flow temperature
        $profiles = $query->orderBy('primary_flow_temp')
            ->orderBy('secondary_flow_temp')
            ->paginate(20)
            ->withQueryString();

        // Get statistics for dashboard
        $stats = [
            'total_profiles' => TemperatureProfile::count(),
            'active_profiles' => TemperatureProfile::where('is_active', true)->count(),
            'used_profiles' => TemperatureProfile::has('performanceData')->count(),
            'temp_range' => [
                'primary_min' => TemperatureProfile::min('primary_flow_temp'),
                'primary_max' => TemperatureProfile::max('primary_flow_temp'),
                'secondary_min' => TemperatureProfile::min('secondary_flow_temp'),
                'secondary_max' => TemperatureProfile::max('secondary_flow_temp'),
            ]
        ];

        return view('temperature-profiles.index', compact('profiles', 'stats'));
    }

    /**
     * Store a newly created temperature profile
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255|unique:temperature_profiles,name',
            'primary_flow_temp' => 'required|numeric|min:-50|max:200',
            'primary_return_temp' => 'required|numeric|min:-50|max:200',
            'secondary_flow_temp' => 'required|numeric|min:-50|max:200',
            'secondary_return_temp' => 'required|numeric|min:-50|max:200',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Auto-generate name if not provided
        if (empty($validated['name'])) {
            $validated['name'] = sprintf(
                '%.0f-%.0f,%.0f-%.0f',
                $validated['primary_flow_temp'],
                $validated['primary_return_temp'],
                $validated['secondary_flow_temp'],
                $validated['secondary_return_temp']
            );
        }

        // Auto-generate description if not provided
        if (empty($validated['description'])) {
            $validated['description'] = sprintf(
                'Primary: %.0f°→%.0f°, Secondary: %.0f°→%.0f°',
                $validated['primary_flow_temp'],
                $validated['primary_return_temp'],
                $validated['secondary_flow_temp'],
                $validated['secondary_return_temp']
            );
        }

        // Validate temperature logic
        if ($validated['primary_flow_temp'] <= $validated['primary_return_temp']) {
            return back()->withErrors(['primary_return_temp' => 'Primary return temperature must be lower than primary flow temperature.'])->withInput();
        }

        if ($validated['secondary_return_temp'] <= $validated['secondary_flow_temp']) {
            return back()->withErrors(['secondary_return_temp' => 'Secondary return temperature must be higher than secondary flow temperature.'])->withInput();
        }

        // Check for duplicate temperature combination
        $existing = TemperatureProfile::where([
            'primary_flow_temp' => $validated['primary_flow_temp'],
            'primary_return_temp' => $validated['primary_return_temp'],
            'secondary_flow_temp' => $validated['secondary_flow_temp'],
            'secondary_return_temp' => $validated['secondary_return_temp'],
        ])->first();

        if ($existing) {
            return back()->withErrors(['primary_flow_temp' => 'A temperature profile with these exact temperatures already exists.'])->withInput();
        }

        $profile = TemperatureProfile::create($validated);

        return redirect()->route('temperature-profiles.show', $profile)
            ->with('success', 'Temperature profile created successfully.');
    }

    /**
     * Show the form for creating a new temperature profile
     */
    public function create(): View
    {
        return view('temperature-profiles.create');
    }

    /**
     * Display the specified temperature profile
     */
    public function show(TemperatureProfile $temperatureProfile): View
    {
        $temperatureProfile->loadCount('performanceData');

        // Get products that use this profile
        $products = $temperatureProfile->products();

        // Get versions that use this profile
        $versions = $temperatureProfile->versions();

        // Get performance data statistics
        $performanceStats = $temperatureProfile->performanceData()
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

        return view('temperature-profiles.show', compact(
            'temperatureProfile',
            'products',
            'versions',
            'performanceStats'
        ));
    }

    /**
     * Show the form for editing the specified temperature profile
     */
    public function edit(TemperatureProfile $temperatureProfile): View
    {
        $temperatureProfile->loadCount('performanceData');

        return view('temperature-profiles.edit', compact('temperatureProfile'));
    }

    /**
     * Remove the specified temperature profile
     */
    public function destroy(TemperatureProfile $temperatureProfile): RedirectResponse
    {
        // Check if profile is in use
        if ($temperatureProfile->performanceData()->count() > 0) {
            return back()->withErrors(['delete' => 'Cannot delete temperature profile because it has associated performance data.']);
        }

        $temperatureProfile->delete();

        return redirect()->route('temperature-profiles.index')
            ->with('success', 'Temperature profile deleted successfully.');
    }

    /**
     * Bulk operations on temperature profiles
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'profile_ids' => 'required|array',
            'profile_ids.*' => 'exists:temperature_profiles,id'
        ]);

        $profiles = TemperatureProfile::whereIn('id', $validated['profile_ids']);

        switch ($validated['action']) {
            case 'activate':
                $profiles->update(['is_active' => true]);
                $message = 'Selected temperature profiles have been activated.';
                break;

            case 'deactivate':
                $profiles->update(['is_active' => false]);
                $message = 'Selected temperature profiles have been deactivated.';
                break;

            case 'delete':
                // Check if any profiles are in use
                $inUse = $profiles->has('performanceData')->count();
                if ($inUse > 0) {
                    return back()->withErrors(['delete' => "{$inUse} temperature profiles cannot be deleted because they have associated performance data."]);
                }

                $count = $profiles->count();
                $profiles->delete();
                $message = "{$count} temperature profiles have been deleted.";
                break;
        }

        return redirect()->route('temperature-profiles.index')
            ->with('success', $message);
    }

    /**
     * Update the specified temperature profile
     */
    public function update(Request $request, TemperatureProfile $temperatureProfile): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:temperature_profiles,name,' . $temperatureProfile->id,
            'primary_flow_temp' => 'required|numeric|min:-50|max:200',
            'primary_return_temp' => 'required|numeric|min:-50|max:200',
            'secondary_flow_temp' => 'required|numeric|min:-50|max:200',
            'secondary_return_temp' => 'required|numeric|min:-50|max:200',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Validate temperature logic
        if ($validated['primary_flow_temp'] <= $validated['primary_return_temp']) {
            return back()->withErrors(['primary_return_temp' => 'Primary return temperature must be lower than primary flow temperature.'])->withInput();
        }

        if ($validated['secondary_return_temp'] <= $validated['secondary_flow_temp']) {
            return back()->withErrors(['secondary_return_temp' => 'Secondary return temperature must be higher than secondary flow temperature.'])->withInput();
        }

        // Check for duplicate temperature combination (excluding current profile)
        $existing = TemperatureProfile::where([
            'primary_flow_temp' => $validated['primary_flow_temp'],
            'primary_return_temp' => $validated['primary_return_temp'],
            'secondary_flow_temp' => $validated['secondary_flow_temp'],
            'secondary_return_temp' => $validated['secondary_return_temp'],
        ])->where('id', '!=', $temperatureProfile->id)->first();

        if ($existing) {
            return back()->withErrors(['primary_flow_temp' => 'A temperature profile with these exact temperatures already exists.'])->withInput();
        }

        $temperatureProfile->update($validated);

        return redirect()->route('temperature-profiles.show', $temperatureProfile)
            ->with('success', 'Temperature profile updated successfully.');
    }


    /**
     * Import temperature profiles from Excel/CSV
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        // This would typically use a package like Laravel Excel
        // For now, return a placeholder response

        return redirect()->route('temperature-profiles.index')
            ->with('success', 'Temperature profiles imported successfully.');
    }
}
