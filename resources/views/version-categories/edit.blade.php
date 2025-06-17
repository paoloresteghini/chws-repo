@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Edit Category: ' . $versionCategory->name,
        'subTitle' => 'Update category information and settings',
        'buttonText' => 'View Category',
        'buttonUrl' => route('version-categories.show', $versionCategory->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('version-categories.index') }}" class="hover:text-primary">Version Categories</a>
                <i class="ki-filled ki-right text-xs"></i>
                <a href="{{ route('version-categories.show', $versionCategory->id) }}" class="hover:text-primary">{{ $versionCategory->name }}</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Edit</span>
            </div>

            <form method="POST" action="{{ route('version-categories.update', $versionCategory->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Basic Information</h3>
                                <div class="text-sm text-gray-500">Required fields are marked with *</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- Product Selection -->
                                    <div class="md:col-span-2">
                                        <label for="product_id" class="kt-label">Product *</label>
                                        <select name="product_id" id="product_id"
                                                class="kt-select @error('product_id') border-danger @enderror"
                                                required onchange="updateProductInfo()">
                                            <option value="">Select a product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        data-type="{{ $product->type }}"
                                                    {{ (old('product_id', $versionCategory->product_id) == $product->id) ? 'selected' : '' }}>
                                                    {{ $product->name }} ({{ $product->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('product_id')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Category Name -->
                                    <div>
                                        <label for="name" class="kt-label">Category Name *</label>
                                        <input type="text" name="name" id="name"
                                               class="kt-input @error('name') border-danger @enderror"
                                               value="{{ old('name', $versionCategory->name) }}"
                                               required>
                                        @error('name')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Descriptive name for the category</div>
                                    </div>

                                    <!-- Prefix -->
                                    <div>
                                        <label for="prefix" class="kt-label">Prefix</label>
                                        <input type="text" name="prefix" id="prefix"
                                               class="kt-input @error('prefix') border-danger @enderror"
                                               value="{{ old('prefix', $versionCategory->prefix) }}">
                                        @error('prefix')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Optional prefix for model numbers</div>
                                    </div>

                                    <!-- Sort Order -->
                                    <div>
                                        <label for="sort_order" class="kt-label">Sort Order</label>
                                        <input type="number" name="sort_order" id="sort_order"
                                               class="kt-input @error('sort_order') border-danger @enderror"
                                               value="{{ old('sort_order', $versionCategory->sort_order) }}"
                                               min="0" max="999">
                                        @error('sort_order')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Order for displaying categories</div>
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2">
                                        <label for="description" class="kt-label">Description</label>
                                        <textarea name="description" id="description" rows="3"
                                                  class="kt-input @error('description') border-danger @enderror"
                                                  placeholder="Optional description of this category">{{ old('description', $versionCategory->description) }}</textarea>
                                        @error('description')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category Specifications -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Category Specifications</h3>
                                <div class="text-sm text-gray-500">Additional specifications for this category</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div id="specifications-container">
                                    <div class="space-y-3" id="spec-fields">
                                        @if($versionCategory->category_specs)
                                            @foreach($versionCategory->category_specs as $key => $value)
                                                <div class="flex gap-3">
                                                    <input type="text" name="spec_keys[]" value="{{ $key }}"
                                                           placeholder="Key" class="kt-input flex-1">
                                                    <input type="text" name="spec_values[]" value="{{ $value }}"
                                                           placeholder="Value" class="kt-input flex-1">
                                                    <button type="button" onclick="this.parentElement.remove()"
                                                            class="kt-btn kt-btn-sm kt-btn-danger">
                                                        <i class="ki-filled ki-trash"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" onclick="addSpecification()" class="kt-btn kt-btn-sm kt-btn-secondary mt-3">
                                        <i class="ki-filled ki-plus"></i>
                                        Add Specification
                                    </button>
                                </div>

                                <!-- Raw JSON Input (Advanced) -->
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <label class="kt-label">Raw JSON (Advanced)</label>
                                    <textarea name="category_specs_json" id="category_specs_json" rows="4"
                                              class="kt-input font-mono text-sm"
                                              placeholder='{"capacity_range": "100-500kW", "applications": ["heating", "cooling"]}'>{{ old('category_specs_json', $versionCategory->category_specs ? json_encode($versionCategory->category_specs, JSON_PRETTY_PRINT) : '') }}</textarea>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Optional: Enter category specifications as JSON. This will override individual fields above.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Associated Versions Summary -->
                        @if($versionCategory->versions_count > 0)
                            <div class="kt-card">
                                <div class="kt-card-header">
                                    <h3 class="kt-card-title">Associated Data</h3>
                                    <div class="text-sm text-gray-500">Data that will be affected by changes</div>
                                </div>
                                <div class="kt-card-body px-6 py-6">
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="flex items-center gap-2 mb-2">
                                                <i class="ki-filled ki-abstract-26 text-info"></i>
                                                <h4 class="font-medium">Versions in Category</h4>
                                            </div>
                                            <div class="text-sm text-gray-600 mb-3">
                                                {{ $versionCategory->versions_count }} versions assigned to this category
                                            </div>
                                            <div class="flex justify-between text-xs">
                                                <span>Active versions:</span>
                                                <span class="font-medium">{{ $versionCategory->active_versions_count }}</span>
                                            </div>
                                        </div>

                                        <div class="p-4 border border-gray-200 rounded-lg">
                                            <div class="flex items-center gap-2 mb-2">
                                                <i class="ki-filled ki-chart-simple text-success"></i>
                                                <h4 class="font-medium">Performance Impact</h4>
                                            </div>
                                            <div class="text-sm text-gray-600 mb-3">
                                                Changes to prefix may affect version organization
                                            </div>
                                            <div class="text-xs text-orange-600">
                                                <strong>Note:</strong> Changing the product will unassign all versions
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Current Category Info -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Current Category</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Product:</span>
                                        <span class="text-sm font-medium">{{ $versionCategory->product->name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Name:</span>
                                        <span class="text-sm font-medium">{{ $versionCategory->name }}</span>
                                    </div>
                                    @if($versionCategory->prefix)
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Prefix:</span>
                                            <span class="kt-badge kt-badge-xs kt-badge-outline">{{ $versionCategory->prefix }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Sort Order:</span>
                                        <span class="text-sm">{{ $versionCategory->sort_order }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Versions:</span>
                                        <span class="text-sm">{{ $versionCategory->versions_count }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Created:</span>
                                        <span class="text-sm">{{ $versionCategory->created_at->format('M j, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="kt-card">
                            <div class="kt-card-body px-6 py-6">
                                <div class="flex flex-col gap-3">
                                    <button type="submit" class="kt-btn kt-btn-primary w-full">
                                        <i class="ki-filled ki-check"></i>
                                        Update Category
                                    </button>
                                    <a href="{{ route('version-categories.show', $versionCategory->id) }}" class="kt-btn kt-btn-secondary w-full">
                                        <i class="ki-filled ki-eye"></i>
                                        View Category
                                    </a>
                                    <a href="{{ route('version-categories.index') }}" class="kt-btn kt-btn-light w-full">
                                        <i class="ki-filled ki-arrow-left"></i>
                                        Back to List
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        @if($versionCategory->versions_count == 0)
                            <div class="kt-card border-danger">
                                <div class="kt-card-header bg-danger-light">
                                    <h3 class="kt-card-title text-danger">Danger Zone</h3>
                                </div>
                                <div class="kt-card-body px-6 py-6">
                                    <div class="space-y-3">
                                        <div class="text-sm text-gray-600">
                                            Delete this category permanently. This action cannot be undone.
                                        </div>
                                        <form method="POST" action="{{ route('version-categories.destroy', $versionCategory->id) }}"
                                              onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="kt-btn kt-btn-danger w-full">
                                                <i class="ki-filled ki-trash"></i>
                                                Delete Category
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="kt-card border-warning">
                                <div class="kt-card-header bg-warning-light">
                                    <h3 class="kt-card-title text-warning">Cannot Delete</h3>
                                </div>
                                <div class="kt-card-body px-6 py-6">
                                    <div class="text-sm text-gray-600">
                                        This category cannot be deleted because it has {{ $versionCategory->versions_count }} associated version(s).
                                        Remove all versions from this category first.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        let specificationCount = {{ $versionCategory->category_specs ? count($versionCategory->category_specs) : 0 }};

        // Update product info when product changes
        function updateProductInfo() {
            const productSelect = document.getElementById('product_id');
            const selectedOption = productSelect.options[productSelect.selectedIndex];

            if (selectedOption.value) {
                // Warn if changing product
                const originalProductId = {{ $versionCategory->product_id }};
                if (selectedOption.value != originalProductId) {
                    if (!confirm('Changing the product will unassign all versions from this category. Are you sure?')) {
                        productSelect.value = originalProductId;
                        return;
                    }
                }
            }
        }

        // Add specification field
        function addSpecification() {
            const container = document.getElementById('spec-fields');
            const div = document.createElement('div');
            div.className = 'flex gap-3';
            div.innerHTML = `
                <input type="text" name="spec_keys[]" placeholder="Key" class="kt-input flex-1">
                <input type="text" name="spec_values[]" placeholder="Value" class="kt-input flex-1">
                <button type="button" onclick="this.parentElement.remove()" class="kt-btn kt-btn-sm kt-btn-danger">
                    <i class="ki-filled ki-trash"></i>
                </button>
            `;
            container.appendChild(div);
            specificationCount++;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set up change detection for product select
            const productSelect = document.getElementById('product_id');
            productSelect.addEventListener('change', updateProductInfo);
        });
    </script>
@endsection
