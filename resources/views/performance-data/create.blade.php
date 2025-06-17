@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Add Performance Data',
        'subTitle' => 'Create new performance data record',
        'buttonText' => 'Back to Performance Data',
        'buttonUrl' => route('performance-data.index'),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('performance-data.index') }}" class="hover:text-primary">Performance Data</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Add New Record</span>
            </div>

            <form method="POST" action="{{ route('performance-data.store') }}" class="space-y-6" id="performance-form">
                @csrf

                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Product & Version Selection -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Product & Version Selection</h3>
                                <div class="text-sm text-gray-500">Select the product version for this performance data</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- Product Selection -->
                                    <div>
                                        <label for="product_id" class="kt-label">Product *</label>
                                        <select name="product_id" id="product_id"
                                                class="kt-select @error('product_id') border-danger @enderror"
                                                required onchange="loadVersions()">
                                            <option value="">Select a product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                        data-type="{{ $product->type }}"
                                                        data-has-vessels="{{ $product->has_vessel_options ? 'true' : 'false' }}"
                                                    {{ old('product_id', $selectedVersion?->product_id) == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }} ({{ $product->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('product_id')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Version Selection -->
                                    <div>
                                        <label for="version_id" class="kt-label">Version *</label>
                                        <select name="version_id" id="version_id"
                                                class="kt-select @error('version_id') border-danger @enderror"
                                                required onchange="loadVesselConfigurations()">
                                            <option value="">Select a version</option>
                                            @if($selectedVersion)
                                                <option value="{{ $selectedVersion->id }}" selected>
                                                    {{ $selectedVersion->model_number }}
                                                    @if($selectedVersion->name)
                                                        ({{ $selectedVersion->name }})
                                                    @endif
                                                </option>
                                            @endif
                                        </select>
                                        @error('version_id')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Temperature Profile -->
                                    <div>
                                        <label for="temperature_profile_id" class="kt-label">Temperature Profile</label>
                                        <select name="temperature_profile_id" id="temperature_profile_id"
                                                class="kt-select @error('temperature_profile_id') border-danger @enderror"
                                                onchange="updateTemperatureInfo()">
                                            <option value="">Select temperature profile (optional)</option>
                                            @foreach($temperatureProfiles as $profile)
                                                <option value="{{ $profile->id }}"
                                                        data-primary-flow="{{ $profile->primary_flow_temp }}"
                                                        data-primary-return="{{ $profile->primary_return_temp }}"
                                                        data-secondary-flow="{{ $profile->secondary_flow_temp }}"
                                                        data-secondary-return="{{ $profile->secondary_return_temp }}"
                                                    {{ old('temperature_profile_id') == $profile->id ? 'selected' : '' }}>
                                                    {{ $profile->name }} - {{ $profile->display_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('temperature_profile_id')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Vessel Configuration -->
                                    <div id="vessel-configuration-section" style="display: none;">
                                        <label for="vessel_configuration_id" class="kt-label">Vessel Configuration</label>
                                        <select name="vessel_configuration_id" id="vessel_configuration_id"
                                                class="kt-select @error('vessel_configuration_id') border-danger @enderror">
                                            <option value="">Select vessel configuration (optional)</option>
                                        </select>
                                        @error('vessel_configuration_id')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Temperature Profile Info -->
                                <div id="temperature-info" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded" style="display: none;">
                                    <h5 class="font-medium text-blue-900 mb-2">Selected Temperature Profile</h5>
                                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <div class="text-blue-700 font-medium">Primary Circuit</div>
                                            <div class="text-blue-600">Flow: <span id="primary-flow">-</span>°C → Return: <span id="primary-return">-</span>°C</div>
                                            <div class="text-blue-600">ΔT: <span id="primary-delta">-</span>°C</div>
                                        </div>
                                        <div>
                                            <div class="text-blue-700 font-medium">Secondary Circuit</div>
                                            <div class="text-blue-600">Flow: <span id="secondary-flow">-</span>°C → Return: <span id="secondary-return">-</span>°C</div>
                                            <div class="text-blue-600">ΔT: <span id="secondary-delta">-</span>°C</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Metrics -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Performance Metrics</h3>
                                <div class="text-sm text-gray-500">Enter the measured performance values</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- Heat Input -->
                                    <div>
                                        <label for="heat_input_kw" class="kt-label">Heat Input (kW) *</label>
                                        <input type="number" name="heat_input_kw" id="heat_input_kw"
                                               class="kt-input @error('heat_input_kw') border-danger @enderror"
                                               value="{{ old('heat_input_kw') }}"
                                               step="0.1" min="0" max="999999" required
                                               onchange="calculateEfficiency()">
                                        @error('heat_input_kw')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Total heat input to the heat exchanger</div>
                                    </div>

                                    <!-- Pressure Drop -->
                                    <div>
                                        <label for="pressure_drop_kpa" class="kt-label">Pressure Drop (kPa) *</label>
                                        <input type="number" name="pressure_drop_kpa" id="pressure_drop_kpa"
                                               class="kt-input @error('pressure_drop_kpa') border-danger @enderror"
                                               value="{{ old('pressure_drop_kpa') }}"
                                               step="0.1" min="0" max="9999" required
                                               onchange="calculateEfficiency()">
                                        @error('pressure_drop_kpa')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Pressure drop across the heat exchanger</div>
                                    </div>

                                    <!-- Primary Flow Rate -->
                                    <div>
                                        <label for="primary_flow_rate_ls" class="kt-label">Primary Flow Rate (l/s) *</label>
                                        <input type="number" name="primary_flow_rate_ls" id="primary_flow_rate_ls"
                                               class="kt-input @error('primary_flow_rate_ls') border-danger @enderror"
                                               value="{{ old('primary_flow_rate_ls') }}"
                                               step="0.001" min="0" max="9999" required
                                               onchange="calculateEfficiency()">
                                        @error('primary_flow_rate_ls')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Primary circuit flow rate in liters per second</div>
                                    </div>

                                    <!-- Secondary Flow Rate -->
                                    <div>
                                        <label for="secondary_flow_rate_ls" class="kt-label">Secondary Flow Rate (l/s) *</label>
                                        <input type="number" name="secondary_flow_rate_ls" id="secondary_flow_rate_ls"
                                               class="kt-input @error('secondary_flow_rate_ls') border-danger @enderror"
                                               value="{{ old('secondary_flow_rate_ls') }}"
                                               step="0.001" min="0" max="9999" required
                                               onchange="calculateEfficiency()">
                                        @error('secondary_flow_rate_ls')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Secondary circuit flow rate in liters per second</div>
                                    </div>
                                </div>

                                <!-- Calculated Metrics Display -->
                                <div class="mt-6 grid md:grid-cols-3 gap-4">
                                    <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                                        <div class="text-sm text-gray-600">Efficiency Ratio</div>
                                        <div class="text-lg font-bold text-primary" id="efficiency-ratio">-</div>
                                        <div class="text-xs text-gray-500">kW per (l/s)</div>
                                    </div>
                                    <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                                        <div class="text-sm text-gray-600">Flow Ratio</div>
                                        <div class="text-lg font-bold text-info" id="flow-ratio">-</div>
                                        <div class="text-xs text-gray-500">Secondary/Primary</div>
                                    </div>
                                    <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                                        <div class="text-sm text-gray-600">Pressure Efficiency</div>
                                        <div class="text-lg font-bold text-success" id="pressure-efficiency">-</div>
                                        <div class="text-xs text-gray-500">kW per kPa</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DHW Performance (Conditional) -->
                        <div class="kt-card" id="dhw-section" style="display: none;">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">DHW Performance</h3>
                                <div class="text-sm text-gray-500">Domestic Hot Water specific performance metrics</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- First Hour DHW Supply -->
                                    <div>
                                        <label for="first_hour_dhw_supply" class="kt-label">First Hour DHW Supply (L)</label>
                                        <input type="number" name="first_hour_dhw_supply" id="first_hour_dhw_supply"
                                               class="kt-input @error('first_hour_dhw_supply') border-danger @enderror"
                                               value="{{ old('first_hour_dhw_supply') }}"
                                               step="1" min="0" max="999999">
                                        @error('first_hour_dhw_supply')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Hot water delivery capacity in the first hour</div>
                                    </div>

                                    <!-- Subsequent Hour DHW Supply -->
                                    <div>
                                        <label for="subsequent_hour_dhw_supply" class="kt-label">Subsequent Hour DHW Supply (L)</label>
                                        <input type="number" name="subsequent_hour_dhw_supply" id="subsequent_hour_dhw_supply"
                                               class="kt-input @error('subsequent_hour_dhw_supply') border-danger @enderror"
                                               value="{{ old('subsequent_hour_dhw_supply') }}"
                                               step="1" min="0" max="999999">
                                        @error('subsequent_hour_dhw_supply')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Hot water delivery capacity in subsequent hours</div>
                                    </div>
                                </div>

                                <!-- DHW Performance Indicators -->
                                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">
                                    <h5 class="font-medium text-blue-900 mb-3">DHW Performance Indicators</h5>
                                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <div class="text-blue-700">Recovery Rate</div>
                                            <div class="text-blue-600" id="recovery-rate">Enter values to calculate</div>
                                        </div>
                                        <div>
                                            <div class="text-blue-700">DHW Efficiency</div>
                                            <div class="text-blue-600" id="dhw-efficiency">Enter values to calculate</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Metrics (Optional) -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Additional Metrics</h3>
                                <div class="text-sm text-gray-500">Optional additional performance data</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div id="additional-metrics-container">
                                    <div class="space-y-3" id="metric-fields">
                                        <!-- Additional metric fields will be added here -->
                                    </div>
                                    <button type="button" onclick="addMetricField()" class="kt-btn kt-btn-sm kt-btn-secondary mt-3">
                                        <i class="ki-filled ki-plus"></i>
                                        Add Custom Metric
                                    </button>
                                </div>

                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <div class="text-sm text-gray-600 mb-3">
                                        <strong>Common Additional Metrics:</strong> Fouling factor, approach temperature,
                                        effectiveness, NTU (Number of Transfer Units), thermal resistance, etc.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Performance Preview -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Performance Preview</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-700 mb-2">Selected Configuration</div>
                                        <div class="space-y-1 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Product:</span>
                                                <span id="preview-product" class="font-medium">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Version:</span>
                                                <span id="preview-version" class="font-medium">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Temp Profile:</span>
                                                <span id="preview-temp-profile" class="font-medium">-</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Vessel:</span>
                                                <span id="preview-vessel" class="font-medium">-</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="performance-summary" style="display: none;">
                                        <div class="text-sm font-medium text-gray-700 mb-2">Performance Summary</div>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Heat Input:</span>
                                                <span id="summary-heat" class="font-medium">- kW</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Total Flow:</span>
                                                <span id="summary-flow" class="font-medium">- l/s</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Pressure Drop:</span>
                                                <span id="summary-pressure" class="font-medium">- kPa</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Efficiency:</span>
                                                <span id="summary-efficiency" class="font-medium">-</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="validation-warnings" class="space-y-2">
                                        <!-- Validation warnings will appear here -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Benchmarks -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Performance Benchmarks</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3 text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                        <div class="text-gray-700">High efficiency: Ratio > 100</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                        <div class="text-gray-700">Medium efficiency: Ratio 50-100</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                        <div class="text-gray-700">Low efficiency: Ratio < 50</div>
                                    </div>
                                    <div class="pt-3 border-t border-gray-200">
                                        <div class="text-xs text-gray-500">
                                            Typical pressure drops: 10-50 kPa<br>
                                            Flow ratios: 0.1-5.0<br>
                                            DHW first hour: 500-5000L
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Quality Tips -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Data Quality Tips</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3 text-sm text-gray-600">
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Ensure flow rates are measured at steady state conditions</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Record temperatures at inlet and outlet points</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Verify pressure drop measurements under normal operation</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>DHW tests should follow standard protocols</div>
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
                                        Save Performance Data
                                    </button>
                                    <button type="button" onclick="validateAndPreview()" class="kt-btn kt-btn-info w-full">
                                        <i class="ki-filled ki-eye"></i>
                                        Preview Data
                                    </button>
                                    <a href="{{ route('performance-data.index') }}" class="kt-btn kt-btn-secondary w-full">
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
        let metricCount = 0;

        // Load versions when product changes
        function loadVersions() {
            const productSelect = document.getElementById('product_id');
            const versionSelect = document.getElementById('version_id');
            const dhwSection = document.getElementById('dhw-section');

            const selectedOption = productSelect.options[productSelect.selectedIndex];

            if (selectedOption.value) {
                // Update preview
                document.getElementById('preview-product').textContent = selectedOption.text.split(' (')[0];

                // Show/hide DHW section based on product type
                const productType = selectedOption.dataset.type;
                if (productType === 'dhw_system' || productType === 'dhw_heat_exchanger') {
                    dhwSection.style.display = 'block';
                } else {
                    dhwSection.style.display = 'none';
                }

                // Load versions via AJAX
                fetch(`/api/versions-for-product?product_id=${selectedOption.value}`)
                    .then(response => response.json())
                    .then(versions => {
                        versionSelect.innerHTML = '<option value="">Select a version</option>';
                        versions.forEach(version => {
                            const option = document.createElement('option');
                            option.value = version.id;
                            option.dataset.hasVessels = version.has_vessel_options;
                            option.textContent = version.model_number + (version.name ? ` (${version.name})` : '');
                            versionSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading versions:', error);
                        versionSelect.innerHTML = '<option value="">Error loading versions</option>';
                    });
            } else {
                versionSelect.innerHTML = '<option value="">Select a version</option>';
                document.getElementById('preview-product').textContent = '-';
                dhwSection.style.display = 'none';
            }
        }

        // Load vessel configurations when version changes
        function loadVesselConfigurations() {
            const versionSelect = document.getElementById('version_id');
            const vesselSection = document.getElementById('vessel-configuration-section');
            const vesselSelect = document.getElementById('vessel_configuration_id');

            const selectedOption = versionSelect.options[versionSelect.selectedIndex];

            if (selectedOption.value) {
                // Update preview
                document.getElementById('preview-version').textContent = selectedOption.text;

                // Show/hide vessel section
                const hasVessels = selectedOption.dataset.hasVessels === 'true';
                if (hasVessels) {
                    vesselSection.style.display = 'block';

                    // Load vessel configurations via AJAX
                    fetch(`/api/vessel-configurations?version_id=${selectedOption.value}`)
                        .then(response => response.json())
                        .then(vessels => {
                            vesselSelect.innerHTML = '<option value="">Select vessel configuration (optional)</option>';
                            vessels.forEach(vessel => {
                                const option = document.createElement('option');
                                option.value = vessel.id;
                                option.textContent = vessel.name + (vessel.capacity ? ` (${vessel.capacity}${vessel.capacity_unit})` : '');
                                vesselSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error loading vessel configurations:', error);
                        });
                } else {
                    vesselSection.style.display = 'none';
                    document.getElementById('preview-vessel').textContent = '-';
                }
            } else {
                vesselSection.style.display = 'none';
                document.getElementById('preview-version').textContent = '-';
                document.getElementById('preview-vessel').textContent = '-';
            }
        }

        // Update temperature profile information
        function updateTemperatureInfo() {
            const profileSelect = document.getElementById('temperature_profile_id');
            const tempInfo = document.getElementById('temperature-info');

            const selectedOption = profileSelect.options[profileSelect.selectedIndex];

            if (selectedOption.value) {
                const primaryFlow = selectedOption.dataset.primaryFlow;
                const primaryReturn = selectedOption.dataset.primaryReturn;
                const secondaryFlow = selectedOption.dataset.secondaryFlow;
                const secondaryReturn = selectedOption.dataset.secondaryReturn;

                document.getElementById('primary-flow').textContent = primaryFlow;
                document.getElementById('primary-return').textContent = primaryReturn;
                document.getElementById('secondary-flow').textContent = secondaryFlow;
                document.getElementById('secondary-return').textContent = secondaryReturn;
                document.getElementById('primary-delta').textContent = (primaryFlow - primaryReturn).toFixed(1);
                document.getElementById('secondary-delta').textContent = (secondaryReturn - secondaryFlow).toFixed(1);

                document.getElementById('preview-temp-profile').textContent = selectedOption.text.split(' - ')[0];
                tempInfo.style.display = 'block';
            } else {
                tempInfo.style.display = 'none';
                document.getElementById('preview-temp-profile').textContent = '-';
            }
        }

        // Calculate efficiency metrics
        function calculateEfficiency() {
            const heatInput = parseFloat(document.getElementById('heat_input_kw').value) || 0;
            const primaryFlow = parseFloat(document.getElementById('primary_flow_rate_ls').value) || 0;
            const secondaryFlow = parseFloat(document.getElementById('secondary_flow_rate_ls').value) || 0;
            const pressureDrop = parseFloat(document.getElementById('pressure_drop_kpa').value) || 0;

            // Efficiency ratio
            const efficiencyRatio = primaryFlow > 0 ? (heatInput / primaryFlow).toFixed(2) : '-';
            document.getElementById('efficiency-ratio').textContent = efficiencyRatio;

            // Flow ratio
            const flowRatio = primaryFlow > 0 ? (secondaryFlow / primaryFlow).toFixed(3) : '-';
            document.getElementById('flow-ratio').textContent = flowRatio;

            // Pressure efficiency
            const pressureEfficiency = pressureDrop > 0 ? (heatInput / pressureDrop).toFixed(2) : '-';
            document.getElementById('pressure-efficiency').textContent = pressureEfficiency;

            // Update summary
            if (heatInput > 0) {
                document.getElementById('summary-heat').textContent = heatInput + ' kW';
                document.getElementById('summary-flow').textContent = (primaryFlow + secondaryFlow).toFixed(3) + ' l/s';
                document.getElementById('summary-pressure').textContent = pressureDrop + ' kPa';
                document.getElementById('summary-efficiency').textContent = efficiencyRatio;
                document.getElementById('performance-summary').style.display = 'block';
            } else {
                document.getElementById('performance-summary').style.display = 'none';
            }

            // Show validation warnings
            updateValidationWarnings(heatInput, primaryFlow, secondaryFlow, pressureDrop, efficiencyRatio);
        }

        // Update validation warnings
        function updateValidationWarnings(heat, primaryFlow, secondaryFlow, pressure, efficiency) {
            const warningsContainer = document.getElementById('validation-warnings');
            warningsContainer.innerHTML = '';

            const warnings = [];

            if (efficiency !== '-' && parseFloat(efficiency) < 20) {
                warnings.push({ type: 'warning', message: 'Very low efficiency ratio detected' });
            }

            if (primaryFlow > 0 && secondaryFlow > 0 && (secondaryFlow / primaryFlow) > 10) {
                warnings.push({ type: 'warning', message: 'Very high flow ratio - check measurements' });
            }

            if (pressure > 100) {
                warnings.push({ type: 'warning', message: 'High pressure drop - verify reading' });
            }

            if (heat > 1000) {
                warnings.push({ type: 'info', message: 'High heat input - ensure this is correct' });
            }

            warnings.forEach(warning => {
                const div = document.createElement('div');
                div.className = `p-2 text-xs rounded ${warning.type === 'warning' ? 'bg-yellow-50 text-yellow-800 border border-yellow-200' : 'bg-blue-50 text-blue-800 border border-blue-200'}`;
                div.innerHTML = `<i class="ki-filled ki-${warning.type === 'warning' ? 'triangle-exclamation' : 'information-2'}"></i> ${warning.message}`;
                warningsContainer.appendChild(div);
            });
        }

        // Add custom metric field
        function addMetricField() {
            const container = document.getElementById('metric-fields');
            const div = document.createElement('div');
            div.className = 'flex gap-3';
            div.innerHTML = `
                <input type="text" name="metric_keys[]" placeholder="Metric name" class="kt-input flex-1">
                <input type="text" name="metric_values[]" placeholder="Value" class="kt-input flex-1">
                <input type="text" name="metric_units[]" placeholder="Unit" class="kt-input w-24">
                <button type="button" onclick="this.parentElement.remove()" class="kt-btn kt-btn-sm kt-btn-danger">
                    <i class="ki-filled ki-trash"></i>
                </button>
            `;
            container.appendChild(div);
            metricCount++;
        }

        // Validate and preview data
        function validateAndPreview() {
            const form = document.getElementById('performance-form');
            const formData = new FormData(form);

            // Basic validation
            const requiredFields = ['version_id', 'heat_input_kw', 'primary_flow_rate_ls', 'secondary_flow_rate_ls', 'pressure_drop_kpa'];
            const missingFields = requiredFields.filter(field => !formData.get(field));

            if (missingFields.length > 0) {
                alert('Please fill in all required fields: ' + missingFields.join(', '));
                return;
            }

            alert('Data validation passed! Review the preview in the sidebar before saving.');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            @if($selectedVersion)
            loadVersions();
            setTimeout(() => {
                document.getElementById('version_id').value = '{{ $selectedVersion->id }}';
                loadVesselConfigurations();
            }, 500);
            @endif
        });

        // Update vessel preview when selection changes
        document.getElementById('vessel_configuration_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('preview-vessel').textContent = selectedOption.value ? selectedOption.text : '-';
        });
    </script>
@endsection
