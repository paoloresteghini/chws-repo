@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Create Vessel Configuration',
        'subTitle' => 'Add a new vessel size option for heat exchanger systems',
        'buttonText' => 'Back to Configurations',
        'buttonUrl' => route('vessel-configurations.index'),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('vessel-configurations.index') }}" class="hover:text-primary">Vessel Configurations</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Create New Configuration</span>
            </div>

            <form method="POST" action="{{ route('vessel-configurations.store') }}" class="space-y-6">
                @csrf

                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Basic Information</h3>
                                <div class="text-sm text-gray-500">Configure vessel size and capacity details</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-6">
                                    <!-- Product and Version Selection -->
                                    <div class="grid md:grid-cols-2 gap-6">
                                        <!-- Product Selection -->
                                        <div>
                                            <label for="product_id" class="kt-label">Product *</label>
                                            <select name="product_id" id="product_id"
                                                    class="kt-select @error('product_id') border-danger @enderror"
                                                    onchange="loadVersionsForProduct()">
                                                <option value="">Select a product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        {{ ($selectedVersion && $selectedVersion->product_id == $product->id) || old('product_id') == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} ({{ $product->type }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('product_id')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="text-xs text-gray-500 mt-1">Only products with vessel support are shown</div>
                                        </div>

                                        <!-- Version Selection -->
                                        <div>
                                            <label for="version_id" class="kt-label">Version *</label>
                                            <select name="version_id" id="version_id"
                                                    class="kt-select @error('version_id') border-danger @enderror"
                                                    required onchange="updateVersionInfo()">
                                                <option value="">Select a version</option>
                                                @foreach($versions as $version)
                                                    <option value="{{ $version->id }}"
                                                            data-product-id="{{ $version->product_id }}"
                                                            data-model="{{ $version->model_number }}"
                                                            data-name="{{ $version->name }}"
                                                        {{ ($selectedVersion && $selectedVersion->id == $version->id) || old('version_id') == $version->id ? 'selected' : '' }}>
                                                        {{ $version->model_number }} - {{ $version->name ?: 'Version ' . $version->model_number }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('version_id')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="text-xs text-gray-500 mt-1">Choose the version to add vessel configuration to</div>
                                        </div>
                                    </div>

                                    <!-- Configuration Name -->
                                    <div>
                                        <label for="name" class="kt-label">Configuration Name *</label>
                                        <input type="text" name="name" id="name"
                                               class="kt-input @error('name') border-danger @enderror"
                                               value="{{ old('name') }}"
                                               placeholder="e.g., 1000L, Large Tank, Standard"
                                               required>
                                        @error('name')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Unique name for this vessel configuration</div>
                                    </div>

                                    <!-- Capacity Configuration -->
                                    <div class="grid md:grid-cols-3 gap-4">
                                        <div class="md:col-span-2">
                                            <label for="capacity" class="kt-label">Capacity</label>
                                            <input type="number" name="capacity" id="capacity"
                                                   class="kt-input @error('capacity') border-danger @enderror"
                                                   value="{{ old('capacity') }}"
                                                   step="0.01" min="0" max="999999.99"
                                                   placeholder="Enter capacity value"
                                                   onchange="updateNameSuggestion()">
                                            @error('capacity')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="text-xs text-gray-500 mt-1">Vessel capacity (optional if using custom naming)</div>
                                        </div>

                                        <div>
                                            <label for="capacity_unit" class="kt-label">Unit *</label>
                                            <select name="capacity_unit" id="capacity_unit"
                                                    class="kt-select @error('capacity_unit') border-danger @enderror"
                                                    required onchange="updateNameSuggestion()">
                                                <option value="L" {{ old('capacity_unit', 'L') == 'L' ? 'selected' : '' }}>Liters (L)</option>
                                                <option value="kL" {{ old('capacity_unit') == 'kL' ? 'selected' : '' }}>Kiloliters (kL)</option>
                                                <option value="gal" {{ old('capacity_unit') == 'gal' ? 'selected' : '' }}>Gallons (gal)</option>
                                                <option value="m³" {{ old('capacity_unit') == 'm³' ? 'selected' : '' }}>Cubic Meters (m³)</option>
                                            </select>
                                            @error('capacity_unit')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label for="description" class="kt-label">Description</label>
                                        <textarea name="description" id="description" rows="3"
                                                  class="kt-input @error('description') border-danger @enderror"
                                                  placeholder="Optional description of this vessel configuration">{{ old('description') }}</textarea>
                                        @error('description')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Additional details about this vessel option</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Specifications (Optional) -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Additional Specifications</h3>
                                <div class="text-sm text-gray-500">Optional technical specifications for this vessel</div>
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

                                <!-- Common Specifications Presets -->
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <label class="kt-label mb-3">Common Specifications</label>
                                    <div class="grid md:grid-cols-2 gap-3">
                                        <button type="button" onclick="addCommonSpec('material', 'Stainless Steel')"
                                                class="kt-btn kt-btn-sm kt-btn-light text-left">
                                            Add Material Specification
                                        </button>
                                        <button type="button" onclick="addCommonSpec('insulation', 'Polyurethane Foam')"
                                                class="kt-btn kt-btn-sm kt-btn-light text-left">
                                            Add Insulation Type
                                        </button>
                                        <button type="button" onclick="addCommonSpec('max_pressure', '10 bar')"
                                                class="kt-btn kt-btn-sm kt-btn-light text-left">
                                            Add Max Pressure
                                        </button>
                                        <button type="button" onclick="addCommonSpec('max_temperature', '95°C')"
                                                class="kt-btn kt-btn-sm kt-btn-light text-left">
                                            Add Max Temperature
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Version Preview -->
                        <div class="kt-card" id="version-preview" style="display: none;">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Selected Version</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3" id="version-info">
                                    <!-- Version info will be populated via JavaScript -->
                                </div>
                            </div>
                        </div>

                        <!-- Name Suggestions -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Name Suggestions</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-2" id="name-suggestions">
                                    <div class="text-sm text-gray-500">Enter capacity to see name suggestions</div>
                                </div>
                            </div>
                        </div>

                        <!-- Common Capacities -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Common Capacities</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" onclick="setCapacity(1000, 'L')" class="kt-btn kt-btn-sm kt-btn-secondary">1000L</button>
                                    <button type="button" onclick="setCapacity(1500, 'L')" class="kt-btn kt-btn-sm kt-btn-secondary">1500L</button>
                                    <button type="button" onclick="setCapacity(2000, 'L')" class="kt-btn kt-btn-sm kt-btn-secondary">2000L</button>
                                    <button type="button" onclick="setCapacity(2500, 'L')" class="kt-btn kt-btn-sm kt-btn-secondary">2500L</button>
                                    <button type="button" onclick="setCapacity(3000, 'L')" class="kt-btn kt-btn-sm kt-btn-secondary">3000L</button>
                                    <button type="button" onclick="setCapacity(5000, 'L')" class="kt-btn kt-btn-sm kt-btn-secondary">5000L</button>
                                </div>
                                <div class="text-xs text-gray-500 mt-3">Click to quickly set common vessel sizes</div>
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
                                        <div>Configuration names must be unique per version</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Use descriptive names like "1000L" or "Large Tank"</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Capacity is optional for custom configurations</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Performance data can be linked to specific vessel configurations</div>
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
                                        Create Vessel Configuration
                                    </button>
                                    <a href="{{ route('vessel-configurations.index') }}" class="kt-btn kt-btn-secondary w-full">
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

        // Load versions when product changes
        function loadVersionsForProduct() {
            const productSelect = document.getElementById('product_id');
            const versionSelect = document.getElementById('version_id');
            const selectedProductId = productSelect.value;

            // Show/hide version options based on selected product
            const versionOptions = versionSelect.querySelectorAll('option');
            versionOptions.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }

                const productId = option.dataset.productId;
                option.style.display = (selectedProductId === '' || productId === selectedProductId) ? 'block' : 'none';
            });

            // Reset version selection if it doesn't match the selected product
            if (selectedProductId && versionSelect.value) {
                const selectedOption = versionSelect.querySelector(`option[value="${versionSelect.value}"]`);
                if (selectedOption && selectedOption.dataset.productId !== selectedProductId) {
                    versionSelect.value = '';
                    updateVersionInfo();
                }
            }
        }

        // Update version info display
        function updateVersionInfo() {
            const versionSelect = document.getElementById('version_id');
            const versionPreview = document.getElementById('version-preview');
            const versionInfo = document.getElementById('version-info');

            if (versionSelect.value) {
                const selectedOption = versionSelect.options[versionSelect.selectedIndex];
                const model = selectedOption.dataset.model;
                const name = selectedOption.dataset.name;

                versionPreview.style.display = 'block';
                versionInfo.innerHTML = `
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Model:</span>
                        <span class="text-sm font-mono font-medium">${model}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Name:</span>
                        <span class="text-sm font-medium">${name || 'Version ' + model}</span>
                    </div>
                `;
            } else {
                versionPreview.style.display = 'none';
            }
        }

        // Update name suggestions based on capacity
        function updateNameSuggestion() {
            const capacity = document.getElementById('capacity').value;
            const unit = document.getElementById('capacity_unit').value;
            const suggestionsContainer = document.getElementById('name-suggestions');

            if (capacity) {
                const suggestions = [
                    `${capacity}${unit}`,
                    `${capacity}${unit} Tank`,
                    `${capacity}${unit} Vessel`,
                    capacity < 1000 ? 'Small Tank' : capacity < 3000 ? 'Medium Tank' : 'Large Tank'
                ];

                suggestionsContainer.innerHTML = suggestions.map(suggestion =>
                    `<button type="button" onclick="document.getElementById('name').value='${suggestion}'"
                             class="kt-btn kt-btn-xs kt-btn-light w-full text-left">${suggestion}</button>`
                ).join('');
            } else {
                suggestionsContainer.innerHTML = '<div class="text-sm text-gray-500">Enter capacity to see name suggestions</div>';
            }
        }

        // Set capacity quickly
        function setCapacity(capacity, unit) {
            document.getElementById('capacity').value = capacity;
            document.getElementById('capacity_unit').value = unit;
            updateNameSuggestion();
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

        // Add common specification
        function addCommonSpec(key, value) {
            const container = document.getElementById('spec-fields');
            const div = document.createElement('div');
            div.className = 'flex gap-3';
            div.innerHTML = `
                <input type="text" name="spec_keys[]" value="${key}" class="kt-input flex-1">
                <input type="text" name="spec_values[]" value="${value}" class="kt-input flex-1">
                <button type="button" onclick="this.parentElement.remove()" class="kt-btn kt-btn-sm kt-btn-danger">
                    <i class="ki-filled ki-trash"></i>
                </button>
            `;
            container.appendChild(div);
            specificationCount++;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadVersionsForProduct();
            updateVersionInfo();
            updateNameSuggestion();
        });
    </script>
@endsection
