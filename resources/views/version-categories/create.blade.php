@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Create Version Category',
        'subTitle' => 'Add a new category to organize product versions',
        'buttonText' => 'Back to Categories',
        'buttonUrl' => route('version-categories.index'),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('version-categories.index') }}" class="hover:text-primary">Version Categories</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Create New Category</span>
            </div>

            <form method="POST" action="{{ route('version-categories.store') }}" class="space-y-6">
                @csrf

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
                                                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
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
                                               value="{{ old('name') }}"
                                               placeholder="e.g., 3000 Series" required>
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
                                               value="{{ old('prefix') }}"
                                               placeholder="e.g., 30">
                                        @error('prefix')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Optional prefix for model numbers (e.g., "30" for 3000 series)</div>
                                    </div>

                                    <!-- Sort Order -->
                                    <div>
                                        <label for="sort_order" class="kt-label">Sort Order</label>
                                        <input type="number" name="sort_order" id="sort_order"
                                               class="kt-input @error('sort_order') border-danger @enderror"
                                               value="{{ old('sort_order', 10) }}"
                                               min="0" max="999">
                                        @error('sort_order')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Order for displaying categories (lower numbers first)</div>
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2">
                                        <label for="description" class="kt-label">Description</label>
                                        <textarea name="description" id="description" rows="3"
                                                  class="kt-textarea @error('description') border-danger @enderror"
                                                  placeholder="Optional description of this category">{{ old('description') }}</textarea>
                                        @error('description')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category Specifications (Optional) -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Category Specifications</h3>
                                <div class="text-sm text-gray-500">Additional specifications for this category (JSON format)</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div id="specifications-container">
                                    <div class="space-y-3" id="spec-fields">
                                        <!-- Specification fields will be added here -->
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
                                              class="kt-textarea font-mono text-sm"
                                              placeholder='{"capacity_range": "100-500kW", "applications": ["heating", "cooling"]}'>{{ old('category_specs_json') }}</textarea>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Optional: Enter category specifications as JSON. This will override individual fields above.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Product Info Preview -->
                        <div class="kt-card" id="product-preview" style="display: none;">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Product Information</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3" id="product-info">
                                    <!-- Product info will be populated via JavaScript -->
                                </div>
                            </div>
                        </div>

                        <!-- Category Examples -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Category Examples</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3 text-sm">
                                    <div>
                                        <div class="font-medium text-gray-900">Heat Exchangers:</div>
                                        <div class="text-gray-600">• 3000 Series (prefix: "30")</div>
                                        <div class="text-gray-600">• 4000 Series (prefix: "40")</div>
                                        <div class="text-gray-600">• 5000 Series (prefix: "50")</div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">DHW Systems:</div>
                                        <div class="text-gray-600">• Standard Range</div>
                                        <div class="text-gray-600">• High Capacity</div>
                                        <div class="text-gray-600">• Compact Series</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tips -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Tips</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3 text-sm text-gray-600">
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Use prefixes to automatically categorize versions by model number</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Sort order controls the display order in dropdowns and lists</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Categories help organise versions for easier management</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>You can assign versions to categories after creation</div>
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
                                        Create Category
                                    </button>
                                    <a href="{{ route('version-categories.index') }}" class="kt-btn kt-btn-secondary w-full">
                                        <i class="ki-filled ki-cross"></i>
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        let specificationCount = 0;

        // Update product info when product changes
        function updateProductInfo() {
            const productSelect = document.getElementById('product_id');
            const productPreview = document.getElementById('product-preview');
            const productInfo = document.getElementById('product-info');

            const selectedOption = productSelect.options[productSelect.selectedIndex];

            if (selectedOption.value) {
                // Show product preview
                productPreview.style.display = 'block';

                // Update product info
                productInfo.innerHTML = `
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Product:</span>
                        <span class="text-sm font-medium">${selectedOption.text.split(' (')[0]}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Type:</span>
                        <span class="text-sm">${selectedOption.dataset.type}</span>
                    </div>
                `;

                // Auto-suggest category name based on product
                const nameInput = document.getElementById('name');
                if (!nameInput.value) {
                    const productName = selectedOption.text.split(' (')[0];
                    if (productName === 'ProPak') {
                        nameInput.placeholder = 'e.g., 3000 Series, 4000 Series';
                    } else if (productName === 'Aquafast') {
                        nameInput.placeholder = 'e.g., Standard Range, High Capacity';
                    } else if (productName === 'ProRapid') {
                        nameInput.placeholder = 'e.g., Rapid Series, Compact Range';
                    }
                }
            } else {
                productPreview.style.display = 'none';
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

        // Auto-generate sort order suggestions
        document.getElementById('product_id').addEventListener('change', function() {
            const sortOrderInput = document.getElementById('sort_order');
            if (!sortOrderInput.value || sortOrderInput.value == 10) {
                // Suggest sort orders based on product type
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.dataset.type === 'heat_exchanger') {
                    sortOrderInput.value = 10;
                } else if (selectedOption.dataset.type === 'dhw_system') {
                    sortOrderInput.value = 20;
                } else {
                    sortOrderInput.value = 30;
                }
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.getElementById('product_id');
            if (productSelect.value) {
                updateProductInfo();
            }
        });
    </script>
@endsection
