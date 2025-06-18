<?php

// File: app/Http/Controllers/VersionController.php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Version;
use App\Models\VersionCategory;
use App\Models\Attachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class VersionController extends Controller
{
    /**
     * Display a listing of versions with filtering options
     */
    public function index(Request $request): View
    {
        $query = Version::with(['product', 'category', 'vesselConfigurations', 'performanceData']);

        // Apply filters
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('versions.model_number', 'like', "%{$search}%")
                    ->orWhere('versions.name', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Order by product name, then by model number
        $query->join('products', 'versions.product_id', '=', 'products.id')
            ->orderBy('products.name')
            ->orderBy('versions.model_number')
            ->select('versions.*');

        $versions = $query->paginate(15)->withQueryString();

        // Get filter options
        $products = Product::orderBy('name')->get();
        $categories = VersionCategory::with('product')->orderBy('name')->get();

        return view('versions.index', compact('versions', 'products', 'categories'));
    }

    /**
     * Store a newly created version in storage
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'model_number' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:version_categories,id',
            'has_vessel_options' => 'boolean',
            'status' => 'boolean',
            'spec_keys' => 'nullable|array',
            'spec_values' => 'nullable|array',
            'specifications_json' => 'nullable|string',
        ]);

        // Ensure model number is unique per product
        $request->validate([
            'model_number' => "unique:versions,model_number,NULL,id,product_id,{$validated['product_id']}"
        ]);

        // Process specifications
        $specifications = null;
        
        // If JSON specifications are provided, use those
        if (!empty($validated['specifications_json'])) {
            $decoded = json_decode($validated['specifications_json'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $specifications = $decoded;
            }
        } else {
            // Process key-value pairs
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
        }

        // Remove specification-related fields from validated data and add processed specifications
        unset($validated['spec_keys'], $validated['spec_values'], $validated['specifications_json']);
        $validated['specifications'] = $specifications;

        $version = Version::create($validated);

        return redirect()->route('versions.show', $version)
            ->with('success', 'Version created successfully.');
    }

    /**
     * Show the form for creating a new version
     */
    public function create(): View
    {
        $products = Product::orderBy('name')->get();
        $categories = VersionCategory::with('product')->orderBy('name')->get();

        return view('versions.create', compact('products', 'categories'));
    }

    /**
     * Display the specified version
     */
    public function show(Version $version): View
    {
        $version->load([
            'product',
            'category',
            'vesselConfigurations',
            'performanceData.temperatureProfile',
            'performanceData.vesselConfiguration',
            'attachments'
        ]);

        // Get performance data grouped by temperature profiles
        $performanceByProfile = $version->performanceData
            ->groupBy('temperature_profile_id')
            ->map(function ($data) {
                return [
                    'profile' => $data->first()->temperatureProfile,
                    'data' => $data
                ];
            });

        // Get available temperature profiles for this version
        $availableProfiles = $version->availableTemperatureProfiles();

        return view('versions.show', compact('version', 'performanceByProfile', 'availableProfiles'));
    }

    /**
     * Show the form for editing the specified version
     */
    public function edit(Version $version): View
    {
        $version->load('attachments');
        $products = Product::orderBy('name')->get();
        $categories = VersionCategory::where('product_id', $version->product_id)
            ->orderBy('name')
            ->get();

        return view('versions.edit', compact('version', 'products', 'categories'));
    }

    /**
     * Remove the specified version from storage
     */
    public function destroy(Version $version): RedirectResponse
    {
        $version->delete();

        return redirect()->route('versions.index')
            ->with('success', 'Version deleted successfully.');
    }

    /**
     * Display performance data for a specific version
     */
    public function performance(Version $version): View
    {
        $version->load([
            'product',
            'performanceData.temperatureProfile',
            'performanceData.vesselConfiguration'
        ]);

        // Group performance data by temperature profile and vessel configuration
        $performanceMatrix = $version->performanceData
            ->groupBy('temperature_profile_id')
            ->map(function ($profileData) {
                return [
                    'profile' => $profileData->first()->temperatureProfile,
                    'vessel_data' => $profileData->groupBy('vessel_configuration_id')->map(function ($vesselData) {
                        return [
                            'vessel' => $vesselData->first()->vesselConfiguration,
                            'performance' => $vesselData->first()
                        ];
                    })
                ];
            });

        return view('versions.performance', compact('version', 'performanceMatrix'));
    }

    /**
     * Get categories for a specific product (AJAX endpoint)
     */
    public function getCategoriesForProduct(Request $request)
    {
        $productId = $request->product_id;

        $categories = VersionCategory::where('product_id', $productId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($categories);
    }

    /**
     * Get versions for a specific product (AJAX endpoint)
     */
    public function getVersionsByProduct($productId, Request $request)
    {
        $query = Version::where('product_id', $productId);

        // Check if we need to filter by has_vessel_options
        if ($request->has('has_vessel_options')) {
            $query->where('has_vessel_options', true);
        }

        $versions = $query->orderBy('model_number')
            ->get(['id', 'model_number', 'name']);

        return response()->json($versions);
    }

    /**
     * Bulk operations on versions
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'version_ids' => 'required|array',
            'version_ids.*' => 'exists:versions,id'
        ]);

        $versions = Version::whereIn('id', $validated['version_ids']);

        switch ($validated['action']) {
            case 'activate':
                $versions->update(['status' => true]);
                $message = 'Selected versions have been activated.';
                break;

            case 'deactivate':
                $versions->update(['status' => false]);
                $message = 'Selected versions have been deactivated.';
                break;

            case 'delete':
                $count = $versions->count();
                $versions->delete();
                $message = "{$count} versions have been deleted.";
                break;
        }

        return redirect()->route('versions.index')
            ->with('success', $message);
    }

    /**
     * Update the specified version in storage
     */
    public function update(Request $request, Version $version): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'model_number' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:version_categories,id',
            'has_vessel_options' => 'boolean',
            'status' => 'boolean',
            'spec_keys' => 'nullable|array',
            'spec_values' => 'nullable|array',
            'specifications_json' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,txt,png,jpg,jpeg,gif|max:10240',
            'attachment_names.*' => 'nullable|string|max:255',
            'delete_attachments.*' => 'nullable|exists:attachments,id',
        ]);

        // Ensure model number is unique per product (excluding current version)
        $request->validate([
            'model_number' => "unique:versions,model_number,{$version->id},id,product_id,{$validated['product_id']}"
        ]);

        // Process specifications
        $specifications = null;
        
        // If JSON specifications are provided, use those
        if (!empty($validated['specifications_json'])) {
            $decoded = json_decode($validated['specifications_json'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $specifications = $decoded;
            }
        } else {
            // Process key-value pairs
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
        }

        // Remove specification-related fields from validated data and add processed specifications
        unset($validated['spec_keys'], $validated['spec_values'], $validated['specifications_json']);
        $validated['specifications'] = $specifications;

        DB::beginTransaction();
        
        try {
            $version->update($validated);
            
            // Handle attachment deletions
            if ($request->has('delete_attachments')) {
                $attachmentsToDelete = $version->attachments()->whereIn('id', $request->delete_attachments)->get();
                foreach ($attachmentsToDelete as $attachment) {
                    Storage::disk('s3')->delete($attachment->file_path);
                    $attachment->delete();
                }
            }
            
            // Handle new attachments
            if ($request->hasFile('attachments')) {
                $attachments = $request->file('attachments');
                $attachmentNames = $request->input('attachment_names', []);
                
                foreach ($attachments as $index => $file) {
                    if ($file) {
                        $path = $file->store('version-attachments/' . $version->id, 's3');
                        $name = isset($attachmentNames[$index]) && !empty($attachmentNames[$index]) 
                            ? $attachmentNames[$index] 
                            : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        
                        $version->attachments()->create([
                            'name' => $name,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'file_size' => $file->getSize(),
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('versions.show', $version)
                ->with('success', 'Version updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update version: ' . $e->getMessage());
        }
    }

    /**
     * Export versions data
     */
    public function export(Request $request)
    {
        // This would typically use a package like Laravel Excel
        // For now, just return a simple CSV response

        $query = Version::with(['product', 'category']);

        // Apply same filters as index
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $versions = $query->get();

        $csvData = "Product,Model Number,Name,Category,Status,Created At\n";

        foreach ($versions as $version) {
            $csvData .= sprintf(
                "%s,%s,%s,%s,%s,%s\n",
                $version->product->name,
                $version->model_number,
                $version->name ?? '',
                $version->category->name ?? '',
                $version->status ? 'Active' : 'Inactive',
                $version->created_at->format('Y-m-d H:i:s')
            );
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="versions-export-' . now()->format('Y-m-d') . '.csv"');
    }
}
