@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => $version->name ?: $version->product->name . ' ' . $version->model_number,
        'subTitle' => 'Version details and performance data',
        'buttonText' => 'Edit Version',
        'buttonUrl' => route('versions.edit', $version->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('versions.index') }}" class="hover:text-primary">Versions</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">{{ $version->model_number }}</span>
            </div>

            <div class="grid gap-5 lg:gap-7.5">
                <!-- Version Overview -->
                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Details -->
                    <div class="lg:col-span-2">
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Version Information</h3>
                                <div class="flex gap-2">
                                    <a href="{{ route('versions.edit', $version->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                        <i class="ki-filled ki-pencil"></i>
                                        Edit
                                    </a>
                                    @if($version->performanceData->count() > 0)
                                        <a href="{{ route('versions.performance', $version->id) }}" class="kt-btn kt-btn-sm kt-btn-info">
                                            <i class="ki-filled ki-chart-simple"></i>
                                            Performance Data
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Product</label>
                                            <div class="mt-1">
                                                <span class="text-lg font-medium text-gray-900">{{ $version->product->name }}</span>
                                                <span class="ml-2 kt-badge kt-badge-sm kt-badge-outline">{{ $version->product->type }}</span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Model Number</label>
                                            <div class="mt-1 text-2xl font-mono font-bold text-primary">{{ $version->model_number }}</div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Display Name</label>
                                            <div class="mt-1 text-lg text-gray-900">{{ $version->name ?: 'Not set' }}</div>
                                        </div>

                                        @if($version->category)
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Category</label>
                                                <div class="mt-1">
                                                    <span class="kt-badge kt-badge-lg kt-badge-info">{{ $version->category->name }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Status</label>
                                            <div class="mt-1">
                                                @if($version->status)
                                                    <span class="kt-badge kt-badge-lg kt-badge-success">
                                                        <i class="ki-filled ki-check-circle"></i>
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="kt-badge kt-badge-lg kt-badge-secondary">
                                                        <i class="ki-filled ki-cross-circle"></i>
                                                        Inactive
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Vessel Options</label>
                                            <div class="mt-1">
                                                @if($version->has_vessel_options)
                                                    <span class="kt-badge kt-badge-lg kt-badge-success">
                                                        <i class="ki-filled ki-check"></i>
                                                        {{ $version->vesselConfigurations->count() }} configurations
                                                    </span>
                                                @else
                                                    <span class="kt-badge kt-badge-lg kt-badge-secondary">
                                                        <i class="ki-filled ki-cross"></i>
                                                        No vessel options
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Performance Records</label>
                                            <div class="mt-1">
                                                <span class="kt-badge kt-badge-lg {{ $version->performanceData->count() > 0 ? 'kt-badge-success' : 'kt-badge-warning' }}">
                                                    <i class="ki-filled ki-chart-simple"></i>
                                                    {{ $version->performanceData->count() }} records
                                                </span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Created</label>
                                            <div class="mt-1 text-sm text-gray-600">{{ $version->created_at->format('M j, Y \a\t g:i A') }}</div>
                                        </div>
                                    </div>
                                </div>

                                @if($version->description)
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <label class="text-sm font-medium text-gray-700">Description</label>
                                        <div class="mt-2 text-gray-900">{{ $version->description }}</div>
                                    </div>
                                @endif

                                @if($version->specifications)
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <label class="text-sm font-medium text-gray-700">Specifications</label>
                                        <div class="mt-2">
                                            <pre class="bg-gray-50 p-3 rounded text-sm">{{ json_encode($version->specifications, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="space-y-5">
                        <!-- Performance Summary -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Performance Summary</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                @if($version->performanceData->count() > 0)
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-primary">{{ $availableProfiles->count() }}</div>
                                                <div class="text-xs text-gray-500">Temperature Profiles</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-info">{{ $version->performanceData->count() }}</div>
                                                <div class="text-xs text-gray-500">Data Points</div>
                                            </div>
                                        </div>

                                        <div class="pt-4 border-t border-gray-200">
                                            <div class="text-sm font-medium text-gray-700 mb-2">Heat Input Range</div>
                                            <div class="text-sm text-gray-600">
                                                {{ number_format($version->performanceData->min('heat_input_kw'), 1) }} -
                                                {{ number_format($version->performanceData->max('heat_input_kw'), 1) }} kW
                                            </div>
                                        </div>

                                        @if($version->performanceData->whereNotNull('first_hour_dhw_supply')->count() > 0)
                                            <div class="pt-4 border-t border-gray-200">
                                                <div class="text-sm font-medium text-gray-700 mb-2">DHW Supply Range</div>
                                                <div class="text-sm text-gray-600">
                                                    {{ number_format($version->performanceData->min('first_hour_dhw_supply'), 0) }} -
                                                    {{ number_format($version->performanceData->max('first_hour_dhw_supply'), 0) }} L
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center py-6">
                                        <i class="ki-filled ki-chart-simple text-4xl text-gray-300"></i>
                                        <div class="mt-2 text-sm text-gray-500">No performance data available</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Product Features -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Product Features</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Temperature Profiles</span>
                                        @if($version->product->has_temperature_profiles)
                                            <i class="ki-filled ki-check text-success"></i>
                                        @else
                                            <i class="ki-filled ki-cross text-gray-400"></i>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Vessel Options</span>
                                        @if($version->product->has_vessel_options)
                                            <i class="ki-filled ki-check text-success"></i>
                                        @else
                                            <i class="ki-filled ki-cross text-gray-400"></i>
                                        @endif
                                    </div>

                                    @if($version->product->hasFeature('dhw_metrics'))
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">DHW Metrics</span>
                                            <i class="ki-filled ki-check text-success"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vessel Configurations -->
                @if($version->has_vessel_options && $version->vesselConfigurations->count() > 0)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Vessel Configurations
                                <span class="text-sm text-gray-500 font-normal">({{ $version->vesselConfigurations->count() }} options)</span>
                            </h3>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @foreach($version->vesselConfigurations as $vessel)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-medium text-gray-900">{{ $vessel->name }}</span>
                                            <span class="kt-badge kt-badge-sm kt-badge-outline">
                                                {{ $vessel->performanceData->count() }} records
                                            </span>
                                        </div>
                                        @if($vessel->capacity)
                                            <div class="text-sm text-gray-600">
                                                <strong>{{ $vessel->formatted_capacity }}</strong>
                                            </div>
                                        @endif
                                        @if($vessel->description)
                                            <div class="text-xs text-gray-500 mt-1">{{ $vessel->description }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Temperature Profiles Performance Overview -->
                @if($performanceByProfile->count() > 0)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Performance by Temperature Profile
                                <span class="text-sm text-gray-500 font-normal">({{ $performanceByProfile->count() }} profiles)</span>
                            </h3>
                            @if($version->performanceData->count() > 10)
                                <a href="{{ route('versions.performance', $version->id) }}" class="kt-btn kt-btn-sm kt-btn-info">
                                    <i class="ki-filled ki-chart-simple"></i>
                                    View Full Performance Data
                                </a>
                            @endif
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="space-y-6">
                                @foreach($performanceByProfile->take(5) as $profileData)
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="mb-4">
                                            <h4 class="font-medium text-gray-900">{{ $profileData['profile']->name }}</h4>
                                            <div class="text-xs text-gray-600 pt-2">{{ $profileData['profile']->display_name }}</div>
                                        </div>

                                        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                                            @foreach($profileData['data'] as $performance)
                                                <div class="bg-gray-50 rounded-lg p-4">
                                                    @if($performance->vesselConfiguration)
                                                        <div class="text-xs font-medium text-gray-500 mb-2">{{ $performance->vesselConfiguration->name }}</div>
                                                    @endif

                                                    <div class="space-y-2">
                                                        <div class="flex justify-between">
                                                            <span class="text-xs text-gray-600">Heat Input</span>
                                                            <span class="text-xs font-medium">{{ number_format($performance->heat_input_kw, 1) }} kW</span>
                                                        </div>

                                                        <div class="flex justify-between">
                                                            <span class="text-xs text-gray-600">Primary Flow</span>
                                                            <span class="text-xs font-medium">{{ number_format($performance->primary_flow_rate_ls, 2) }} l/s</span>
                                                        </div>

                                                        <div class="flex justify-between">
                                                            <span class="text-xs text-gray-600">Pressure Drop</span>
                                                            <span class="text-xs font-medium">{{ number_format($performance->pressure_drop_kpa, 1) }} kPa</span>
                                                        </div>

                                                        @if($performance->is_dhw_data)
                                                            <div class="flex justify-between">
                                                                <span class="text-xs text-gray-600">First Hour DHW</span>
                                                                <span class="text-xs font-medium">{{ number_format($performance->first_hour_dhw_supply, 0) }} L</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                @if($performanceByProfile->count() > 5)
                                    <div class="text-center py-4">
                                        <div class="text-sm text-gray-500 mb-3">
                                            Showing 5 of {{ $performanceByProfile->count() }} temperature profiles
                                        </div>
                                        <a href="{{ route('versions.performance', $version->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                            <i class="ki-filled ki-chart-simple"></i>
                                            View All Performance Data
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="kt-card">
                    <div class="kt-card-body px-6 py-6">
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('versions.edit', $version->id) }}" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-pencil"></i>
                                Edit Version
                            </a>

                            @if($version->performanceData->count() > 0)
                                <a href="{{ route('versions.performance', $version->id) }}" class="kt-btn kt-btn-outline">
                                    <i class="ki-filled ki-chart-simple"></i>
                                    Performance Data
                                </a>
                            @endif

                            <a href="{{ route('versions.index') }}" class="kt-btn kt-btn-outline">
                                <i class="ki-filled ki-arrow-left"></i>
                                Back to Versions
                            </a>

                            <div class="ml-auto">
                                <form method="POST" action="{{ route('versions.destroy', $version->id) }}" class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this version? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="kt-btn kt-btn-destructive">
                                        <i class="ki-filled ki-trash"></i>
                                        Delete Version
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
