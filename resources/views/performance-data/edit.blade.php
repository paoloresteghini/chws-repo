@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Edit Performance Data',
        'subTitle' => 'Update performance metrics and settings',
        'buttonText' => 'View Performance Data',
        'buttonUrl' => route('performance-data.show', $performanceData->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('performance-data.index') }}" class="hover:text-primary">Performance Data</a>
                <i class="ki-filled ki-right text-xs"></i>
                <a href="{{ route('performance-data.show', $performanceData->id) }}" class="hover:text-primary">Record #{{ $performanceData->id }}</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Edit</span>
            </div>

            <form method="POST" action="{{ route('performance-data.update', $performanceData->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Product & Version Selection -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Product & Version Information</h3>
                                <div class="text-sm text-gray-500">Select the product and version for this performance data</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- Product Selection -->
                                    <div>
                                        <label for="product_id" class="kt-label">Product *</label>
                                        <select id="product_id" class="kt-select @error('product_id') border-danger @enderror"
                                                onchange="loadVersions()" disabled>
                                            <option value="">Select a product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ $performanceData->version->product_id == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }} ({{ $product->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="text-xs text-gray-500 mt-1">Product cannot be changed to maintain data integrity</div>
                                    </div>

                                    <!-- Version Selection -->
                                    <div>
                                        <label for="version_id" class="kt-label">Version/Model *</label>
                                        <select name="version_id" id="version_id"
                                                class="kt-select @error('version_id') border-danger @enderror"
                                                required onchange="loadVesselConfigurations()">
                                            <option value="">Select a version</option>
                                            @foreach($performanceData->version->product->versions as $version)
                                                <option value="{{ $version->id }}"
                                                        data-has-vessels="{{ $version->has_vessel_options ? 'true' : 'false' }}"
                                                    {{ old('version_id', $performanceData->version_id) == $version->id ? 'selected' : '' }}>
                                                    {{ $version->model_number }} - {{ $version->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('version_id')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Temperature Profile -->
                                    <div>
                                        <label for="temperature_profile_id" class="kt-label">Temperature Profile</label>
                                        <select name="temperature_profile_id" id="temperature_profile_id"
                                                class="kt-select @error('temperature_profile_id') border-danger @enderror">
                                            <option value="">No temperature profile</option>
                                            @foreach($temperatureProfiles as $profile)
                                                <option value="{{ $profile->id }}"
                                                    {{ old('temperature_profile_id', $performanceData->temperature_profile_id) == $profile->id ? 'selected' : '' }}>
                                                    {{ $profile->name }} - {{ $profile->display_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('temperature_profile_id')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Vessel Configuration -->
                                    <div id="vessel-section">
                                        <label for="vessel_configuration_id" class="kt-label">Vessel Configuration</label>
                                        <select name="vessel_configuration_id" id="vessel_configuration_id"
                                                class="kt-select @error('vessel_configuration_id') border-danger @enderror">
                                            <option value="">No vessel configuration</option>
                                            @foreach($vesselConfigurations as $vessel)
                                                <option value="{{ $vessel->id }}"
                                                    {{ old('vessel_configuration_id', $performanceData->vessel_configuration_id) == $vessel->id ? 'selected' : '' }}>
                                                    {{ $vessel->name }} ({{ $vessel->formatted_capacity }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('vessel_configuration_id')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Metrics -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Performance Metrics</h3>
                                <div class="text-sm text-gray-500">Core heat exchanger performance data</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- Heat Input -->
                                    <div>
                                        <label for="heat_input_kw" class="kt-label">Heat Input (kW) *</label>
                                        <input type="number" name="heat_input_kw" id="heat_input_kw"
                                               class="kt-input @error('heat_input_kw') border-danger @enderror"
                                               value="{{ old('heat_input_kw', $performanceData->heat_input_kw) }}"
                                               step="0.01" min="0" max="999999" required>
                                        @error('heat_input_kw')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Pressure Drop -->
                                    <div>
                                        <label for="pressure_drop_kpa" class="kt-label">Pressure Drop (kPa) *</label>
                                        <input type="number" name="pressure_drop_kpa" id="pressure_drop_kpa"
                                               class="kt-input @error('pressure_drop_kpa') border-danger @enderror"
                                               value="{{ old('pressure_drop_kpa', $performanceData->pressure_drop_kpa) }}"
                                               step="0.01" min="0" max="9999" required>
                                        @error('pressure_drop_kpa')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Primary Flow Rate -->
                                    <div>
                                        <label for="primary_flow_rate_ls" class="kt-label">Primary Flow Rate (l/s) *</label>
                                        <input type="number" name="primary_flow_rate_ls" id="primary_flow_rate_ls"
                                               class="kt-input @error('primary_flow_rate_ls') border-danger @enderror"
                                               value="{{ old('primary_flow_rate_ls', $performanceData->primary_flow_rate_ls) }}"
                                               step="0.0001" min="0" max="9999" required>
                                        @error('primary_flow_rate_ls')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Secondary Flow Rate -->
                                    <div>
                                        <label for="secondary_flow_rate_ls" class="kt-label">Secondary Flow Rate (l/s) *</label>
                                        <input type="number" name="secondary_flow_rate_ls" id="secondary_flow_rate_ls"
                                               class="kt-input @error('secondary_flow_rate_ls') border-danger @enderror"
                                               value="{{ old('secondary_flow_rate_ls', $performanceData->secondary_flow_rate_ls) }}"
                                               step="0.0001" min="0" max="9999" required>
                                        @error('secondary_flow_rate_ls')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DHW Metrics (if applicable) -->
                        <div class="kt-card" id="dhw-section" style="{{ ($performanceData->first_hour_dhw_supply || $performanceData->subsequent_hour_dhw_supply) ? 'display: block;' : 'display: none;' }}">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">DHW Performance Metrics</h3>
                                <div class="text-sm text-gray-500">Domestic Hot Water specific metrics (for Aquafast-type products)</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- First Hour DHW Supply -->
                                    <div>
                                        <label for="first_hour_dhw_supply" class="kt-label">First Hour DHW Supply (L)</label>
                                        <input type="number" name="first_hour_dhw_supply" id="first_hour_dhw_supply"
                                               class="kt-input @error('first_hour_dhw_supply') border-danger @enderror"
                                               value="{{ old('first_hour_dhw_supply', $performanceData->first_hour_dhw_supply) }}"
                                               step="0.01" min="0" max="999999">
                                        @error('first_hour_dhw_supply')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Subsequent Hour DHW Supply -->
                                    <div>
                                        <label for="subsequent_hour_dhw_supply" class="kt-label">Subsequent Hour DHW Supply (L)</label>
                                        <input type="number" name="subsequent_hour_dhw_supply" id="subsequent_hour_dhw_supply"
                                               class="kt-input @error('subsequent_hour_dhw_supply') border-danger @enderror"
                                               value="{{ old('subsequent_hour_dhw_supply', $performanceData->subsequent_hour_dhw_supply) }}"
                                               step="0.01" min="0" max="999999">
                                        @error('subsequent_hour_dhw_supply')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Metrics -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Additional Metrics</h3>
                                <div class="text-sm text-gray-500">Optional additional performance data (JSON format)</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div>
                                    <label for="additional_metrics" class="kt-label">Additional Metrics (JSON)</label>
                                    <textarea name="additional_metrics" id="additional_metrics" rows="4"
                                              class="kt-textarea font-mono text-sm @error('additional_metrics') border-danger @enderror"
                                              placeholder='{"custom_metric": "value", "another_metric": 123}'>{{ old('additional_metrics', json_encode($performanceData->additional_metrics, JSON_PRETTY_PRINT)) }}</textarea>
                                    @error('additional_metrics')
                                    <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="text-xs text-gray-500 mt-1">
                                        Optional: Enter additional metrics as JSON. Leave empty if not needed.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Current Data Summary -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Current Data</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Product:</span>
                                        <span class="font-medium">{{ $performanceData->version->product->name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Model:</span>
                                        <span class="font-medium">{{ $performanceData->version->model_number }}</span>
                                    </div>
                                    @if($performanceData->temperatureProfile)
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Temp Profile:</span>
                                            <span class="font-medium">{{ $performanceData->temperatureProfile->name }}</span>
                                        </div>
                                    @endif
                                    @if($performanceData->vesselConfiguration)
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Vessel:</span>
                                            <span class="font-medium">{{ $performanceData->vesselConfiguration->name }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Heat Input:</span>
                                        <span class="font-medium">{{ $performanceData->heat_input_kw }} kW</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Efficiency:</span>
                                        <span class="font-medium">{{ number_format($performanceData->efficiency_ratio, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Created:</span>
                                        <span class="text-sm">{{ $performanceData->created_at->format('M j, Y') }}</span>
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
                                        Update Performance Data
                                    </button>
                                    <a href="{{ route('performance-data.show', $performanceData->id) }}" class="kt-btn kt-btn-secondary w-full">
                                        <i class="ki-filled ki-eye"></i>
                                        View Performance Data
                                    </a>
                                    <a href="{{ route('performance-data.index') }}" class="kt-btn kt-btn-light w-full">
                                        <i class="ki-filled ki-arrow-left"></i>
                                        Back to List
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Validation Info -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Validation Rules</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-2 text-xs text-gray-600">
                                    <div>• Heat Input: 0 - 999,999 kW</div>
                                    <div>• Flow Rates: 0 - 9,999 l/s</div>
                                    <div>• Pressure Drop: 0 - 9,999 kPa</div>
                                    <div>• DHW Supply: 0 - 999,999 L</div>
                                    <div>• Vessel must belong to selected version</div>
                                    <div>• No duplicate combinations allowed</div>
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
                                        Delete this performance data record permanently. This action cannot be undone.
                                    </div>
                                    <form method="POST" action="{{ route('performance-data.destroy', $performanceData->id) }}"
                                          onsubmit="return confirm('Are you sure you want to delete this performance data? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-danger w-full">
                                            <i class="ki-filled ki-trash"></i>
                                            Delete Performance Data
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
        function loadVersions() {
            const productSelect = document.getElementById('product_id');
            const versionSelect = document.getElementById('version_id');

            if (productSelect.value) {
                // This would typically load versions via AJAX
                // For now, versions are already loaded based on current product
            }
        }

        function loadVesselConfigurations() {
            const versionSelect = document.getElementById('version_id');
            const vesselSelect = document.getElementById('vessel_configuration_id');
            const vesselSection = document.getElementById('vessel-section');

            const selectedOption = versionSelect.options[versionSelect.selectedIndex];
            const hasVessels = selectedOption.dataset.hasVessels === 'true';

            if (hasVessels) {
                vesselSection.style.display = 'block';
                // Load vessel configurations via AJAX if needed
                loadVesselConfigurationsAjax(versionSelect.value);
            } else {
                vesselSection.style.display = 'none';
                vesselSelect.value = '';
            }
        }

        function loadVesselConfigurationsAjax(versionId) {
            if (!versionId) return;

            fetch(`/api/vessel-configurations?version_id=${versionId}`)
                .then(response => response.json())
                .then(vessels => {
                    const vesselSelect = document.getElementById('vessel_configuration_id');
                    vesselSelect.innerHTML = '<option value="">No vessel configuration</option>';

                    vessels.forEach(vessel => {
                        const option = document.createElement('option');
                        option.value = vessel.id;
                        option.textContent = `${vessel.name} (${vessel.capacity}${vessel.capacity_unit})`;
                        vesselSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading vessel configurations:', error);
                });
        }

        // Show/hide DHW section based on product type
        function toggleDhwSection() {
            const productSelect = document.getElementById('product_id');
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const dhwSection = document.getElementById('dhw-section');

            // Show DHW section for DHW-type products or if DHW data already exists
            const isDhwProduct = selectedOption.text.includes('dhw') || selectedOption.text.includes('Aquafast');
            const hasDhwData = document.getElementById('first_hour_dhw_supply').value ||
                document.getElementById('subsequent_hour_dhw_supply').value;

            if (isDhwProduct || hasDhwData) {
                dhwSection.style.display = 'block';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadVesselConfigurations();
            toggleDhwSection();
        });
    </script>
@endsection
