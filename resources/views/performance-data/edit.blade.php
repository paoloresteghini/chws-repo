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

            <form method="POST" action="{{ route('performance-data.update', $performanceData->id) }}" class="space-y-6" id="performance-form">
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
                                    <div class="kt-form-item">
                                        <label for="product_id" class="kt-form-label">Product *</label>
                                        <div class="kt-form-control">
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
                                    </div>

                                    <!-- Version Selection -->
                                    <div class="kt-form-item">
                                        <label for="version_id" class="kt-form-label">Version/Model *</label>
                                        <div class="kt-form-control">
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
                                    </div>

                                    <!-- Temperature Profile -->
                                    <div class="kt-form-item">
                                        <label for="temperature_profile_id" class="kt-form-label">Temperature Profile</label>
                                        <div class="kt-form-control">
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
                                    </div>

                                    <!-- Vessel Configuration -->
                                    <div id="vessel-section" class="kt-form-item">
                                        <label for="vessel_configuration_id" class="kt-form-label">Vessel Configuration</label>
                                        <div class="kt-form-control">
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
                        </div>

                        <!-- Calculation Method Selection -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Calculation Method</h3>
                                <div class="text-sm text-gray-500">Choose how to update heat input and flow rate data</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-3 gap-4">
                                    <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" name="calculation_method" value="heat_from_flow" class="kt-radio" onchange="toggleCalculationMethod()">
                                        <div>
                                            <div class="font-medium text-gray-900">Calculate Heat from Flow</div>
                                            <div class="text-sm text-gray-500">Enter flow rate, get heat input</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" name="calculation_method" value="flow_from_heat" class="kt-radio" onchange="toggleCalculationMethod()">
                                        <div>
                                            <div class="font-medium text-gray-900">Calculate Flow from Heat</div>
                                            <div class="text-sm text-gray-500">Enter heat input, get flow rate</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" name="calculation_method" value="manual" class="kt-radio" onchange="toggleCalculationMethod()" checked>
                                        <div>
                                            <div class="font-medium text-gray-900">Manual Entry</div>
                                            <div class="text-sm text-gray-500">Enter both values manually</div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Formula Display -->
                                <div class="mt-4 p-3 bg-gray-50 border border-gray-200 rounded">
                                    <div class="text-sm font-medium text-gray-700 mb-1">Formula Used:</div>
                                    <div class="text-sm font-mono text-gray-600">Heat Input (kW) = Primary Flow Rate (l/s) × 209.36</div>
                                    <div class="text-xs text-gray-500 mt-1">Based on standard heat transfer calculations</div>
                                </div>

                                <!-- Current Values Info -->
                                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                                    <div class="text-sm font-medium text-blue-700 mb-1">Current Values:</div>
                                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-blue-600">Heat Input: </span>
                                            <span class="font-medium">{{ $performanceData->heat_input_kw }} kW</span>
                                        </div>
                                        <div>
                                            <span class="text-blue-600">Flow Rate: </span>
                                            <span class="font-medium">{{ $performanceData->primary_flow_rate_ls }} l/s</span>
                                        </div>
                                        <div>
                                            <span class="text-blue-600">Theoretical Heat: </span>
                                            <span class="font-medium">{{ number_format($performanceData->theoretical_heat_input, 2) }} kW</span>
                                        </div>
                                        <div>
                                            <span class="text-blue-600">Variance: </span>
                                            <span class="font-medium {{ abs($performanceData->heat_input_variance) > 5 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ number_format($performanceData->heat_input_variance, 2) }} kW
                                            </span>
                                        </div>
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
                                    <div class="kt-form-item">
                                        <label for="heat_input_kw" class="kt-form-label">
                                            Heat Input (kW)
                                            <span id="heat-required-indicator">*</span>
                                            <span id="heat-calculated-indicator" class="text-blue-600 text-sm" style="display: none;">(Calculated)</span>
                                        </label>
                                        <div class="kt-form-control">
                                            <div class="relative">
                                                <input type="number" name="heat_input_kw" id="heat_input_kw"
                                                       class="kt-input @error('heat_input_kw') border-danger @enderror"
                                                       value="{{ old('heat_input_kw', $performanceData->heat_input_kw) }}"
                                                       step="0.01" min="0" max="999999" required
                                                       oninput="calculateFromHeat()" onchange="calculateEfficiency()">
                                                <button type="button" id="calculate-heat-btn" class="absolute right-2 top-1/2 transform -translate-y-1/2 kt-btn kt-btn-xs kt-btn-info" onclick="calculateHeatFromFlow()" style="display: none;">
                                                    <i class="ki-filled ki-calculator"></i>
                                                    Calculate
                                                </button>
                                            </div>
                                            @error('heat_input_kw')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div id="heat-validation" class="text-xs mt-1" style="display: none;"></div>
                                        </div>
                                    </div>

                                    <!-- Primary Flow Rate -->
                                    <div class="kt-form-item">
                                        <label for="primary_flow_rate_ls" class="kt-form-label">
                                            Primary Flow Rate (l/s)
                                            <span id="flow-required-indicator">*</span>
                                            <span id="flow-calculated-indicator" class="text-blue-600 text-sm" style="display: none;">(Calculated)</span>
                                        </label>
                                        <div class="kt-form-control">
                                            <div class="relative">
                                                <input type="number" name="primary_flow_rate_ls" id="primary_flow_rate_ls"
                                                       class="kt-input @error('primary_flow_rate_ls') border-danger @enderror"
                                                       value="{{ old('primary_flow_rate_ls', $performanceData->primary_flow_rate_ls) }}"
                                                       step="0.0001" min="0" max="9999" required
                                                       oninput="calculateFromFlow()" onchange="calculateEfficiency()">
                                                <button type="button" id="calculate-flow-btn" class="absolute right-2 top-1/2 transform -translate-y-1/2 kt-btn kt-btn-xs kt-btn-info" onclick="calculateFlowFromHeat()" style="display: none;">
                                                    <i class="ki-filled ki-calculator"></i>
                                                    Calculate
                                                </button>
                                            </div>
                                            @error('primary_flow_rate_ls')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div id="flow-validation" class="text-xs mt-1" style="display: none;"></div>
                                        </div>
                                    </div>

                                    <!-- Secondary Flow Rate -->
                                    <div class="kt-form-item">
                                        <label for="secondary_flow_rate_ls" class="kt-form-label">Secondary Flow Rate (l/s) *</label>
                                        <div class="kt-form-control">
                                            <input type="number" name="secondary_flow_rate_ls" id="secondary_flow_rate_ls"
                                                   class="kt-input @error('secondary_flow_rate_ls') border-danger @enderror"
                                                   value="{{ old('secondary_flow_rate_ls', $performanceData->secondary_flow_rate_ls) }}"
                                                   step="0.0001" min="0" max="9999" required
                                                   onchange="calculateEfficiency()">
                                            @error('secondary_flow_rate_ls')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Pressure Drop -->
                                    <div class="kt-form-item">
                                        <label for="pressure_drop_kpa" class="kt-form-label">Pressure Drop (kPa) *</label>
                                        <div class="kt-form-control">
                                            <input type="number" name="pressure_drop_kpa" id="pressure_drop_kpa"
                                                   class="kt-input @error('pressure_drop_kpa') border-danger @enderror"
                                                   value="{{ old('pressure_drop_kpa', $performanceData->pressure_drop_kpa) }}"
                                                   step="0.01" min="0" max="9999" required
                                                   onchange="calculateEfficiency()">
                                            @error('pressure_drop_kpa')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Heat/Flow Relationship Validation -->
                                <div id="relationship-validation" class="mt-6 p-4 border rounded">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i id="validation-icon" class="ki-filled ki-check-circle text-success"></i>
                                        <div class="font-medium" id="validation-title">Heat/Flow Relationship</div>
                                    </div>
                                    <div class="grid md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <div class="text-gray-600">Current Heat Input:</div>
                                            <div class="font-medium" id="current-heat">{{ $performanceData->heat_input_kw }} kW</div>
                                        </div>
                                        <div>
                                            <div class="text-gray-600">Theoretical Heat Input:</div>
                                            <div class="font-medium" id="theoretical-heat">{{ number_format($performanceData->theoretical_heat_input, 2) }} kW</div>
                                        </div>
                                        <div>
                                            <div class="text-gray-600">Variance:</div>
                                            <div class="font-medium" id="heat-variance">{{ number_format($performanceData->heat_input_variance, 2) }} kW</div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-2" id="validation-message"></div>

                                    <!-- Auto-correction buttons -->
                                    <div class="flex gap-2 mt-3">
                                        <button type="button" onclick="autoCorrectHeat()" class="kt-btn kt-btn-xs kt-btn-info">
                                            <i class="ki-filled ki-arrows-loop"></i>
                                            Auto-correct Heat Input
                                        </button>
                                        <button type="button" onclick="autoCorrectFlow()" class="kt-btn kt-btn-xs kt-btn-info">
                                            <i class="ki-filled ki-arrows-loop"></i>
                                            Auto-correct Flow Rate
                                        </button>
                                    </div>
                                </div>

                                <!-- Calculated Metrics Display -->
                                <div class="mt-6 grid md:grid-cols-3 gap-4">
                                    <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                                        <div class="text-sm text-gray-600">Efficiency Ratio</div>
                                        <div class="text-lg font-bold text-primary" id="efficiency-ratio">{{ number_format($performanceData->efficiency_ratio, 2) }}</div>
                                        <div class="text-xs text-gray-500">kW per (l/s)</div>
                                    </div>
                                    <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                                        <div class="text-sm text-gray-600">Flow Ratio</div>
                                        <div class="text-lg font-bold text-info" id="flow-ratio">{{ number_format($performanceData->secondary_flow_rate_ls / max($performanceData->primary_flow_rate_ls, 0.001), 3) }}</div>
                                        <div class="text-xs text-gray-500">Secondary/Primary</div>
                                    </div>
                                    <div class="p-3 bg-gray-50 border border-gray-200 rounded">
                                        <div class="text-sm text-gray-600">Pressure Efficiency</div>
                                        <div class="text-lg font-bold text-success" id="pressure-efficiency">{{ number_format($performanceData->heat_input_kw / max($performanceData->pressure_drop_kpa, 0.01), 2) }}</div>
                                        <div class="text-xs text-gray-500">kW per kPa</div>
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
                                    <div class="kt-form-item">
                                        <label for="first_hour_dhw_supply" class="kt-form-label">First Hour DHW Supply (L)</label>
                                        <div class="kt-form-control">
                                            <input type="number" name="first_hour_dhw_supply" id="first_hour_dhw_supply"
                                                   class="kt-input @error('first_hour_dhw_supply') border-danger @enderror"
                                                   value="{{ old('first_hour_dhw_supply', $performanceData->first_hour_dhw_supply) }}"
                                                   step="0.01" min="0" max="999999">
                                            @error('first_hour_dhw_supply')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Subsequent Hour DHW Supply -->
                                    <div class="kt-form-item">
                                        <label for="subsequent_hour_dhw_supply" class="kt-form-label">Subsequent Hour DHW Supply (L)</label>
                                        <div class="kt-form-control">
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
                        </div>

                        <!-- Additional Metrics -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Additional Metrics</h3>
                                <div class="text-sm text-gray-500">Optional additional performance data (JSON format)</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="kt-form-item">
                                    <label for="additional_metrics" class="kt-form-label">Additional Metrics (JSON)</label>
                                    <div class="kt-form-control">
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
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Actions -->
                        <div class="kt-card">
                            <div class="kt-card-body px-6 py-6">
                                <div class="flex flex-col gap-3">
                                    <button type="submit" class="kt-btn kt-btn-primary w-full">
                                        <i class="ki-filled ki-check"></i>
                                        Update Performance Data
                                    </button>
                                    <a href="{{ route('performance-data.show', $performanceData->id) }}" class="kt-btn kt-btn-outline w-full">
                                        <i class="ki-filled ki-eye"></i>
                                        View Performance Data
                                    </a>
                                    <a href="{{ route('performance-data.index') }}" class="kt-btn kt-btn-outline w-full">
                                        <i class="ki-filled ki-arrow-left"></i>
                                        Back to List
                                    </a>
                                </div>
                            </div>
                        </div>

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
                                        <span class="text-gray-600">Flow Rate:</span>
                                        <span class="font-medium">{{ $performanceData->primary_flow_rate_ls }} l/s</span>
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

                        <!-- Calculation Tools -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Calculation Tools</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div class="text-sm text-gray-600 mb-3">
                                        Use these tools to auto-correct values based on the heat transfer formula.
                                    </div>

                                    <button type="button" onclick="resetToTheoretical()" class="kt-btn kt-btn-sm kt-btn-info w-full">
                                        <i class="ki-filled ki-arrows-loop"></i>
                                        Reset to Theoretical Values
                                    </button>

                                    <div class="border-t border-gray-200 pt-3">
                                        <div class="text-xs text-gray-500">
                                            <strong>Current Accuracy:</strong><br>
                                            @if(abs($performanceData->heat_input_variance) <= 5)
                                                <span class="text-green-600">✓ Accurate ({{ number_format(abs($performanceData->heat_input_variance), 2) }} kW variance)</span>
                                            @elseif(abs($performanceData->heat_input_variance) <= 15)
                                                <span class="text-yellow-600">⚠ Minor variance ({{ number_format(abs($performanceData->heat_input_variance), 2) }} kW)</span>
                                            @else
                                                <span class="text-red-600">✗ Major variance ({{ number_format(abs($performanceData->heat_input_variance), 2) }} kW)</span>
                                            @endif
                                        </div>
                                    </div>
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
                                    <div class="pt-2 border-t border-gray-200">
                                        <strong>Formula:</strong> Heat (kW) = Flow (l/s) × 209.36
                                    </div>
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
                                        <button type="submit" class="kt-btn kt-btn-destructive w-full">
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
        // Heat transfer constant
        const HEAT_TRANSFER_CONSTANT = 209.36;
        let ignoreCalculations = false;

        // Toggle calculation method
        function toggleCalculationMethod() {
            const method = document.querySelector('input[name="calculation_method"]:checked').value;
            const heatInput = document.getElementById('heat_input_kw');
            const flowRate = document.getElementById('primary_flow_rate_ls');
            const heatBtn = document.getElementById('calculate-heat-btn');
            const flowBtn = document.getElementById('calculate-flow-btn');
            const heatRequired = document.getElementById('heat-required-indicator');
            const flowRequired = document.getElementById('flow-required-indicator');
            const heatCalculated = document.getElementById('heat-calculated-indicator');
            const flowCalculated = document.getElementById('flow-calculated-indicator');

            // Reset states
            heatInput.readOnly = false;
            flowRate.readOnly = false;
            heatBtn.style.display = 'none';
            flowBtn.style.display = 'none';
            heatRequired.style.display = 'inline';
            flowRequired.style.display = 'inline';
            heatCalculated.style.display = 'none';
            flowCalculated.style.display = 'none';
            heatInput.required = true;
            flowRate.required = true;

            switch (method) {
                case 'heat_from_flow':
                    heatInput.readOnly = true;
                    heatInput.required = false;
                    heatBtn.style.display = 'block';
                    heatRequired.style.display = 'none';
                    heatCalculated.style.display = 'inline';
                    break;
                case 'flow_from_heat':
                    flowRate.readOnly = true;
                    flowRate.required = false;
                    flowBtn.style.display = 'block';
                    flowRequired.style.display = 'none';
                    flowCalculated.style.display = 'inline';
                    break;
                case 'manual':
                    // Both fields are editable and required
                    break;
            }
        }

        // Calculate heat from flow rate
        function calculateHeatFromFlow() {
            const flowRate = parseFloat(document.getElementById('primary_flow_rate_ls').value);
            if (flowRate && flowRate > 0) {
                const heatInput = flowRate * HEAT_TRANSFER_CONSTANT;
                document.getElementById('heat_input_kw').value = heatInput.toFixed(2);
                calculateEfficiency();
                validateHeatFlowRelationship();
            }
        }

        // Calculate flow from heat input
        function calculateFlowFromHeat() {
            const heatInput = parseFloat(document.getElementById('heat_input_kw').value);
            if (heatInput && heatInput > 0) {
                const flowRate = heatInput / HEAT_TRANSFER_CONSTANT;
                document.getElementById('primary_flow_rate_ls').value = flowRate.toFixed(4);
                calculateEfficiency();
                validateHeatFlowRelationship();
            }
        }

        // Calculate from flow rate input (real-time)
        function calculateFromFlow() {
            const method = document.querySelector('input[name="calculation_method"]:checked').value;
            if (method === 'heat_from_flow' && !ignoreCalculations) {
                calculateHeatFromFlow();
            } else if (method === 'manual') {
                validateHeatFlowRelationship();
            }
        }

        // Calculate from heat input (real-time)
        function calculateFromHeat() {
            const method = document.querySelector('input[name="calculation_method"]:checked').value;
            if (method === 'flow_from_heat' && !ignoreCalculations) {
                calculateFlowFromHeat();
            } else if (method === 'manual') {
                validateHeatFlowRelationship();
            }
        }

        // Validate heat/flow relationship
        function validateHeatFlowRelationship() {
            const heatInput = parseFloat(document.getElementById('heat_input_kw').value);
            const flowRate = parseFloat(document.getElementById('primary_flow_rate_ls').value);
            const validationDiv = document.getElementById('relationship-validation');
            const validationIcon = document.getElementById('validation-icon');
            const validationTitle = document.getElementById('validation-title');
            const validationMessage = document.getElementById('validation-message');

            if (heatInput && flowRate && heatInput > 0 && flowRate > 0) {
                const theoreticalHeat = flowRate * HEAT_TRANSFER_CONSTANT;
                const variance = heatInput - theoreticalHeat;
                const absVariance = Math.abs(variance);
                const percentageError = (absVariance / theoreticalHeat) * 100;

                document.getElementById('current-heat').textContent = heatInput.toFixed(2) + ' kW';
                document.getElementById('theoretical-heat').textContent = theoreticalHeat.toFixed(2) + ' kW';
                document.getElementById('heat-variance').textContent = variance.toFixed(2) + ' kW (' + percentageError.toFixed(1) + '%)';

                if (absVariance <= 5) {
                    // Accurate relationship
                    validationDiv.className = 'mt-6 p-4 border border-green-200 bg-green-50 rounded';
                    validationIcon.className = 'ki-filled ki-check-circle text-success';
                    validationTitle.textContent = 'Heat/Flow Relationship: Accurate';
                    validationMessage.textContent = 'The heat input and flow rate values are consistent with the expected relationship.';
                } else if (absVariance <= 15) {
                    // Minor variance
                    validationDiv.className = 'mt-6 p-4 border border-yellow-200 bg-yellow-50 rounded';
                    validationIcon.className = 'ki-filled ki-triangle-exclamation text-warning';
                    validationTitle.textContent = 'Heat/Flow Relationship: Minor Variance';
                    validationMessage.textContent = 'Small deviation detected. This may be within acceptable tolerances depending on operating conditions.';
                } else {
                    // Major variance
                    validationDiv.className = 'mt-6 p-4 border border-red-200 bg-red-50 rounded';
                    validationIcon.className = 'ki-filled ki-cross-circle text-danger';
                    validationTitle.textContent = 'Heat/Flow Relationship: Major Variance';
                    validationMessage.textContent = 'Significant deviation detected. Please verify your measurements or consider using calculated values.';
                }
            }
        }

        // Auto-correct heat input based on flow rate
        function autoCorrectHeat() {
            const flowRate = parseFloat(document.getElementById('primary_flow_rate_ls').value);
            if (flowRate && flowRate > 0) {
                const correctedHeat = flowRate * HEAT_TRANSFER_CONSTANT;
                document.getElementById('heat_input_kw').value = correctedHeat.toFixed(2);
                calculateEfficiency();
                validateHeatFlowRelationship();

                // Show success message
                alert(`Heat input auto-corrected to ${correctedHeat.toFixed(2)} kW based on flow rate (${flowRate} l/s)`);
            } else {
                alert('Please enter a valid flow rate first.');
            }
        }

        // Auto-correct flow rate based on heat input
        function autoCorrectFlow() {
            const heatInput = parseFloat(document.getElementById('heat_input_kw').value);
            if (heatInput && heatInput > 0) {
                const correctedFlow = heatInput / HEAT_TRANSFER_CONSTANT;
                document.getElementById('primary_flow_rate_ls').value = correctedFlow.toFixed(4);
                calculateEfficiency();
                validateHeatFlowRelationship();

                // Show success message
                alert(`Flow rate auto-corrected to ${correctedFlow.toFixed(4)} l/s based on heat input (${heatInput} kW)`);
            } else {
                alert('Please enter a valid heat input first.');
            }
        }

        // Reset to theoretical values
        function resetToTheoretical() {
            const flowRate = parseFloat(document.getElementById('primary_flow_rate_ls').value);
            if (flowRate && flowRate > 0) {
                if (confirm('Reset heat input to theoretical value based on current flow rate?')) {
                    autoCorrectHeat();
                }
            } else {
                const heatInput = parseFloat(document.getElementById('heat_input_kw').value);
                if (heatInput && heatInput > 0) {
                    if (confirm('Reset flow rate to theoretical value based on current heat input?')) {
                        autoCorrectFlow();
                    }
                } else {
                    alert('Please enter either a heat input or flow rate value first.');
                }
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
        }

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
                    const currentValue = vesselSelect.value;
                    vesselSelect.innerHTML = '<option value="">No vessel configuration</option>';

                    vessels.forEach(vessel => {
                        const option = document.createElement('option');
                        option.value = vessel.id;
                        option.textContent = `${vessel.name} (${vessel.capacity}${vessel.capacity_unit})`;
                        if (vessel.id == currentValue) {
                            option.selected = true;
                        }
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
            // Set default calculation method and validate current data
            toggleCalculationMethod();
            calculateEfficiency();
            validateHeatFlowRelationship();

            loadVesselConfigurations();
            toggleDhwSection();
        });
    </script>
@endsection
