@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Create Temperature Profile',
        'subTitle' => 'Define new flow and return temperature configuration',
        'buttonText' => 'Back to Profiles',
        'buttonUrl' => route('temperature-profiles.index'),
    ])
    <main class="grow" id="content" role="content">

        <div class="kt-container-fixed">

            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('temperature-profiles.index') }}" class="hover:text-primary">Temperature Profiles</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Create New Profile</span>
            </div>

            @include('partials.errorsbag')


            <form method="POST" action="{{ route('temperature-profiles.store') }}" class="space-y-6">
                @csrf

                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Temperature Configuration -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Temperature Configuration</h3>
                                <div class="text-xs text-gray-500">Set flow and return temperatures for primary and secondary circuits</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-8">
                                    <!-- Primary Circuit -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-2 mb-4">
                                            <div class="w-4 h-4 bg-blue-500 rounded"></div>
                                            <h4 class="font-medium text-gray-900">Primary Circuit</h4>
                                            <span class="text-sm text-gray-500">(Hot side)</span>
                                        </div>

                                        <div>
                                            <label for="primary_flow_temp" class="kt-label">Flow Temperature (°C) *</label>
                                            <input type="number" name="primary_flow_temp" id="primary_flow_temp"
                                                   class="kt-input @error('primary_flow_temp') border-danger @enderror"
                                                   value="{{ old('primary_flow_temp') }}"
                                                   step="0.1" min="-50" max="200" required
                                                   onchange="updatePreview()">
                                            @error('primary_flow_temp')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="text-xs text-gray-500 mt-1">Temperature entering the heat exchanger</div>
                                        </div>

                                        <div>
                                            <label for="primary_return_temp" class="kt-label">Return Temperature (°C) *</label>
                                            <input type="number" name="primary_return_temp" id="primary_return_temp"
                                                   class="kt-input @error('primary_return_temp') border-danger @enderror"
                                                   value="{{ old('primary_return_temp') }}"
                                                   step="0.1" min="-50" max="200" required
                                                   onchange="updatePreview()">
                                            @error('primary_return_temp')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="text-xs text-gray-500 mt-1">Temperature leaving the heat exchanger</div>
                                        </div>

                                        <div class="p-3 bg-blue-50 border border-blue-200 rounded">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-blue-800">Primary ΔT:</span>
                                                <span id="primary-delta" class="font-bold text-blue-900">0°C</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Secondary Circuit -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-2 mb-4">
                                            <div class="w-4 h-4 bg-green-500 rounded"></div>
                                            <h4 class="font-medium text-gray-900">Secondary Circuit</h4>
                                            <span class="text-sm text-gray-500">(Cold side)</span>
                                        </div>

                                        <div class="kt-form-item">
                                            <label for="secondary_flow_temp" class="kt-form-label">Flow Temperature (°C) *</label>
                                            <div class="kt-form-control">
                                                <input type="number" name="secondary_flow_temp" id="secondary_flow_temp"
                                                       class="kt-input @error('secondary_flow_temp') border-danger @enderror"
                                                       value="{{ old('secondary_flow_temp') }}"
                                                       step="0.1" min="-50" max="200" required
                                                       onchange="updatePreview()">
                                                @error('secondary_flow_temp')
                                                <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                                <div class="text-xs text-gray-500 mt-1">Temperature entering the heat exchanger</div>
                                            </div>
                                        </div>

                                        <div class="kt-form-item">
                                            <label for="secondary_return_temp" class="kt-form-label">Return Temperature (°C) *</label>
                                            <div class="kt-form-control">
                                                <input type="number" name="secondary_return_temp" id="secondary_return_temp"
                                                       class="kt-input @error('secondary_return_temp') border-danger @enderror"
                                                       value="{{ old('secondary_return_temp') }}"
                                                       step="0.1" min="-50" max="200" required
                                                       onchange="updatePreview()">
                                                @error('secondary_return_temp')
                                                <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                                <div class="text-xs text-gray-500 mt-1">Temperature leaving the heat exchanger</div>
                                            </div>
                                        </div>

                                        <div class="p-3 bg-green-50 border border-green-200 rounded">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-green-800">Secondary ΔT:</span>
                                                <span id="secondary-delta" class="font-bold text-green-900">0°C</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Visual Flow Diagram -->
                                <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-4">Heat Exchanger Flow Diagram</h5>
                                    <div class="grid md:grid-cols-2 gap-8">
                                        <!-- Primary Flow -->
                                        <div class="space-y-3">
                                            <div class="text-sm font-medium text-blue-700 mb-2">Primary Circuit (Hot)</div>
                                            <div class="flex items-center gap-3">
                                                <div class="flex-1 bg-blue-500 h-8 rounded flex items-center justify-center text-white text-sm font-medium">
                                                    <span id="primary-flow-display">--°C</span>
                                                </div>
                                                <div class="text-2xl">→</div>
                                                <div class="w-20 h-12 border-2 border-gray-400 rounded flex items-center justify-center text-xs font-medium">
                                                    HX
                                                </div>
                                                <div class="text-2xl">→</div>
                                                <div class="flex-1 bg-blue-300 h-8 rounded flex items-center justify-center text-white text-sm font-medium">
                                                    <span id="primary-return-display">--°C</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Secondary Flow -->
                                        <div class="space-y-3">
                                            <div class="text-sm font-medium text-green-700 mb-2">Secondary Circuit (Cold)</div>
                                            <div class="flex items-center gap-3">
                                                <div class="flex-1 bg-green-300 h-8 rounded flex items-center justify-center text-white text-sm font-medium">
                                                    <span id="secondary-flow-display">--°C</span>
                                                </div>
                                                <div class="text-2xl">→</div>
                                                <div class="w-20 h-12 border-2 border-gray-400 rounded flex items-center justify-center text-xs font-medium">
                                                    HX
                                                </div>
                                                <div class="text-2xl">→</div>
                                                <div class="flex-1 bg-green-500 h-8 rounded flex items-center justify-center text-white text-sm font-medium">
                                                    <span id="secondary-return-display">--°C</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Information -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Profile Information</h3>
                                <div class="text-xs text-gray-500">Optional details and settings</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-4">
                                    <!-- Auto-generated name preview -->
                                    <div class="kt-form-item">
                                        <label class="kt-form-label">Auto-generated Name</label>
                                        <div class="kt-form-control">
                                            <div class="kt-input bg-gray-50" id="auto-name-preview">Enter temperatures to see generated name</div>
                                            <div class="text-xs text-gray-500 mt-1">This name will be used if you don't provide a custom name below</div>
                                        </div>
                                    </div>

                                    <!-- Custom name -->
                                    <div class="kt-form-item">
                                        <label for="name" class="kt-form-label">Custom Name (Optional)</label>
                                        <div class="kt-form-control">
                                            <input type="text" name="name" id="name"
                                                   class="kt-input @error('name') border-danger @enderror"
                                                   value="{{ old('name') }}"
                                                   placeholder="Leave empty to use auto-generated name">
                                            @error('name')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="text-xs text-gray-500 mt-1">Override the auto-generated name with your own</div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="kt-form-item">
                                        <label for="description" class="kt-form-label">Description (Optional)</label>
                                        <div class="kt-form-control">
                                            <textarea name="description" id="description" rows="3"
                                                      class="kt-textarea @error('description') border-danger @enderror"
                                                      placeholder="Optional description of this temperature profile">{{ old('description') }}</textarea>
                                            @error('description')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="kt-form-item">
                                        <div class="kt-form-control">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="is_active" id="is_active"
                                                       class="kt-checkbox" value="1"
                                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label for="is_active" class="kt-form-label mb-0">
                                                    Active Profile
                                                </label>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">Only active profiles will be available for use in performance data</div>
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
                                        Create Temperature Profile
                                    </button>
                                    <a href="{{ route('temperature-profiles.index') }}" class="kt-btn kt-btn-outline w-full">
                                        <i class="ki-filled ki-cross"></i>
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Live Preview -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Profile Preview</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-700 mb-2">Generated Name</div>
                                        <div class="font-mono text-lg text-primary" id="preview-name">--</div>
                                    </div>

                                    <div>
                                        <div class="text-sm font-medium text-gray-700 mb-2">Temperature Summary</div>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Primary ΔT:</span>
                                                <span id="preview-primary-delta" class="font-medium">--°C</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Secondary ΔT:</span>
                                                <span id="preview-secondary-delta" class="font-medium">--°C</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Total Heat Transfer:</span>
                                                <span id="preview-total-delta" class="font-medium">--°C</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="temperature-warnings" class="space-y-2">
                                        <!-- Warnings will be inserted here by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Common Presets -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Common Presets</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-2">
                                    <button type="button" onclick="applyPreset(80, 60, 10, 60)" class="w-full kt-btn kt-btn-sm kt-btn-secondary text-left">
                                        80-60,10-60 (High Temp)
                                    </button>
                                    <button type="button" onclick="applyPreset(70, 50, 10, 60)" class="w-full kt-btn kt-btn-sm kt-btn-secondary text-left">
                                        70-50,10-60 (Medium Temp)
                                    </button>
                                    <button type="button" onclick="applyPreset(60, 55, 10, 55)" class="w-full kt-btn kt-btn-sm kt-btn-secondary text-left">
                                        60-55,10-55 (Low Temp)
                                    </button>
                                    <button type="button" onclick="applyPreset(55, 50, 10, 50)" class="w-full kt-btn kt-btn-sm kt-btn-secondary text-left">
                                        55-50,10-50 (DHW)
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500 mt-3">Click to quickly apply common temperature combinations</div>
                            </div>
                        </div>

                        <!-- Validation Tips -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Validation Rules</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3 text-sm text-gray-600">
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Primary flow temperature must be higher than primary return</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Secondary return temperature must be higher than secondary flow</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Temperature profiles must be unique (no duplicates)</div>
                                    </div>
                                    <div class="flex gap-2">
                                        <i class="ki-filled ki-information-2 text-info mt-0.5"></i>
                                        <div>Temperature range: -50°C to 200°C</div>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        function updatePreview() {
            const primaryFlow = parseFloat(document.getElementById('primary_flow_temp').value) || 0;
            const primaryReturn = parseFloat(document.getElementById('primary_return_temp').value) || 0;
            const secondaryFlow = parseFloat(document.getElementById('secondary_flow_temp').value) || 0;
            const secondaryReturn = parseFloat(document.getElementById('secondary_return_temp').value) || 0;

            // Update visual diagram
            document.getElementById('primary-flow-display').textContent = primaryFlow ? primaryFlow + '°C' : '--°C';
            document.getElementById('primary-return-display').textContent = primaryReturn ? primaryReturn + '°C' : '--°C';
            document.getElementById('secondary-flow-display').textContent = secondaryFlow ? secondaryFlow + '°C' : '--°C';
            document.getElementById('secondary-return-display').textContent = secondaryReturn ? secondaryReturn + '°C' : '--°C';

            // Calculate deltas
            const primaryDelta = primaryFlow - primaryReturn;
            const secondaryDelta = secondaryReturn - secondaryFlow;
            const totalDelta = primaryDelta + secondaryDelta;

            document.getElementById('primary-delta').textContent = primaryDelta.toFixed(1) + '°C';
            document.getElementById('secondary-delta').textContent = secondaryDelta.toFixed(1) + '°C';

            // Update preview sidebar
            document.getElementById('preview-primary-delta').textContent = primaryDelta.toFixed(1) + '°C';
            document.getElementById('preview-secondary-delta').textContent = secondaryDelta.toFixed(1) + '°C';
            document.getElementById('preview-total-delta').textContent = totalDelta.toFixed(1) + '°C';

            // Generate name
            if (primaryFlow && primaryReturn && secondaryFlow && secondaryReturn) {
                const generatedName = `${primaryFlow}-${primaryReturn},${secondaryFlow}-${secondaryReturn}`;
                document.getElementById('auto-name-preview').textContent = generatedName;
                document.getElementById('preview-name').textContent = generatedName;
            } else {
                document.getElementById('auto-name-preview').textContent = 'Enter temperatures to see generated name';
                document.getElementById('preview-name').textContent = '--';
            }

            // Show warnings
            updateWarnings(primaryFlow, primaryReturn, secondaryFlow, secondaryReturn);
        }

        function updateWarnings(primaryFlow, primaryReturn, secondaryFlow, secondaryReturn) {
            const warningsContainer = document.getElementById('temperature-warnings');
            warningsContainer.innerHTML = '';

            const warnings = [];

            if (primaryFlow && primaryReturn && primaryFlow <= primaryReturn) {
                warnings.push({
                    type: 'danger',
                    message: 'Primary flow temperature must be higher than return temperature'
                });
            }

            if (secondaryFlow && secondaryReturn && secondaryReturn <= secondaryFlow) {
                warnings.push({
                    type: 'danger',
                    message: 'Secondary return temperature must be higher than flow temperature'
                });
            }

            if (primaryFlow && primaryReturn && (primaryFlow - primaryReturn) < 5) {
                warnings.push({
                    type: 'warning',
                    message: 'Low primary ΔT may indicate inefficient heat transfer'
                });
            }

            if (secondaryFlow && secondaryReturn && (secondaryReturn - secondaryFlow) < 5) {
                warnings.push({
                    type: 'warning',
                    message: 'Low secondary ΔT may indicate inefficient heat transfer'
                });
            }

            warnings.forEach(warning => {
                const div = document.createElement('div');
                div.className = `p-2 text-xs rounded ${warning.type === 'danger' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-yellow-50 text-yellow-800 border border-yellow-200'}`;
                div.innerHTML = `<i class="ki-filled ki-${warning.type === 'danger' ? 'cross-circle' : 'information-2'}"></i> ${warning.message}`;
                warningsContainer.appendChild(div);
            });
        }

        function applyPreset(primaryFlow, primaryReturn, secondaryFlow, secondaryReturn) {
            document.getElementById('primary_flow_temp').value = primaryFlow;
            document.getElementById('primary_return_temp').value = primaryReturn;
            document.getElementById('secondary_flow_temp').value = secondaryFlow;
            document.getElementById('secondary_return_temp').value = secondaryReturn;
            updatePreview();
        }

        // Initialize preview on page load
        document.addEventListener('DOMContentLoaded', function() {
            updatePreview();
        });
    </script>
@endsection
