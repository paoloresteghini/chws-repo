@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Create Version',
        'subTitle' => 'Add a new product version to the system',
        'buttonText' => 'Back to Versions',
        'buttonUrl' => route('versions.index'),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('versions.index') }}" class="hover:text-primary">Versions</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Create New Version</span>
            </div>

            <form method="POST" action="{{ route('versions.store') }}" class="space-y-6">
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
                                                required onchange="loadCategories()">
                                            <option value="">Select a product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        data-type="{{ $product->type }}"
                                                        data-has-vessels="{{ $product->has_vessel_options ? 'true' : 'false' }}"
                                                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
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
                                               value="{{ old('model_number') }}"
                                               placeholder="e.g., 3017 or 30/120" required>
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
                                               value="{{ old('name') }}"
                                               placeholder="e.g., ProPak 3017">
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
                                            <!-- Categories will be loaded via JavaScript -->
                                        </select>
                                        @error('category_id')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status -->
                                    <div>
                                        <label for="status" class="kt-label">Status</label>
                                        <select name="status" id="status" class="kt-select @error('status') border-danger @enderror">
                                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="md:col-span-2">
                                        <label for="description" class="kt-label">Description</label>
                                        <textarea name="description" id="description" rows="3"
                                                  class="kt-textarea @error('description') border-danger @enderror"
                                                  placeholder="Optional description of this version">{{ old('description') }}</textarea>
                                        @error('description')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Features -->
                        <div class="kt-card" id="features-card" style="display: none;">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Product Features</h3>
                                <div class="text-sm text-gray-500">Configure product-specific options</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <!-- Vessel Options -->
                                <div id="vessel-options" style="display: none;">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="has_vessel_options" id="has_vessel_options"
                                               class="kt-checkbox" value="1"
                                            {{ old('has_vessel_options') ? 'checked' : '' }}>
                                        <label for="has_vessel_options" class="kt-label mb-0">
                                            This version supports vessel configurations
                                        </label>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Enable this if the version can be configured with different vessel sizes
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Specifications (Optional) -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Specifications</h3>
                                <div class="text-sm text-gray-500">Additional technical specifications (JSON format)</div>
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
                                    <textarea name="specifications_json" id="specifications_json" rows="4"
                                              class="kt-textarea font-mono text-sm"
                                              placeholder='{"key": "value", "another_key": "another_value"}'>{{ old('specifications_json') }}</textarea>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Optional: Enter specifications as JSON. This will override individual fields above.
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

                        <div class="kt-card">
                            <div class="kt-card-body px-6 py-6">
                                <div class="flex flex-col gap-3">
                                    <button type="submit" class="kt-btn kt-btn-primary w-full">
                                        <i class="ki-filled ki-check"></i>
                                        Create Version
                                    </button>
                                    <a href="{{ route('versions.index') }}" class="kt-btn kt-btn-secondary w-full">
                                        <i class="ki-filled ki-cross"></i>
                                        Cancel
                                    </a>
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
                                        <div>Model numbers must be unique within each product</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Use categories to organise versions by series (e.g., 3000 Series)</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Enable vessel options for products that support different tank sizes</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Performance data can be imported later via Excel files</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->

                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        let specificationCount = 0;

        // Load categories when product changes
        function loadCategories() {
            const productSelect = document.getElementById('product_id');
            const categorySelect = document.getElementById('category_id');
            const featuresCard = document.getElementById('features-card');
            const vesselOptions = document.getElementById('vessel-options');
            const productPreview = document.getElementById('product-preview');
            const productInfo = document.getElementById('product-info');

            const selectedOption = productSelect.options[productSelect.selectedIndex];

            if (selectedOption.value) {
                // Show features card
                featuresCard.style.display = 'block';

                // Show/hide vessel options based on product
                const hasVessels = selectedOption.dataset.hasVessels === 'true';
                vesselOptions.style.display = hasVessels ? 'block' : 'none';

                // Update product preview
                productPreview.style.display = 'block';
                productInfo.innerHTML = `
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Product:</span>
                        <span class="text-sm font-medium">${selectedOption.text.split(' (')[0]}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Type:</span>
                        <span class="text-sm">${selectedOption.dataset.type}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Vessel Support:</span>
                        <span class="text-sm">${hasVessels ? 'Yes' : 'No'}</span>
                    </div>
                `;

                // Load categories via AJAX
                fetch(`/api/categories-for-product?product_id=${selectedOption.value}`)
                    .then(response => response.json())
                    .then(categories => {
                        categorySelect.innerHTML = '<option value="">Select a category</option>';
                        categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            categorySelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading categories:', error);
                        categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                    });
            } else {
                featuresCard.style.display = 'none';
                productPreview.style.display = 'none';
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

        // Auto-generate name based on product and model
        document.getElementById('product_id').addEventListener('change', function() {
            updateGeneratedName();
        });

        document.getElementById('model_number').addEventListener('input', function() {
            updateGeneratedName();
        });

        function updateGeneratedName() {
            const productSelect = document.getElementById('product_id');
            const modelInput = document.getElementById('model_number');
            const nameInput = document.getElementById('name');

            if (productSelect.value && modelInput.value && !nameInput.value) {
                const productName = productSelect.options[productSelect.selectedIndex].text.split(' (')[0];
                nameInput.value = `${productName} ${modelInput.value}`;
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.getElementById('product_id');
            if (productSelect.value) {
                loadCategories();
            }
        });
    </script>
@endsection
