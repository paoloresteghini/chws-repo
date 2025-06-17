<?php

// File: app/Http/Controllers/VersionCategoryController.php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\VersionCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VersionCategoryController extends Controller
{
    /**
     * Display a listing of version categories
     */
    public function index(Request $request): View
    {
        $query = VersionCategory::with(['product', 'versions'])
            ->withCount(['versions', 'activeVersions']);

        // Apply filters
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('prefix', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Order by product name, then sort order, then name
        $categories = $query->join('products', 'version_categories.product_id', '=', 'products.id')
            ->orderBy('products.name')
            ->orderBy('version_categories.sort_order')
            ->orderBy('version_categories.name')
            ->select('version_categories.*')
            ->get();

        // Get filter options
        $products = Product::orderBy('name')->get();

        // Get statistics
        $stats = [
            'total_categories' => VersionCategory::count(),
            'categories_with_versions' => VersionCategory::has('versions')->count(),
            'total_versions_categorized' => VersionCategory::withCount('versions')->get()->sum('versions_count'),
        ];

        return view('version-categories.index', compact('categories', 'products', 'stats'));
    }

    /**
     * Store a newly created version category
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0|max:999',
            'spec_keys' => 'nullable|array',
            'spec_values' => 'nullable|array',
            'category_specs_json' => 'nullable|string',
        ]);

        // Set default sort order if not provided
        if (!isset($validated['sort_order'])) {
            $maxSortOrder = VersionCategory::where('product_id', $validated['product_id'])->max('sort_order') ?? 0;
            $validated['sort_order'] = $maxSortOrder + 10;
        }

        // Validate prefix uniqueness within product
        if ($validated['prefix']) {
            $existingPrefix = VersionCategory::where('product_id', $validated['product_id'])
                ->where('prefix', $validated['prefix'])
                ->exists();

            if ($existingPrefix) {
                return back()->withErrors(['prefix' => 'This prefix is already used for this product.'])->withInput();
            }
        }

        // Process specifications
        $specifications = null;
        
        // If JSON specifications are provided, use those
        if (!empty($validated['category_specs_json'])) {
            $decoded = json_decode($validated['category_specs_json'], true);
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
        unset($validated['spec_keys'], $validated['spec_values'], $validated['category_specs_json']);
        $validated['category_specs'] = $specifications;

        $category = VersionCategory::create($validated);

        return redirect()->route('version-categories.show', $category)
            ->with('success', 'Version category created successfully.');
    }

    /**
     * Show the form for creating a new version category
     */
    public function create(): View
    {
        $products = Product::orderBy('name')->get();

        return view('version-categories.create', compact('products'));
    }

    /**
     * Display the specified version category
     */
    public function show(VersionCategory $versionCategory): View
    {
        $versionCategory->load([
            'product',
            'versions.performanceData',
            'versions.vesselConfigurations'
        ]);

        // Get category statistics
        $stats = [
            'total_versions' => $versionCategory->versions->count(),
            'active_versions' => $versionCategory->activeVersions->count(),
            'versions_with_performance' => $versionCategory->versions->filter(function($version) {
                return $version->performanceData->count() > 0;
            })->count(),
            'total_performance_records' => $versionCategory->versions->sum(function($version) {
                return $version->performanceData->count();
            }),
        ];

        if ($versionCategory->product->has_vessel_options) {
            $stats['total_vessel_configurations'] = $versionCategory->versions->sum(function($version) {
                return $version->vesselConfigurations->count();
            });
        }

        // Get versions grouped by status
        $versionsByStatus = $versionCategory->versions->groupBy('status');

        return view('version-categories.show', compact('versionCategory', 'stats', 'versionsByStatus'));
    }

    /**
     * Show the form for editing the specified version category
     */
    public function edit(VersionCategory $versionCategory): View
    {
        $products = Product::orderBy('name')->get();
        $versionCategory->loadCount(['versions', 'activeVersions']);

        return view('version-categories.edit', compact('versionCategory', 'products'));
    }

    /**
     * Remove the specified version category
     */
    public function destroy(VersionCategory $versionCategory): RedirectResponse
    {
        // Check if category has versions
        if ($versionCategory->versions()->count() > 0) {
            return back()->withErrors(['delete' => 'Cannot delete category because it has associated versions.']);
        }

        $versionCategory->delete();

        return redirect()->route('version-categories.index')
            ->with('success', 'Version category deleted successfully.');
    }

    /**
     * Bulk operations on version categories
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,reorder',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:version_categories,id'
        ]);

        $categories = VersionCategory::whereIn('id', $validated['category_ids']);

        switch ($validated['action']) {
            case 'delete':
                // Check if any categories have versions
                $categoriesWithVersions = $categories->has('versions')->count();
                if ($categoriesWithVersions > 0) {
                    return back()->withErrors(['delete' => "{$categoriesWithVersions} categories cannot be deleted because they have associated versions."]);
                }

                $count = $categories->count();
                $categories->delete();
                $message = "{$count} version categories have been deleted.";
                break;

            case 'reorder':
                // This would require additional front-end work for drag & drop
                $message = 'Reordering functionality not yet implemented.';
                break;
        }

        return redirect()->route('version-categories.index')
            ->with('success', $message);
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
            ->get(['id', 'name', 'prefix', 'description']);

        return response()->json($categories);
    }

    /**
     * Update sort orders for categories
     */
    public function updateSortOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'exists:version_categories,id',
            'categories.*.sort_order' => 'integer|min:0|max:999'
        ]);

        foreach ($validated['categories'] as $categoryData) {
            VersionCategory::where('id', $categoryData['id'])
                ->update(['sort_order' => $categoryData['sort_order']]);
        }

        return redirect()->route('version-categories.index')
            ->with('success', 'Category order updated successfully.');
    }

    /**
     * Update the specified version category
     */
    public function update(Request $request, VersionCategory $versionCategory): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0|max:999',
            'spec_keys' => 'nullable|array',
            'spec_values' => 'nullable|array',
            'category_specs_json' => 'nullable|string',
        ]);

        // Validate prefix uniqueness within product (excluding current category)
        if ($validated['prefix']) {
            $existingPrefix = VersionCategory::where('product_id', $validated['product_id'])
                ->where('prefix', $validated['prefix'])
                ->where('id', '!=', $versionCategory->id)
                ->exists();

            if ($existingPrefix) {
                return back()->withErrors(['prefix' => 'This prefix is already used for this product.'])->withInput();
            }
        }

        // Process specifications
        $specifications = null;
        
        // If JSON specifications are provided, use those
        if (!empty($validated['category_specs_json'])) {
            $decoded = json_decode($validated['category_specs_json'], true);
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
        unset($validated['spec_keys'], $validated['spec_values'], $validated['category_specs_json']);
        $validated['category_specs'] = $specifications;

        $versionCategory->update($validated);

        return redirect()->route('version-categories.show', $versionCategory)
            ->with('success', 'Version category updated successfully.');
    }

    /**
     * Assign versions to a category
     */
    public function assignVersions(Request $request, VersionCategory $versionCategory): RedirectResponse
    {
        $validated = $request->validate([
            'version_ids' => 'required|array',
            'version_ids.*' => 'exists:versions,id'
        ]);

        // Verify all versions belong to the same product as the category
        $invalidVersions = \App\Models\Version::whereIn('id', $validated['version_ids'])
            ->where('product_id', '!=', $versionCategory->product_id)
            ->count();

        if ($invalidVersions > 0) {
            return back()->withErrors(['version_ids' => 'All versions must belong to the same product as the category.']);
        }

        // Update the versions
        \App\Models\Version::whereIn('id', $validated['version_ids'])
            ->update(['category_id' => $versionCategory->id]);

        $count = count($validated['version_ids']);
        return redirect()->route('version-categories.show', $versionCategory)
            ->with('success', "{$count} versions have been assigned to this category.");
    }
}
