@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Edit Temperature Profile: ' . $temperatureProfile->name,
        'subTitle' => 'Update flow and return temperature configuration',
        'buttonText' => 'View Profile',
        'buttonUrl' => route('temperature-profiles.show', $temperatureProfile->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('temperature-profiles.index') }}" class="hover:text-primary">Temperature Profiles</a>
                <i class="ki-filled ki-right text-xs"></i>
                <a href="{{ route('temperature-profiles.show', $temperatureProfile->id) }}" class="hover:text-primary">{{ $temperatureProfile->name }}</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Edit</span>
            </div>

            <form method="POST" action="{{ route('temperature-profiles.update', $temperatureProfile->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Temperature Configuration -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Temperature Configuration</h3>
                                <div class="text-sm text-gray-500">Update flow and return temperatures for primary and secondary circuits</div>
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
                                                   value="{{ old('primary_flow_temp', $temperatureProfile->primary_flow_temp) }}"
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
                                                   value="{{ old('primary_return_temp', $temperatureProfile->primary_return_temp) }}"
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
                                                <span id="primary-delta" class="font-bold text-blue-900">{{ $temperatureProfile->primary_temp_difference }}°C</span>
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

                                        <div>
                                            <label for="secondary_flow_temp" class="kt-label">Flow Temperature (°C) *</label>
                                            <input type="number" name="secondary_flow_temp" id="secondary_flow_temp"
                                                   class="kt-input @error('secondary_flow_temp') border-danger @enderror"
                                                   value="{{ old('secondary_flow_temp', $temperatureProfile->secondary_flow_temp) }}"
                                                   step="0.1" min="-50" max="200" required
                                                   onchange="updatePreview()">
                                            @error('secondary_flow_temp')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="text-xs text-gray-500 mt-1">Temperature entering the heat exchanger</div>
                                        </div>

                                        <div>
                                            <label for="secondary_return_temp" class="kt-label">Return Temperature (°C) *</label>
                                            <input type="number" name="secondary_return_temp" id="secondary_return_temp"
                                                   class="kt-input @error('secondary_return_temp') border-danger @enderror"
                                                   value="{{ old('secondary_return_temp', $temperatureProfile->secondary_return_temp) }}"
                                                   step="0.1" min="-50" max="200" required
                                                   onchange="updatePreview()">
                                            @error('secondary_return_temp')
                                            <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                            @enderror
                                            <div class="text-xs text-gray-500 mt-1">Temperature leaving the heat exchanger</div>
                                        </div>

                                        <div class="p-3 bg-green-50 border border-green-200 rounded">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm text-green-800">Secondary ΔT:</span>
                                                <span id="secondary-delta" class="font-bold text-green-900">{{ $temperatureProfile->secondary_temp_difference }}°C</span>
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
                                                    <span id="primary-flow-display">{{ $temperatureProfile->primary_flow_temp }}°C</span>
                                                </div>
                                                <div class="text-2xl">→</div>
                                                <div class="w-20 h-12 border-2 border-gray-400 rounded flex items-center justify-center text-xs font-medium">
                                                    HX
                                                </div>
                                                <div class="text-2xl">→</div>
                                                <div class="flex-1 bg-blue-300 h-8 rounded flex items-center justify-center text-white text-sm font-medium">
                                                    <span id="primary-return-display">{{ $temperatureProfile->primary_return_temp }}°C</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Secondary Flow -->
                                        <div class="space-y-3">
                                            <div class="text-sm font-medium text-green-700 mb-2">Secondary Circuit (Cold)</div>
                                            <div class="flex items-center gap-3">
                                                <div class="flex-1 bg-green-300 h-8 rounded flex items-center justify-center text-white text-sm font-medium">
                                                    <span id="secondary-flow-display">{{ $temperatureProfile->secondary_flow_temp }}°C</span>
                                                </div>
                                                <div class="text-2xl">→</div>
                                                <div class="w-20 h-12 border-2 border-gray-400 rounded flex items-center justify-center text-xs font-medium">
                                                    HX
                                                </div>
                                                <div class="text-2xl">→</div>
                                                <div class="flex-1 bg-green-500 h-8 rounded flex items-center justify-center text-white text-sm font-medium">
                                                    <span id="secondary-return-display">{{ $temperatureProfile->secondary_return_temp }}°C</span>
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
                                <div class="text-sm text-gray-500">Profile details and settings</div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-4">
                                    <!-- Profile name -->
                                    <div>
                                        <label for="name" class="kt-label">Profile Name *</label>
                                        <input type="text" name="name" id="name"
                                               class="kt-input @error('name') border-danger @enderror"
                                               value="{{ old('name', $temperatureProfile->name) }}"
                                               required>
                                        @error('name')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="text-xs text-gray-500 mt-1">Unique identifier for this temperature profile</div>
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label for="description" class="kt-label">Description</label>
                                        <textarea name="description" id="description" rows="3"
                                                  class="kt-input @error('description') border-danger @enderror"
                                                  placeholder="Optional description of this temperature profile">{{ old('description', $temperatureProfile->description) }}</textarea>
                                        @error('description')
                                        <div class="text-sm text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status -->
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="is_active" id="is_active"
                                                   class="kt-checkbox" value="1"
                                                {{ old('is_active', $temperatureProfile->is_active) ? 'checked' : '' }}>
                                            <label for="is_active" class="kt-label mb-0">
                                                Active Profile
                                            </label>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">Only active profiles will be available for use in performance data</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Usage Information -->
                        @if($temperatureProfile->performance_data_count > 0)
                            <div class="kt-card border-info">
                                <div class="kt-card-header bg-info-light">
                                    <h3 class="kt-card-title text-info">Usage Information</h3>
                                </div>
                                <div class="kt-card-body px-6 py-6">
                                    <div class="flex items-start gap-3">
                                        <i class="ki-filled ki-information-2 text-info mt-1"></i>
                                        <div>
                                            <div class="font-medium text-info">This temperature profile is currently in use</div>
                                            <div class="text-sm text-gray-600 mt-1">
                                                This profile has <strong>{{ $temperatureProfile->performance_data_count }} performance records</strong> associated with it.
                                                Changes to the temperature values will affect how this profile is displayed but won't change existing performance data.
                                            </div>
                                            <div class="mt-3">
                                                <a href="{{ route('temperature-profiles.show', $temperatureProfile->id) }}" class="kt-btn kt-btn-sm kt-btn-info">
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
                        <!-- Current Profile Info -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Current Profile</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Name:</span>
                                        <span class="text-sm font-medium">{{ $temperatureProfile->name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Status:</span>
                                        <span class="kt-badge kt-badge-xs {{ $temperatureProfile->is_active ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                                            {{ $temperatureProfile->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Usage:</span>
                                        <span class="text-sm">{{ $temperatureProfile->performance_data_count }} records</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Created:</span>
                                        <span class="text-sm">{{ $temperatureProfile->created_at->format('M j, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Updated:</span>
                                        <span class="text-sm">{{ $temperatureProfile->updated_at->format('M j, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Live Preview -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Updated Preview</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-700 mb-2">Temperature Summary</div>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Primary ΔT:</span>
                                                <span id="preview-primary-delta" class="font-medium">{{ $temperatureProfile->primary_temp_difference }}°C</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Secondary ΔT:</span>
                                                <span id="preview-secondary-delta" class="font-medium">{{ $temperatureProfile->secondary_temp_difference }}°C</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Total Heat Transfer:</span>
                                                <span id="preview-total-delta" class="font-medium">{{ $temperatureProfile->primary_temp_difference + $temperatureProfile->secondary_temp_difference }}°C</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="temperature-warnings" class="space-y-2">
                                        <!-- Warnings will be inserted here by JavaScript -->
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
                                        Update Temperature Profile
                                    </button>
                                    <a href="{{ route('temperature-profiles.show', $temperatureProfile->id) }}" class="kt-btn kt-btn-secondary w-full">
                                        <i class="ki-filled ki-eye"></i>
                                        View Profile
                                    </a>
                                    <a href="{{ route('temperature-profiles.index') }}" class="kt-btn kt-btn-light w-full">
                                        <i class="ki-filled ki-arrow-left"></i>
                                        Back to List
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        @if($temperatureProfile->performance_data_count == 0)
                            <div class="kt-card border-danger">
                                <div class="kt-card-header bg-danger-light">
                                    <h3 class="kt-card-title text-danger">Danger Zone</h3>
                                </div>
                                <div class="kt-card-body px-6 py-6">
                                    <div class="space-y-3">
                                        <div class="text-sm text-gray-600">
                                            Delete this temperature profile permanently. This action cannot be undone.
                                        </div>
                                        <form method="POST" action="{{ route('temperature-profiles.destroy', $temperatureProfile->id) }}"
                                              onsubmit="return confirm('Are you sure you want to delete this temperature profile? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="kt-btn kt-btn-danger w-full">
                                                <i class="ki-filled ki-trash"></i>
                                                Delete Temperature Profile
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
                                            This temperature profile cannot be deleted because it has {{ $temperatureProfile->performance_data_count }} associated performance records.
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

        // Initialize preview on page load
        document.addEventListener('DOMContentLoaded', function() {
            updatePreview();
        });
    </script>
@endsection
