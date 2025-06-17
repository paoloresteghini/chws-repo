@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Vessel Configuration: ' . $vesselConfiguration->name,
        'subTitle' => 'Detailed view of vessel size and capacity specifications',
        'buttonText' => 'Edit Configuration',
        'buttonUrl' => route('vessel-configurations.edit', $vesselConfiguration->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('vessel-configurations.index') }}" class="hover:text-primary">Vessel Configurations</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">{{ $vesselConfiguration->name }}</span>
            </div>

            <div class="grid gap-5 lg:gap-7.5">
                <!-- Vessel Configuration Overview -->
                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Details -->
                    <div class="lg:col-span-2">
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Vessel Configuration Details</h3>
                                <div class="flex gap-2">
                                    <a href="{{ route('vessel-configurations.edit', $vesselConfiguration->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                        <i class="ki-filled ki-pencil"></i>
                                        Edit
                                    </a>
                                    @if($vesselConfiguration->performanceData->count() == 0)
                                        <form method="POST" action="{{ route('vessel-configurations.destroy', $vesselConfiguration->id) }}"
                                              class="inline" onsubmit="return confirm('Are you sure you want to delete this vessel configuration?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="kt-btn kt-btn-sm kt-btn-danger">
                                                <i class="ki-filled ki-trash"></i>
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                    <!-- Duplicate Button -->
                                    <button onclick="showDuplicateModal()" class="kt-btn kt-btn-sm kt-btn-info">
                                        <i class="ki-filled ki-copy"></i>
                                        Duplicate
                                    </button>
                                </div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <!-- Configuration Information Grid -->
                                <div class="grid md:grid-cols-2 gap-8 mb-8">
                                    <!-- Basic Information -->
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Configuration Name</label>
                                            <div class="mt-1 text-2xl font-bold text-primary">{{ $vesselConfiguration->name }}</div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Product & Version</label>
                                            <div class="mt-1 space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium text-gray-900">{{ $vesselConfiguration->version->product->name }}</span>
                                                    <span class="kt-badge kt-badge-sm kt-badge-outline">{{ $vesselConfiguration->version->product->type }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('versions.show', $vesselConfiguration->version->id) }}"
                                                       class="text-primary hover:underline font-medium">
                                                        {{ $vesselConfiguration->version->name ?: 'Version ' . $vesselConfiguration->version->model_number }}
                                                    </a>
                                                    <span class="text-sm text-gray-500 font-mono">({{ $vesselConfiguration->version->model_number }})</span>
                                                </div>
                                            </div>
                                        </div>

                                        @if($vesselConfiguration->capacity)
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Vessel Capacity</label>
                                                <div class="mt-1">
                                                    <span class="text-3xl font-bold text-info">{{ number_format($vesselConfiguration->capacity, 0) }}</span>
                                                    <span class="text-xl text-gray-600">{{ $vesselConfiguration->capacity_unit }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Capacity Unit</label>
                                                <div class="mt-1 text-lg text-gray-600">{{ $vesselConfiguration->capacity_unit }}</div>
                                            </div>
                                        @endif

                                        @if($vesselConfiguration->description)
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Description</label>
                                                <div class="mt-1 text-gray-900">{{ $vesselConfiguration->description }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Visual Representation -->
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Visual Representation</label>
                                            <div class="mt-4 flex justify-center">
                                                <!-- Simple Vessel Diagram -->
                                                <div class="relative">
                                                    <!-- Tank Body -->
                                                    <div class="w-32 h-40 border-4 border-blue-500 rounded-lg bg-blue-50 flex items-center justify-center relative">
                                                        <!-- Capacity Text -->
                                                        @if($vesselConfiguration->capacity)
                                                            <div class="text-center">
                                                                <div class="text-lg font-bold text-blue-700">{{ number_format($vesselConfiguration->capacity, 0) }}</div>
                                                                <div class="text-sm text-blue-600">{{ $vesselConfiguration->capacity_unit }}</div>
                                                            </div>
                                                        @else
                                                            <div class="text-center">
                                                                <div class="text-sm font-medium text-blue-700">Custom</div>
                                                                <div class="text-xs text-blue-600">Vessel</div>
                                                            </div>
                                                        @endif

                                                        <!-- Tank Connections -->
                                                        <div class="absolute -top-2 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-gray-400 rounded-full"></div>
                                                        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-gray-400 rounded-full"></div>
                                                        <div class="absolute top-1/2 -left-2 transform -translate-y-1/2 w-4 h-4 bg-gray-400 rounded-full"></div>
                                                        <div class="absolute top-1/2 -right-2 transform -translate-y-1/2 w-4 h-4 bg-gray-400 rounded-full"></div>
                                                    </div>

                                                    <!-- Base -->
                                                    <div class="w-36 h-3 bg-gray-600 rounded-sm mx-auto -mt-1"></div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($vesselConfiguration->capacity)
                                            <!-- Capacity Comparisons -->
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Capacity Comparison</label>
                                                <div class="mt-2 space-y-2 text-sm">
                                                    @php
                                                        $capacityL = $vesselConfiguration->capacity_unit === 'L' ? $vesselConfiguration->capacity :
                                                                    ($vesselConfiguration->capacity_unit === 'kL' ? $vesselConfiguration->capacity * 1000 :
                                                                    ($vesselConfiguration->capacity_unit === 'm³' ? $vesselConfiguration->capacity * 1000 : $vesselConfiguration->capacity));
                                                    @endphp
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-600">Bathtubs (~300L):</span>
                                                        <span class="font-medium">{{ number_format($capacityL / 300, 1) }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-gray-600">Swimming pools (~50,000L):</span>
                                                        <span class="font-medium">{{ number_format($capacityL / 50000, 3) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Specifications -->
                                @if($vesselConfiguration->specifications)
                                    <div class="pt-6 border-t border-gray-200">
                                        <label class="text-sm font-medium text-gray-700">Technical Specifications</label>
                                        <div class="mt-3 grid md:grid-cols-2 gap-4">
                                            @foreach($vesselConfiguration->specifications as $key => $value)
                                                <div class="flex justify-between p-3 bg-gray-50 rounded">
                                                    <span class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $key) }}:</span>
                                                    <span class="text-sm font-medium text-gray-900">{{ $value }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Performance Data Summary -->
                                @if($vesselConfiguration->performanceData->count() > 0)
                                    <div class="pt-6 border-t border-gray-200">
                                        <label class="text-sm font-medium text-gray-700">Performance Data Summary</label>
                                        <div class="mt-3 grid md:grid-cols-3 gap-4">
                                            <div class="text-center p-4 bg-blue-50 rounded">
                                                <div class="text-xl font-bold text-blue-600">{{ $vesselConfiguration->performanceData->count() }}</div>
                                                <div class="text-sm text-blue-700">Performance Records</div>
                                            </div>
                                            @if($performanceStats)
                                                <div class="text-center p-4 bg-green-50 rounded">
                                                    <div class="text-xl font-bold text-green-600">{{ number_format($performanceStats->avg_heat, 1) }}</div>
                                                    <div class="text-sm text-green-700">Avg Heat Input (kW)</div>
                                                </div>
                                                <div class="text-center p-4 bg-orange-50 rounded">
                                                    <div class="text-xl font-bold text-orange-600">{{ number_format($performanceStats->avg_pressure, 1) }}</div>
                                                    <div class="text-sm text-orange-700">Avg Pressure (kPa)</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-5">
                        <!-- Quick Stats -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Configuration Stats</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-4">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-primary">{{ $vesselConfiguration->performanceData->count() }}</div>
                                        <div class="text-sm text-gray-600">Performance Records</div>
                                    </div>

                                    @if($performanceStats && $performanceStats->total_records > 0)
                                        <div class="pt-4 border-t border-gray-200 space-y-3">
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Heat Range:</span>
                                                <span class="text-sm font-medium">
                                                    {{ number_format($performanceStats->min_heat, 1) }} - {{ number_format($performanceStats->max_heat, 1) }} kW
                                                </span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Pressure Range:</span>
                                                <span class="text-sm font-medium">
                                                    {{ number_format($performanceStats->min_pressure, 1) }} - {{ number_format($performanceStats->max_pressure, 1) }} kPa
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Details -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Details</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Name:</span>
                                        <span class="text-sm font-medium">{{ $vesselConfiguration->name }}</span>
                                    </div>
                                    @if($vesselConfiguration->capacity)
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Capacity:</span>
                                            <span class="text-sm font-medium">{{ $vesselConfiguration->formatted_capacity }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Created:</span>
                                        <span class="text-sm">{{ $vesselConfiguration->created_at->format('M j, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Updated:</span>
                                        <span class="text-sm">{{ $vesselConfiguration->updated_at->format('M j, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Related Information -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Related Information</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">Product:</span>
                                        <div class="mt-1">
                                            <span class="text-sm text-gray-900">{{ $vesselConfiguration->version->product->name }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">Version:</span>
                                        <div class="mt-1">
                                            <a href="{{ route('versions.show', $vesselConfiguration->version->id) }}"
                                               class="text-sm text-primary hover:underline">
                                                {{ $vesselConfiguration->version->name ?: $vesselConfiguration->version->model_number }}
                                            </a>
                                        </div>
                                    </div>
                                    @if($vesselConfiguration->performanceData->count() > 0)
                                        <div>
                                            <span class="text-sm font-medium text-gray-700">Performance:</span>
                                            <div class="mt-1">
                                                <a href="{{ route('versions.performance', $vesselConfiguration->version->id) }}"
                                                   class="text-sm text-primary hover:underline">
                                                    View Performance Data
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Data by Temperature Profile -->
                @if($performanceByProfile->count() > 0)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Performance Data by Temperature Profile
                                <span class="text-sm text-gray-500 font-normal">({{ $performanceByProfile->count() }} profiles)</span>
                            </h3>
                            <a href="{{ route('versions.performance', $vesselConfiguration->version->id) }}" class="kt-btn kt-btn-sm kt-btn-info">
                                <i class="ki-filled ki-chart-simple"></i>
                                View All Performance Data
                            </a>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($performanceByProfile as $profileData)
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="mb-4">
                                            <h4 class="font-medium text-gray-900">{{ $profileData['profile']->name }}</h4>
                                            <div class="text-sm text-gray-600">{{ $profileData['profile']->display_name }}</div>
                                        </div>

                                        <div class="space-y-3">
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Heat Input:</span>
                                                <span class="text-sm font-medium">{{ number_format($profileData['data']->heat_input_kw, 1) }} kW</span>
                                            </div>

                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Primary Flow:</span>
                                                <span class="text-sm font-medium">{{ number_format($profileData['data']->primary_flow_rate_ls, 3) }} l/s</span>
                                            </div>

                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Secondary Flow:</span>
                                                <span class="text-sm font-medium">{{ number_format($profileData['data']->secondary_flow_rate_ls, 3) }} l/s</span>
                                            </div>

                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Pressure Drop:</span>
                                                <span class="text-sm font-medium">{{ number_format($profileData['data']->pressure_drop_kpa, 1) }} kPa</span>
                                            </div>

                                            @if($profileData['data']->is_dhw_data)
                                                <div class="border-t border-gray-200 pt-3 mt-3">
                                                    <div class="text-sm font-medium text-blue-600 mb-2">DHW Performance</div>
                                                    @if($profileData['data']->first_hour_dhw_supply)
                                                        <div class="flex justify-between">
                                                            <span class="text-sm text-gray-600">First Hour:</span>
                                                            <span class="text-sm font-medium">{{ number_format($profileData['data']->first_hour_dhw_supply, 0) }} L</span>
                                                        </div>
                                                    @endif
                                                    @if($profileData['data']->subsequent_hour_dhw_supply)
                                                        <div class="flex justify-between">
                                                            <span class="text-sm text-gray-600">Subsequent Hour:</span>
                                                            <span class="text-sm font-medium">{{ number_format($profileData['data']->subsequent_hour_dhw_supply, 0) }} L</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="kt-card">
                    <div class="kt-card-body px-6 py-6">
                        <div class="flex justify-between items-center">
                            <div class="flex gap-3">
                                <a href="{{ route('vessel-configurations.index') }}" class="kt-btn kt-btn-secondary">
                                    <i class="ki-filled ki-arrow-left"></i>
                                    Back to Configurations
                                </a>
                                <a href="{{ route('vessel-configurations.edit', $vesselConfiguration->id) }}" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-pencil"></i>
                                    Edit Configuration
                                </a>
                                <button onclick="showDuplicateModal()" class="kt-btn kt-btn-info">
                                    <i class="ki-filled ki-copy"></i>
                                    Duplicate
                                </button>
                            </div>

                            @if($vesselConfiguration->performanceData->count() == 0)
                                <form method="POST" action="{{ route('vessel-configurations.destroy', $vesselConfiguration->id) }}"
                                      class="inline" onsubmit="return confirm('Are you sure you want to delete this vessel configuration? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="kt-btn kt-btn-danger">
                                        <i class="ki-filled ki-trash"></i>
                                        Delete Configuration
                                    </button>
                                </form>
                            @else
                                <div class="text-sm text-gray-500">
                                    <i class="ki-filled ki-information-2"></i>
                                    Cannot delete - configuration is in use
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Duplicate Modal -->
    <div id="duplicate-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Duplicate Vessel Configuration</h3>

                    <form method="POST" action="{{ route('vessel-configurations.duplicate', $vesselConfiguration->id) }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="target_version_id" class="kt-label">Target Version *</label>
                                <select name="target_version_id" id="target_version_id" class="kt-select" required>
                                    <option value="">Select a version</option>
                                    @foreach($vesselConfiguration->version->product->versions->where('has_vessel_options', true) as $version)
                                        @if($version->id !== $vesselConfiguration->version_id)
                                            <option value="{{ $version->id }}">
                                                {{ $version->name ?: $version->model_number }} ({{ $version->model_number }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="text-xs text-gray-500 mt-1">Choose the version to copy this configuration to</div>
                            </div>

                            <div>
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="copy_specifications" id="copy_specifications"
                                           class="kt-checkbox" value="1" checked>
                                    <label for="copy_specifications" class="kt-label mb-0">
                                        Copy technical specifications
                                    </label>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Include all technical specifications in the duplicate</div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" onclick="hideDuplicateModal()" class="kt-btn kt-btn-secondary">Cancel</button>
                            <button type="submit" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-copy"></i>
                                Duplicate Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDuplicateModal() {
            document.getElementById('duplicate-modal').classList.remove('hidden');
        }

        function hideDuplicateModal() {
            document.getElementById('duplicate-modal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('duplicate-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideDuplicateModal();
            }
        });
    </script>
@endsection
