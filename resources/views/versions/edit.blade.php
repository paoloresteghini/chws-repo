@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Edit Version: ' . $version->model_number,
        'subTitle' => 'Update version information and settings',
        'buttonText' => 'View Version',
        'buttonUrl' => route('versions.show', $version->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('versions.index') }}" class="hover:text-primary">Versions</a>
                <i class="ki-filled ki-right text-xs"></i>
                <a href="{{ route('versions.show', $version->id) }}" class="hover:text-primary">{{ $version->model_number }}</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Edit</span>
            </div>

            <form method="POST" action="{{ route('versions.update', $version->id) }}" class="space-y-6">
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
                                                required onchange="loadCategories()">
                                            <option value="">Select a product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        data-type="{{ $product->type }}"
                                                        data-has-vessels="{{ $product->has_vessel_options ? 'true' : 'false' }}"
                                                    {{ (old('product_id', $version->product_id) == $product->id) ? 'selected' : '' }}>
                                                    {{ $product->name }} ({{ $product->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('product_id')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Model Number -->
                                    <div>
                                        <label for="model_number" class="kt-label">Model Number *</label>
                                        <input type="text" name="model_number" id="model_number"
                                               class="kt-input @error('model_number') border-danger @enderror"
                                               value="{{ old('model_number', $version->model_number) }}"
                                               required>
                                        @error('model_number')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Unique identifier for this version</div>
                                    </div>

                                    <!-- Display Name -->
                                    <div>
                                        <label for="name" class="kt-label">Display Name</label>
                                        <input type="text" name="name" id="name"
                                               class="kt-input @error('name') border-danger @enderror"
                                               value="{{ old('name', $version->name) }}">
                                        @error('name')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Human-readable name (optional)</div>
                                    </div>

                                    <!-- Category -->
                                    <div>
                                        <label for="category_id" class="kt-label">Category</label>
                                        <select name="category_id" id="category_id"
                                                class="kt-select @error('category_id') border-danger @enderror">
                                            <option value="">Select a category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ (old('category_id', $version->category_id) == $category->id) ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status -->
                                    <div>
                                        <label for="status" class="kt-label">Status</label>
                                        <select name="status" id="status" class="kt-select @error('status') border-danger @enderror">
                                            <option value="1" {{ old('status', $version->status) == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $version->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2">
                                        <label for="description" class="kt-label">Description</label>
                                        <textarea name="description" id="description" rows="3"
                                                  class="kt-input @error('description') border-danger @enderror"
                                                  placeholder="Optional description of this version">{{ old('description', $version->description) }}</textarea>
                                        @error('description')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Features -->
                        <div class="kt-card" id="features-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Product Features</h3>
                                <div class="text-sm text-gray-500">Configure product-specific options</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <!-- Vessel Options -->
                                <div id="vessel-options">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="has_vessel_options" id="has_vessel_options"
                                               class="kt-checkbox" value="1"
                                            {{ old('has_vessel_options', $version->has_vessel_options) ? 'checked' : '' }}>
                                        <label for="has_vessel_options" class="kt-label mb-0">
                                            This version supports vessel configurations
                                        </label>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Enable this if the version can be configured with different vessel sizes
                                    </div>

                                    @if($version->vesselConfigurations->count() > 0)
                                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                                            <div class="text-sm text-blue-800">
                                                <strong>Note:</strong> This version currently has {{ $version->vesselConfigurations->count() }} vessel configurations.
                                                Disabling vessel options will not delete existing configurations.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Specifications -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Specifications</h3>
                                <div class="text-sm text-gray-500">Additional technical specifications</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div id="specifications-container">
                                    <div class="space-y-3" id="spec-fields">
                                        @if($version->specifications)
                                            @foreach($version->specifications as $key => $value)
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
                                    <textarea name="specifications_json" id="specifications_json" rows="4"
                                              class="kt-input font-mono text-sm"
                                              placeholder='{"key": "value", "another_key": "another_value"}'>{{ old('specifications_json', $version->specifications ? json_encode($version->specifications, JSON_PRETTY_PRINT) : '') }}</textarea>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Optional: Enter specifications as JSON. This will override individual fields above.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Summary -->
                        @if($version->performanceData->count() > 0 || $version->vesselConfigurations->count() > 0)
                            <div class="kt-card">
                                <div class="kt-card-header">
                                    <h3 class="kt-card-title">Associated Data</h3>
                                    <div class="text-sm text-gray-500">Related data that will be affected by changes</div>
                                </div>
                                <div class="kt-card-body px-6 py-6">
                                    <div class="grid md:grid-cols-2 gap-6">
                                        @if($version->vesselConfigurations->count() > 0)
                                            <div class="p-4 border border-gray-200 rounded-lg">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i class="ki-filled ki-bucket text-info"></i>
                                                    <h4 class="font-medium">Vessel Configurations</h4>
                                                </div>
                                                <div class="text-sm text-gray-600 mb-3">
                                                    {{ $version->vesselConfigurations->count() }} configurations available
                                                </div>
                                                <div class="space-y-1">
                                                    @foreach($version->vesselConfigurations->take(3) as $vessel)
                                                        <div class="text-xs text-gray-500">• {{ $vessel->name }}</div>
                                                    @endforeach
                                                    @if($version->vesselConfigurations->count() > 3)
                                                        <div class="text-xs text-gray-400">... and {{ $version->vesselConfigurations->count() - 3 }} more</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if($version->performanceData->count() > 0)
                                            <div class="p-4 border border-gray-200 rounded-lg">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <i class="ki-filled ki-chart-simple text-success"></i>
                                                    <h4 class="font-medium">Performance Data</h4>
                                                </div>
                                                <div class="text-sm text-gray-600 mb-3">
                                                    {{ $version->performanceData->count() }} performance records
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    <div>• {{ $version->availableTemperatureProfiles()->count() }} temperature profiles</div>
                                                    <div>• Heat range: {{ number_format($version->performanceData->min('heat_input_kw'), 1) }} - {{ number_format($version->performanceData->max('heat_input_kw'), 1) }} kW</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Current Version Info -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Current Version</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Product:</span>
                                        <span class="text-sm font-medium">{{ $version->product->name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Model:</span>
                                        <span class="text-sm font-mono font-medium">{{ $version->model_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Status:</span>
                                        <span class="kt-badge kt-badge-xs {{ $version->status ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                                            {{ $version->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Created:</span>
                                        <span class="text-sm">{{ $version->created_at->format('M j, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Updated:</span>
                                        <span class="text-sm">{{ $version->updated_at->format('M j, Y') }}</span>
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
                                        Update Version
                                    </button>
                                    <a href="{{ route('versions.show', $version->id) }}" class="kt-btn kt-btn-secondary w-full">
                                        <i class="ki-filled ki-eye"></i>
                                        View Version
                                    </a>
                                    <a href="{{ route('versions.index') }}" class="kt-btn kt-btn-light w-full">
                                        <i class="ki-filled ki-arrow-left"></i>
                                        Back to List
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        <div class="kt-card border-danger">
                            <div class="kt-card-header bg-danger-light">
                                <h3 class="kt-card-title text-danger">Danger Zone</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div class="text-sm text-gray-600">
                                        Delete this version permanently. This action cannot be undone.
                                    </div>
                                    @if($version->performanceData->count() > 0 || $version->vesselConfigurations->count() > 0)
                                        <div class="p-3 bg-red-50 border border-red-200 rounded text-sm text-red-800">
                                            <strong>Warning:</strong> This version has associated data that will also be deleted.
                                        </div>
                                    @endif
                                    <form method="POST" action="{{ route('versions.destroy', $version->id) }}"
                                          onsubmit="return confirm('Are you sure you want to delete this version? This action cannot be undone and will delete all associated data.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-danger w-full">
                                            <i class="ki-filled ki-trash"></i>
                                            Delete Version
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        let specificationCount = {{ $version->specifications ? count($version->specifications) : 0 }};

        // Load categories when product changes
        function loadCategories() {
            const productSelect = document.getElementById('product_id');
            const categorySelect = document.getElementById('category_id');
            const vesselOptions = document.getElementById('vessel-options');

            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const currentCategoryId = {{ old('category_id', $version->category_id) ?: 'null' }};

            if (selectedOption.value) {
                // Show/hide vessel options based on product
                const hasVessels = selectedOption.dataset.hasVessels === 'true';
                if (!hasVessels) {
                    document.getElementById('has_vessel_options').checked = false;
                }

                // Load categories via AJAX
                fetch(`/api/categories-for-product?product_id=${selectedOption.value}`)
                    .then(response => response.json())
                    .then(categories => {
                        categorySelect.innerHTML = '<option value="">Select a category</option>';
                        categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            if (category.id == currentCategoryId) {
                                option.selected = true;
                            }
                            categorySelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading categories:', error);
                        categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                    });
            } else {
                categorySelect.innerHTML = '<option value="">Select a category</option>';
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
            // Set initial state based on current product
            loadCategories();
        });
    </script>
@endsection
