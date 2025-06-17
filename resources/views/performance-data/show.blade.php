@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Performance Data Details',
        'subTitle' => ($performanceData->version && $performanceData->version->product)
            ? $performanceData->version->product->name . ' ' . $performanceData->version->model_number
            : 'Performance Data Record',
        'buttonText' => 'Edit Data',
        'buttonUrl' => route('performance-data.edit', $performanceData->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('performance-data.index') }}" class="hover:text-primary">Performance Data</a>
                <i class="ki-filled ki-right text-xs"></i>
                @if($performanceData->version)
                    <a href="{{ route('versions.show', $performanceData->version->id) }}" class="hover:text-primary">{{ $performanceData->version->model_number }}</a>
                    <i class="ki-filled ki-right text-xs"></i>
                @endif
                <span class="text-gray-900">Performance Details</span>
            </div>

            <div class="grid gap-5 lg:gap-7.5">
                <!-- Performance Data Overview -->
                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Performance Metrics -->
                    <div class="lg:col-span-2">
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Performance Metrics</h3>
                                <div class="flex gap-2">
                                    <a href="{{ route('performance-data.edit', $performanceData->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                        <i class="ki-filled ki-pencil"></i>
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('performance-data.destroy', $performanceData->id) }}"
                                          class="inline" onsubmit="return confirm('Are you sure you want to delete this performance data?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-danger">
                                            <i class="ki-filled ki-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <!-- Core Performance Metrics -->
                                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                                    <div class="text-center p-4 bg-primary-light border border-primary-200 rounded-lg">
                                        <div class="text-3xl font-bold text-primary">{{ number_format($performanceData->heat_input_kw, 1) }}</div>
                                        <div class="text-sm text-primary-700">Heat Input (kW)</div>
                                    </div>
                                    <div class="text-center p-4 bg-info-light border border-info-200 rounded-lg">
                                        <div class="text-3xl font-bold text-info">{{ number_format($performanceData->primary_flow_rate_ls, 3) }}</div>
                                        <div class="text-sm text-info-700">Primary Flow (l/s)</div>
                                    </div>
                                    <div class="text-center p-4 bg-success-light border border-success-200 rounded-lg">
                                        <div class="text-3xl font-bold text-success">{{ number_format($performanceData->secondary_flow_rate_ls, 3) }}</div>
                                        <div class="text-sm text-success-700">Secondary Flow (l/s)</div>
                                    </div>
                                    <div class="text-center p-4 bg-warning-light border border-warning-200 rounded-lg">
                                        <div class="text-3xl font-bold text-warning">{{ number_format($performanceData->pressure_drop_kpa, 1) }}</div>
                                        <div class="text-sm text-warning-700">Pressure Drop (kPa)</div>
                                    </div>
                                </div>

                                <!-- Efficiency Metrics -->
                                <div class="grid md:grid-cols-3 gap-6 mb-8">
                                    <div class="text-center p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                        <div class="text-2xl font-bold text-purple-600">{{ number_format($efficiencyMetrics['heat_transfer_efficiency'], 2) }}</div>
                                        <div class="text-sm text-gray-600">Heat Transfer Efficiency</div>
                                        <div class="text-xs text-gray-500">kW per L/min</div>
                                    </div>
                                    <div class="text-center p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                        <div class="text-2xl font-bold text-orange-600">{{ number_format($efficiencyMetrics['flow_ratio'], 3) }}</div>
                                        <div class="text-sm text-gray-600">Flow Ratio</div>
                                        <div class="text-xs text-gray-500">Secondary/Primary</div>
                                    </div>
                                    <div class="text-center p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                        <div class="text-2xl font-bold text-blue-600">{{ number_format($efficiencyMetrics['pressure_efficiency'], 2) }}</div>
                                        <div class="text-sm text-gray-600">Pressure Efficiency</div>
                                        <div class="text-xs text-gray-500">kW per kPa</div>
                                    </div>
                                </div>

                                <!-- DHW Performance (if applicable) -->
                                @if($performanceData->is_dhw_data)
                                    <div class="p-6 bg-blue-50 border border-blue-200 rounded-lg">
                                        <h4 class="font-medium text-blue-900 mb-4">Domestic Hot Water Performance</h4>
                                        <div class="grid md:grid-cols-2 gap-6">
                                            @if($performanceData->first_hour_dhw_supply)
                                                <div class="text-center">
                                                    <div class="text-3xl font-bold text-blue-700">{{ number_format($performanceData->first_hour_dhw_supply, 0) }}</div>
                                                    <div class="text-sm text-blue-600">First Hour DHW Supply (L)</div>
                                                </div>
                                            @endif
                                            @if($performanceData->subsequent_hour_dhw_supply)
                                                <div class="text-center">
                                                    <div class="text-3xl font-bold text-blue-700">{{ number_format($performanceData->subsequent_hour_dhw_supply, 0) }}</div>
                                                    <div class="text-sm text-blue-600">Subsequent Hour DHW Supply (L)</div>
                                                </div>
                                            @endif
                                        </div>

                                        @if($performanceData->first_hour_dhw_supply && $performanceData->subsequent_hour_dhw_supply)
                                            <div class="mt-4 pt-4 border-t border-blue-300">
                                                <div class="text-center">
                                                    <div class="text-lg font-bold text-blue-800">
                                                        {{ number_format(($performanceData->first_hour_dhw_supply - $performanceData->subsequent_hour_dhw_supply), 0) }}L
                                                    </div>
                                                    <div class="text-sm text-blue-600">Recovery Difference</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Flow Diagram -->
                                <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                                    <h5 class="font-medium text-gray-900 mb-6">Heat Exchanger Flow Analysis</h5>
                                    <div class="space-y-6">
                                        <!-- Primary Circuit -->
                                        <div>
                                            <div class="text-sm font-medium text-blue-700 mb-3">Primary Circuit (Hot Side)</div>
                                            <div class="flex items-center gap-4">
                                                <div class="flex-1 bg-blue-500 h-12 rounded flex items-center justify-center text-white font-medium">
                                                    @if($performanceData->temperatureProfile)
                                                        {{ $performanceData->temperatureProfile->primary_flow_temp }}°C
                                                    @else
                                                        Hot IN
                                                    @endif
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-sm text-blue-600 font-medium">{{ number_format($performanceData->primary_flow_rate_ls, 3) }} l/s</div>
                                                    <div class="text-2xl text-gray-400">→</div>
                                                </div>
                                                <div class="w-24 h-16 border-2 border-gray-400 rounded flex items-center justify-center font-bold text-gray-600">
                                                    <div class="text-center">
                                                        <div class="text-xs">{{ number_format($performanceData->heat_input_kw, 1) }}kW</div>
                                                        <div class="text-xs">HX</div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-2xl text-gray-400">→</div>
                                                    <div class="text-sm text-blue-600 font-medium">{{ number_format($performanceData->pressure_drop_kpa, 1) }} kPa</div>
                                                </div>
                                                <div class="flex-1 bg-blue-300 h-12 rounded flex items-center justify-center text-white font-medium">
                                                    @if($performanceData->temperatureProfile)
                                                        {{ $performanceData->temperatureProfile->primary_return_temp }}°C
                                                    @else
                                                        Hot OUT
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Heat Transfer -->
                                        <div class="flex justify-center">
                                            <div class="text-center">
                                                <div class="text-2xl text-red-500">↕</div>
                                                <div class="text-sm font-medium text-red-600">Heat Transfer</div>
                                                <div class="text-lg font-bold text-red-700">{{ number_format($performanceData->heat_input_kw, 1) }} kW</div>
                                            </div>
                                        </div>

                                        <!-- Secondary Circuit -->
                                        <div>
                                            <div class="text-sm font-medium text-green-700 mb-3">Secondary Circuit (Cold Side)</div>
                                            <div class="flex items-center gap-4">
                                                <div class="flex-1 bg-green-300 h-12 rounded flex items-center justify-center text-white font-medium">
                                                    @if($performanceData->temperatureProfile)
                                                        {{ $performanceData->temperatureProfile->secondary_flow_temp }}°C
                                                    @else
                                                        Cold IN
                                                    @endif
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-sm text-green-600 font-medium">{{ number_format($performanceData->secondary_flow_rate_ls, 3) }} l/s</div>
                                                    <div class="text-2xl text-gray-400">→</div>
                                                </div>
                                                <div class="w-24 h-16 border-2 border-gray-400 rounded flex items-center justify-center font-bold text-gray-600 bg-gray-100">
                                                    <div class="text-center">
                                                        <div class="text-xs">{{ $performanceData->version->model_number }}</div>
                                                        <div class="text-xs">HX</div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-2xl text-gray-400">→</div>
                                                    <div class="text-sm text-green-600 font-medium">Δ{{ number_format($efficiencyMetrics['flow_ratio'], 2) }}</div>
                                                </div>
                                                <div class="flex-1 bg-green-500 h-12 rounded flex items-center justify-center text-white font-medium">
                                                    @if($performanceData->temperatureProfile)
                                                        {{ $performanceData->temperatureProfile->secondary_return_temp }}°C
                                                    @else
                                                        Hot OUT
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuration Details -->
                    <div class="space-y-5">
                        <!-- Configuration Summary -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Configuration</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-4">
                                    <div>
                                        <div class="text-sm text-gray-600">Product</div>
                                        @if($performanceData->version && $performanceData->version->product)
                                            <div class="font-medium text-gray-900">{{ $performanceData->version->product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $performanceData->version->product->type }}</div>
                                        @else
                                            <div class="text-red-500">Product information missing</div>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="text-sm text-gray-600">Version</div>
                                        @if($performanceData->version)
                                            <div class="font-medium text-gray-900">{{ $performanceData->version->name ?: $performanceData->version->model_number }}</div>
                                            <div class="text-xs text-gray-500 font-mono">Model: {{ $performanceData->version->model_number }}</div>
                                            @if($performanceData->version->category)
                                                <div class="mt-1">
                                                    <span class="kt-badge kt-badge-xs kt-badge-outline">{{ $performanceData->version->category->name }}</span>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-red-500">Version information missing</div>
                                        @endif
                                    </div>

                                    @if($performanceData->temperatureProfile)
                                        <div>
                                            <div class="text-sm text-gray-600">Temperature Profile</div>
                                            <div class="font-medium text-gray-900">{{ $performanceData->temperatureProfile->name }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <div>Primary: {{ $performanceData->temperatureProfile->primary_flow_temp }}°→{{ $performanceData->temperatureProfile->primary_return_temp }}° ({{ $performanceData->temperatureProfile->primary_temp_difference }}°C ΔT)</div>
                                                <div>Secondary: {{ $performanceData->temperatureProfile->secondary_flow_temp }}°→{{ $performanceData->temperatureProfile->secondary_return_temp }}° ({{ $performanceData->temperatureProfile->secondary_temp_difference }}°C ΔT)</div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($performanceData->vesselConfiguration)
                                        <div>
                                            <div class="text-sm text-gray-600">Vessel Configuration</div>
                                            <div class="font-medium text-gray-900">{{ $performanceData->vesselConfiguration->name }}</div>
                                            @if($performanceData->vesselConfiguration->capacity)
                                                <div class="text-xs text-gray-500">{{ $performanceData->vesselConfiguration->formatted_capacity }}</div>
                                            @endif
                                        </div>
                                    @endif

                                    <div>
                                        <div class="text-sm text-gray-600">Record Created</div>
                                        <div class="font-medium text-gray-900">{{ $performanceData->created_at->format('M j, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $performanceData->created_at->format('g:i A') }}</div>
                                    </div>

                                    @if($performanceData->updated_at != $performanceData->created_at)
                                        <div>
                                            <div class="text-sm text-gray-600">Last Updated</div>
                                            <div class="font-medium text-gray-900">{{ $performanceData->updated_at->format('M j, Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $performanceData->updated_at->format('g:i A') }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Performance Rating -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Performance Rating</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-4">
                                    <!-- Efficiency Rating -->
                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-sm text-gray-600">Efficiency</span>
                                            @php
                                                $efficiency = $performanceData->efficiency_ratio;
                                                $efficiencyLevel = $efficiency > 100 ? 'High' : ($efficiency > 50 ? 'Medium' : 'Low');
                                                $efficiencyColor = $efficiency > 100 ? 'success' : ($efficiency > 50 ? 'warning' : 'danger');
                                            @endphp
                                            <span class="kt-badge kt-badge-sm kt-badge-{{ $efficiencyColor }}">{{ $efficiencyLevel }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-{{ $efficiencyColor }}-500 h-2 rounded-full" style="width: {{ min(100, ($efficiency / 150) * 100) }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ number_format($efficiency, 2) }} ratio</div>
                                    </div>

                                    <!-- Pressure Rating -->
                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-sm text-gray-600">Pressure Drop</span>
                                            @php
                                                $pressure = $performanceData->pressure_drop_kpa;
                                                $pressureLevel = $pressure < 20 ? 'Low' : ($pressure < 50 ? 'Medium' : 'High');
                                                $pressureColor = $pressure < 20 ? 'success' : ($pressure < 50 ? 'warning' : 'danger');
                                            @endphp
                                            <span class="kt-badge kt-badge-sm kt-badge-{{ $pressureColor }}">{{ $pressureLevel }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-{{ $pressureColor }}-500 h-2 rounded-full" style="width: {{ min(100, ($pressure / 100) * 100) }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ number_format($pressure, 1) }} kPa</div>
                                    </div>

                                    <!-- Overall Rating -->
                                    <div class="pt-3 border-t border-gray-200">
                                        @php
                                            $overallScore = 0;
                                            $overallScore += $efficiency > 100 ? 3 : ($efficiency > 50 ? 2 : 1);
                                            $overallScore += $pressure < 20 ? 3 : ($pressure < 50 ? 2 : 1);
                                            $overallRating = $overallScore >= 5 ? 'Excellent' : ($overallScore >= 4 ? 'Good' : 'Fair');
                                            $overallColor = $overallScore >= 5 ? 'success' : ($overallScore >= 4 ? 'info' : 'warning');
                                        @endphp
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-{{ $overallColor }}-600">{{ $overallRating }}</div>
                                            <div class="text-sm text-gray-600">Overall Performance</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Metrics -->
                        @if($performanceData->additional_metrics)
                            <div class="kt-card">
                                <div class="kt-card-header">
                                    <h3 class="kt-card-title">Additional Metrics</h3>
                                </div>
                                <div class="kt-card-body px-6 py-6">
                                    <div class="space-y-2">
                                        @foreach($performanceData->additional_metrics as $key => $value)
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">{{ $key }}:</span>
                                                <span class="text-sm font-medium">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Related Performance Data -->
                @if($relatedData->count() > 0)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Related Performance Data
                                <span class="text-sm text-gray-500 font-normal">(Same version, different conditions)</span>
                            </h3>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="kt-scrollable-x-auto">
                                <table class="kt-table kt-table-border">
                                    <thead>
                                    <tr>
                                        <th>Temperature Profile</th>
                                        <th>Vessel</th>
                                        <th>Heat Input (kW)</th>
                                        <th>Flow Rates (l/s)</th>
                                        <th>Pressure (kPa)</th>
                                        <th>Efficiency</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($relatedData as $related)
                                        <tr>
                                            <td>
                                                @if($related->temperatureProfile)
                                                    <div class="text-sm font-medium">{{ $related->temperatureProfile->name }}</div>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($related->vesselConfiguration)
                                                    <div class="text-sm">{{ $related->vesselConfiguration->name }}</div>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-center font-medium">{{ number_format($related->heat_input_kw, 1) }}</div>
                                            </td>
                                            <td>
                                                <div class="text-sm">
                                                    <div>P: {{ number_format($related->primary_flow_rate_ls, 3) }}</div>
                                                    <div>S: {{ number_format($related->secondary_flow_rate_ls, 3) }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center font-medium">{{ number_format($related->pressure_drop_kpa, 1) }}</div>
                                            </td>
                                            <td>
                                                <div class="text-center font-medium">{{ number_format($related->efficiency_ratio, 2) }}</div>
                                            </td>
                                            <td>
                                                <div class="flex gap-1">
                                                    <a href="{{ route('performance-data.show', $related->id) }}"
                                                       class="kt-btn kt-btn-xs kt-btn-secondary" title="View">
                                                        <i class="ki-filled ki-eye"></i>
                                                    </a>
                                                    <a href="{{ route('performance-data.compare') }}?ids={{ $performanceData->id }},{{ $related->id }}"
                                                       class="kt-btn kt-btn-xs kt-btn-info" title="Compare">
                                                        <i class="ki-filled ki-compare"></i>
                                                    </a>
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
                    <div class="kt-card-body px-6 py-6">
                        <div class="flex justify-between items-center">
                            <div class="flex gap-3">
                                <a href="{{ route('performance-data.index') }}" class="kt-btn kt-btn-secondary">
                                    <i class="ki-filled ki-arrow-left"></i>
                                    Back to Performance Data
                                </a>
                                <a href="{{ route('versions.show', $performanceData->version->id) }}" class="kt-btn kt-btn-light">
                                    <i class="ki-filled ki-setting-2"></i>
                                    View Version
                                </a>
                            </div>

                            <div class="flex gap-3">
                                <a href="{{ route('performance-data.edit', $performanceData->id) }}" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-pencil"></i>
                                    Edit Performance Data
                                </a>
                                <button onclick="exportSingleRecord()" class="kt-btn kt-btn-info">
                                    <i class="ki-filled ki-download"></i>
                                    Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function exportSingleRecord() {
            // Create simple export for this single record
            const data = [
                ['Product', '{{ $performanceData->version->product->name }}'],
                ['Version', '{{ $performanceData->version->model_number }}'],
                ['Temperature Profile', '{{ $performanceData->temperatureProfile->name ?? "N/A" }}'],
                ['Vessel Configuration', '{{ $performanceData->vesselConfiguration->name ?? "N/A" }}'],
                ['Heat Input (kW)', '{{ $performanceData->heat_input_kw }}'],
                ['Primary Flow Rate (l/s)', '{{ $performanceData->primary_flow_rate_ls }}'],
                ['Secondary Flow Rate (l/s)', '{{ $performanceData->secondary_flow_rate_ls }}'],
                ['Pressure Drop (kPa)', '{{ $performanceData->pressure_drop_kpa }}'],
                    @if($performanceData->first_hour_dhw_supply)
                ['First Hour DHW (L)', '{{ $performanceData->first_hour_dhw_supply }}'],
                    @endif
                    @if($performanceData->subsequent_hour_dhw_supply)
                ['Subsequent Hour DHW (L)', '{{ $performanceData->subsequent_hour_dhw_supply }}'],
                    @endif
                ['Efficiency Ratio', '{{ $performanceData->efficiency_ratio }}'],
                ['Created At', '{{ $performanceData->created_at->format("Y-m-d H:i:s") }}']
            ];

            const csvContent = data.map(row => row.join(',')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'performance-data-{{ $performanceData->id }}-{{ now()->format("Y-m-d") }}.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
    </script>
@endsection
