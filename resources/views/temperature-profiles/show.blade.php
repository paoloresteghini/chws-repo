@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Temperature Profile: ' . $temperatureProfile->name,
        'subTitle' => 'Detailed view of flow and return temperature configuration',
        'buttonText' => 'Edit Profile',
        'buttonUrl' => route('temperature-profiles.edit', $temperatureProfile->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('temperature-profiles.index') }}" class="hover:text-primary">Temperature Profiles</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">{{ $temperatureProfile->name }}</span>
            </div>

            <div class="grid gap-5 lg:gap-7.5">
                <!-- Temperature Profile Overview -->
                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Details -->
                    <div class="lg:col-span-2">
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Temperature Configuration</h3>
                                <div class="flex gap-2">
                                    <a href="{{ route('temperature-profiles.edit', $temperatureProfile->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                        <i class="ki-filled ki-pencil"></i>
                                        Edit
                                    </a>
                                    @if($temperatureProfile->performance_data_count == 0)
                                        <form method="POST" action="{{ route('temperature-profiles.destroy', $temperatureProfile->id) }}"
                                              class="inline" onsubmit="return confirm('Are you sure you want to delete this temperature profile?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="kt-btn kt-btn-sm kt-btn-danger">
                                                <i class="ki-filled ki-trash"></i>
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="kt-card-body">
                                <!-- Temperature Values Grid -->
                                <div class="grid md:grid-cols-2 gap-8 mb-8">
                                    <!-- Primary Circuit -->
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-2 mb-4">
                                            <div class="w-4 h-4 bg-blue-500 rounded"></div>
                                            <h4 class="font-medium text-gray-900">Primary Circuit</h4>
                                            <span class="text-sm text-gray-500">(Hot side)</span>
                                        </div>

                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center p-3 bg-blue-50 border border-blue-200 rounded">
                                                <span class="text-sm text-blue-800">Flow Temperature</span>
                                                <span class="text-xl font-bold text-blue-900">{{ $temperatureProfile->primary_flow_temp }}°C</span>
                                            </div>
                                            <div class="flex justify-between items-center p-3 bg-blue-50 border border-blue-200 rounded">
                                                <span class="text-sm text-blue-800">Return Temperature</span>
                                                <span class="text-xl font-bold text-blue-900">{{ $temperatureProfile->primary_return_temp }}°C</span>
                                            </div>
                                            <div class="flex justify-between items-center p-3 bg-blue-100 border border-blue-300 rounded">
                                                <span class="text-sm font-medium text-blue-800">Temperature Difference (ΔT)</span>
                                                <span class="text-xl font-bold text-blue-900">{{ $temperatureProfile->primary_temp_difference }}°C</span>
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

                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center p-3 bg-green-50 border border-green-200 rounded">
                                                <span class="text-sm text-green-800">Flow Temperature</span>
                                                <span class="text-xl font-bold text-green-900">{{ $temperatureProfile->secondary_flow_temp }}°C</span>
                                            </div>
                                            <div class="flex justify-between items-center p-3 bg-green-50 border border-green-200 rounded">
                                                <span class="text-sm text-green-800">Return Temperature</span>
                                                <span class="text-xl font-bold text-green-900">{{ $temperatureProfile->secondary_return_temp }}°C</span>
                                            </div>
                                            <div class="flex justify-between items-center p-3 bg-green-100 border border-green-300 rounded">
                                                <span class="text-sm font-medium text-green-800">Temperature Difference (ΔT)</span>
                                                <span class="text-xl font-bold text-green-900">{{ $temperatureProfile->secondary_temp_difference }}°C</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Visual Flow Diagram -->
                                <div class="p-6 bg-gray-50 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-6">Heat Exchanger Flow Diagram</h5>
                                    <div class="space-y-6">
                                        <!-- Primary Flow -->
                                        <div class="space-y-3">
                                            <div class="text-sm font-medium text-blue-700 mb-2">Primary Circuit (Hot Side)</div>
                                            <div class="flex items-center gap-4">
                                                <div class="flex-1 bg-blue-500 h-12 rounded flex items-center justify-center text-white font-medium">
                                                    {{ $temperatureProfile->primary_flow_temp }}°C IN
                                                </div>
                                                <div class="text-3xl text-gray-400">→</div>
                                                <div class="w-24 h-16 border-2 border-gray-400 rounded flex items-center justify-center font-bold text-gray-600">
                                                    HEAT<br>EXCHANGER
                                                </div>
                                                <div class="text-3xl text-gray-400">→</div>
                                                <div class="flex-1 bg-blue-300 h-12 rounded flex items-center justify-center text-white font-medium">
                                                    {{ $temperatureProfile->primary_return_temp }}°C OUT
                                                </div>
                                            </div>
                                            <div class="text-center text-sm text-blue-600 font-medium">
                                                Heat Released: {{ $temperatureProfile->primary_temp_difference }}°C
                                            </div>
                                        </div>

                                        <!-- Heat Transfer Arrow -->
                                        <div class="flex justify-center">
                                            <div class="flex flex-col items-center">
                                                <div class="text-2xl text-red-500">↓</div>
                                                <div class="text-sm font-medium text-red-600">Heat Transfer</div>
                                                <div class="text-2xl text-red-500">↑</div>
                                            </div>
                                        </div>

                                        <!-- Secondary Flow -->
                                        <div class="space-y-3">
                                            <div class="text-sm font-medium text-green-700 mb-2">Secondary Circuit (Cold Side)</div>
                                            <div class="flex items-center gap-4">
                                                <div class="flex-1 bg-green-300 h-12 rounded flex items-center justify-center text-white font-medium">
                                                    {{ $temperatureProfile->secondary_flow_temp }}°C IN
                                                </div>
                                                <div class="text-3xl text-gray-400">→</div>
                                                <div class="w-24 h-16 border-2 border-gray-400 rounded flex items-center justify-center font-bold text-gray-600 bg-gray-100">
                                                    HEAT<br>EXCHANGER
                                                </div>
                                                <div class="text-3xl text-gray-400">→</div>
                                                <div class="flex-1 bg-green-500 h-12 rounded flex items-center justify-center text-white font-medium">
                                                    {{ $temperatureProfile->secondary_return_temp }}°C OUT
                                                </div>
                                            </div>
                                            <div class="text-center text-sm text-green-600 font-medium">
                                                Heat Absorbed: {{ $temperatureProfile->secondary_temp_difference }}°C
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Total Heat Transfer -->
                                    <div class="mt-6 p-4 bg-orange-50 border border-orange-200 rounded text-center">
                                        <div class="text-lg font-bold text-orange-900">
                                            Total Heat Transfer Potential: {{ $temperatureProfile->primary_temp_difference + $temperatureProfile->secondary_temp_difference }}°C
                                        </div>
                                        <div class="text-sm text-orange-700 mt-1">
                                            Combined temperature difference across both circuits
                                        </div>
                                    </div>
                                </div>

                                @if($temperatureProfile->description)
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <label class="text-sm font-medium text-gray-700">Description</label>
                                        <div class="mt-2 text-gray-900">{{ $temperatureProfile->description }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Profile Stats -->
                    <div class="space-y-5">
                        <!-- Quick Stats -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Profile Statistics</h3>
                            </div>
                            <div class="kt-card-body">
                                <div class="space-y-4">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-primary">{{ $temperatureProfile->performance_data_count }}</div>
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
                                                <span class="text-sm text-gray-600">Avg Heat:</span>
                                                <span class="text-sm font-medium">{{ number_format($performanceStats->avg_heat, 1) }} kW</span>
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

                        <!-- Profile Details -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Profile Details</h3>
                            </div>
                            <div class="kt-card-body">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Name:</span>
                                        <span class="text-sm font-medium">{{ $temperatureProfile->name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Status:</span>
                                        <span class="kt-badge kt-badge-sm {{ $temperatureProfile->is_active ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                                            {{ $temperatureProfile->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Created:</span>
                                        <span class="text-sm">{{ $temperatureProfile->created_at->format('M j, Y \a\t g:i A') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Updated:</span>
                                        <span class="text-sm">{{ $temperatureProfile->updated_at->format('M j, Y \a\t g:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Temperature Analysis -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Temperature Analysis</h3>
                            </div>
                            <div class="kt-card-body">
                                <div class="space-y-4">
                                    <!-- Efficiency Indicators -->
                                    <div>
                                        <div class="text-sm font-medium text-gray-700 mb-2">Heat Transfer Efficiency</div>
                                        @php
                                            $primaryEfficiency = $temperatureProfile->primary_temp_difference >= 10 ? 'High' : ($temperatureProfile->primary_temp_difference >= 5 ? 'Medium' : 'Low');
                                            $secondaryEfficiency = $temperatureProfile->secondary_temp_difference >= 10 ? 'High' : ($temperatureProfile->secondary_temp_difference >= 5 ? 'Medium' : 'Low');
                                            $primaryColor = $primaryEfficiency === 'High' ? 'success' : ($primaryEfficiency === 'Medium' ? 'warning' : 'danger');
                                            $secondaryColor = $secondaryEfficiency === 'High' ? 'success' : ($secondaryEfficiency === 'Medium' ? 'warning' : 'danger');
                                        @endphp
                                        <div class="space-y-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs text-gray-600">Primary ΔT:</span>
                                                <span class="kt-badge kt-badge-xs kt-badge-{{ $primaryColor }}">{{ $primaryEfficiency }}</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs text-gray-600">Secondary ΔT:</span>
                                                <span class="kt-badge kt-badge-xs kt-badge-{{ $secondaryColor }}">{{ $secondaryEfficiency }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Application Type -->
                                    <div>
                                        <div class="text-sm font-medium text-gray-700 mb-2">Typical Applications</div>
                                        <div class="space-y-1 text-xs text-gray-600">
                                            @if($temperatureProfile->primary_flow_temp >= 70)
                                                <div>• High temperature heating</div>
                                            @endif
                                            @if($temperatureProfile->secondary_return_temp >= 50)
                                                <div>• Domestic hot water (DHW)</div>
                                            @endif
                                            @if($temperatureProfile->primary_flow_temp <= 60)
                                                <div>• Low temperature heating</div>
                                            @endif
                                            @if($temperatureProfile->secondary_flow_temp <= 15)
                                                <div>• Cold water heating</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products and Versions Using This Profile -->
                @if($products->count() > 0)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Products Using This Profile
                                <span class="text-sm text-gray-500 font-normal">({{ $products->count() }} products)</span>
                            </h3>
                        </div>
                        <div class="kt-card-body">
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($products as $product)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-medium text-gray-900">{{ $product->name }}</h4>
                                            <span class="kt-badge kt-badge-sm kt-badge-outline">{{ $product->type }}</span>
                                        </div>
                                        @if($product->description)
                                            <div class="text-sm text-gray-600 mb-3">{{ Str::limit($product->description, 60) }}</div>
                                        @endif
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-500">
                                                {{ $versions->where('product_id', $product->id)->count() }} versions
                                            </span>
                                            <a href="{{ route('products.show', $product->id) }}" class="text-xs text-primary hover:underline">
                                                View Product
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Versions Using This Profile -->
                @if($versions->count() > 0)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Versions Using This Profile
                                <span class="text-sm text-gray-500 font-normal">({{ $versions->count() }} versions)</span>
                            </h3>
                        </div>
                        <div class="kt-card-body">
                            <div class="kt-scrollable-x-auto">
                                <table class="kt-table kt-table-border">
                                    <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Version</th>
                                        <th>Model Number</th>
                                        <th>Performance Records</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($versions as $version)
                                        <tr>
                                            <td>
                                                <div class="flex flex-col">
                                                    <span class="font-medium text-gray-900">{{ $version->product->name }}</span>
                                                    <span class="text-xs text-gray-500">{{ $version->product->type }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('versions.show', $version->id) }}" class="font-medium text-primary hover:underline">
                                                    {{ $version->name ?: 'Version ' . $version->model_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="font-mono text-sm">{{ $version->model_number }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $recordCount = $version->performanceData()->where('temperature_profile_id', $temperatureProfile->id)->count();
                                                @endphp
                                                <span class="kt-badge kt-badge-sm {{ $recordCount > 0 ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                                                        {{ $recordCount }} records
                                                    </span>
                                            </td>
                                            <td>
                                                    <span class="kt-badge kt-badge-sm {{ $version->status ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                                                        {{ $version->status ? 'Active' : 'Inactive' }}
                                                    </span>
                                            </td>
                                            <td>
                                                <div class="flex gap-1">
                                                    <a href="{{ route('versions.show', $version->id) }}" class="kt-btn kt-btn-xs kt-btn-secondary" title="View Version">
                                                        <i class="ki-filled ki-eye"></i>
                                                    </a>
                                                    @if($recordCount > 0)
                                                        <a href="{{ route('versions.performance', $version->id) }}" class="kt-btn kt-btn-xs kt-btn-info" title="Performance Data">
                                                            <i class="ki-filled ki-chart-simple"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="kt-card">
                    <div class="kt-card-body">
                        <div class="flex justify-between items-center">
                            <div class="flex gap-3">
                                <a href="{{ route('temperature-profiles.index') }}" class="kt-btn kt-btn-secondary">
                                    <i class="ki-filled ki-arrow-left"></i>
                                    Back to Profiles
                                </a>
                                <a href="{{ route('temperature-profiles.edit', $temperatureProfile->id) }}" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-pencil"></i>
                                    Edit Profile
                                </a>
                            </div>

                            @if($temperatureProfile->performance_data_count == 0)
                                <form method="POST" action="{{ route('temperature-profiles.destroy', $temperatureProfile->id) }}"
                                      class="inline" onsubmit="return confirm('Are you sure you want to delete this temperature profile? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="kt-btn kt-btn-danger">
                                        <i class="ki-filled ki-trash"></i>
                                        Delete Profile
                                    </button>
                                </form>
                            @else
                                <div class="text-sm text-gray-500">
                                    <i class="ki-filled ki-information-2"></i>
                                    Cannot delete - profile is in use
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
