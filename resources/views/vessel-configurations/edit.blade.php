@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Edit Vessel Configuration: ' . $vesselConfiguration->name,
        'subTitle' => 'Update vessel size and capacity specifications',
        'buttonText' => 'View Configuration',
        'buttonUrl' => route('vessel-configurations.show', $vesselConfiguration->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('vessel-configurations.index') }}" class="hover:text-primary">Vessel Configurations</a>
                <i class="ki-filled ki-right text-xs"></i>
                <a href="{{ route('vessel-configurations.show', $vesselConfiguration->id) }}" class="hover:text-primary">{{ $vesselConfiguration->name }}</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Edit</span>
            </div>

            <form method="POST" action="{{ route('vessel-configurations.update', $vesselConfiguration->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Basic Information</h3>
                                <div class="text-sm text-gray-500">Update vessel size and capacity details</div>
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
                                                        {{ old('product_id', $vesselConfiguration->version->product_id) == $product->id ? 'selected' : '' }}>
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
                                                        {{ old('version_id', $vesselConfiguration->version_id) == $version->id ? 'selected' : '' }}>
                                                        {{ $version->model_number }} - {{ $version->name ?: 'Version ' . $version->model_number }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('version_id')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="text-xs text-gray-500 mt-1">Choose the version for this vessel configuration</div>
                                        </div>
                                    </div>

                                    <!-- Configuration Name -->
                                    <div>
                                        <label for="name" class="kt-label">Configuration Name *</label>
                                        <input type="text" name="name" id="name"
                                               class="kt-input @error('name') border-danger @enderror"
                                               value="{{ old('name', $vesselConfiguration->name) }}"
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
                                                   value="{{ old('capacity', $vesselConfiguration->capacity) }}"
                                                   step="0.01" min="0" max="999999.99"
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
                                                <option value="L" {{ old('capacity_unit', $vesselConfiguration->capacity_unit) == 'L' ? 'selected' : '' }}>Liters (L)</option>
                                                <option value="kL" {{ old('capacity_unit', $vesselConfiguration->capacity_unit) == 'kL' ? 'selected' : '' }}>Kiloliters (kL)</option>
                                                <option value="gal" {{ old('capacity_unit', $vesselConfiguration->capacity_unit) == 'gal' ? 'selected' : '' }}>Gallons (gal)</option>
                                                <option value="m³" {{ old('capacity_unit', $vesselConfiguration->capacity_unit) == 'm³' ? 'selected' : '' }}>Cubic Meters (m³)</option>
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
                                                  placeholder="Optional description of this vessel configuration">{{ old('description', $vesselConfiguration->description) }}</textarea>
                                        @error('description')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Additional details about this vessel option</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Specifications -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Additional Specifications</h3>
                                <div class="text-sm text-gray-500">Technical specifications for this vessel</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div id="specifications-container">
                                    <div class="space-y-3" id="spec-fields">
                                        @if($vesselConfiguration->specifications)
                                            @foreach($vesselConfiguration->specifications as $key => $value)
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

                        <!-- Usage Information -->
                        @if($vesselConfiguration->performanceData->count() > 0)
                            <div class="kt-card border-info">
                                <div class="kt-card-header bg-info-light">
                                    <h3 class="kt-card-title text-info">Usage Information</h3>
                                </div>
                                <div class="kt-card-body px-6 py-6">
                                    <div class="flex items-start gap-3">
                                        <i class="ki-filled ki-information-2 text-info mt-1"></i>
                                        <div>
                                            <div class="font-medium text-info">This vessel configuration is currently in use</div>
                                            <div class="text-sm text-gray-600 mt-1">
                                                This configuration has <strong>{{ $vesselConfiguration->performanceData->count() }} performance records</strong> associated with it.
                                                Changes will affect how this configuration is displayed but won't modify existing performance data.
                                            </div>
                                            <div class="mt-3">
                                                <a href="{{ route('vessel-configurations.show', $vesselConfiguration->id) }}" class="kt-btn kt-btn-sm kt-btn-info">
                                                    <i class="ki-filled ki-eye"></i>
                                                    View Usage Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Current Configuration Info -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Current Configuration</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Name:</span>
                                        <span class="text-sm font-medium">{{ $vesselConfiguration->name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Product:</span>
                                        <span class="text-sm font-medium">{{ $vesselConfiguration->version->product->name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Version:</span>
                                        <span class="text-sm font-medium">{{ $vesselConfiguration->version->model_number }}</span>
                                    </div>
                                    @if($vesselConfiguration->capacity)
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Capacity:</span>
                                            <span class="text-sm font-medium">{{ $vesselConfiguration->formatted_capacity }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Usage:</span>
                                        <span class="text-sm">{{ $vesselConfiguration->performanceData->count() }} records</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Created:</span>
                                        <span class="text-sm">{{ $vesselConfiguration->created_at->format('M j, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Version Preview -->
                        <div class="kt-card" id="version-preview">
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
                                    <!-- Suggestions will be populated via JavaScript -->
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="kt-card">
                            <div class="kt-card-body px-6 py-6">
                                <div class="flex flex-col gap-3">
                                    <button type="submit" class="kt-btn kt-btn-primary w-full">
                                        <i class="ki-filled ki-check"></i>
                                        Update Vessel Configuration
                                    </button>
                                    <a href="{{ route('vessel-configurations.show', $vesselConfiguration->id) }}" class="kt-btn kt-btn-secondary w-full">
                                        <i class="ki-filled ki-eye"></i>
                                        View Configuration
                                    </a>
                                    <a href="{{ route('vessel-configurations.index') }}" class="kt-btn kt-btn-light w-full">
                                        <i class="ki-filled ki-arrow-left"></i>
                                        Back to List
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        @if($vesselConfiguration->performanceData->count() == 0)
                            <div class="kt-card border-danger">
                                <div class="kt-card-header bg-danger-light">
                                    <h3 class="kt-card-title text-danger">Danger Zone</h3>
                                </div>
                                <div class="kt-card-body px-6 py-6">
                                    <div class="space-y-3">
                                        <div class="text-sm text-gray-600">
                                            Delete this vessel configuration permanently. This action cannot be undone.
                                        </div>
                                        <form method="POST" action="{{ route('vessel-configurations.destroy', $vesselConfiguration->id) }}"
                                              onsubmit="return confirm('Are you sure you want to delete this vessel configuration? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="kt-btn kt-btn-danger w-full">
                                                <i class="ki-filled ki-trash"></i>
                                                Delete Vessel Configuration
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="kt-card border-gray-300">
                                <div class="kt-card-header bg-gray-50">
                                    <h3 class="kt-card-title text-gray-600">Delete Restricted</h3>
                                </div>
                                <div class="kt-card-body px-6 py-6">
                                    <div class="flex items-start gap-3">
                                        <i class="ki-filled ki-shield-cross text-gray-400 mt-1"></i>
                                        <div class="text-sm text-gray-600">
                                            This vessel configuration cannot be deleted because it has {{ $vesselConfiguration->performanceData->count() }} associated performance records.
                                        </div>
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
        let specificationCount = {{ $vesselConfiguration->specifications ? count($vesselConfiguration->specifications) : 0 }};

        // Load versions when product changes
        function loadVersionsForProduct() {
            const productSelect = document.getElementById('product_id');
            const versionSelect = document.getElementById('version_id');
            const selectedProductId = productSelect.value;
            const currentVersionId = {{ old('version_id', $vesselConfiguration->version_id) }};

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
            const versionInfo = document.getElementById('version-info');

            if (versionSelect.value) {
                const selectedOption = versionSelect.options[versionSelect.selectedIndex];
                const model = selectedOption.dataset.model;
                const name = selectedOption.dataset.name;

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
                versionInfo.innerHTML = '<div class="text-sm text-gray-500">Select a version to see details</div>';
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
            updateVersionInfo();
            updateNameSuggestion();
        });
    </script>
@endsection
