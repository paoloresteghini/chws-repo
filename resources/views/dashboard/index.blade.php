@extends('layouts.app')
@section('content')
    <!-- Dashboard Header -->

    @include('partials.toolbar', [
           'title' => 'System Dashboard',
           'subTitle' => "Last updated: Today",
           'buttonText' => 'Create Profile',
           'buttonUrl' => route('temperature-profiles.create'),
       ])



    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <div class="grid gap-5 lg:gap-7.5">

                <!-- Key Metrics -->
                <div class="grid grid-cols-2 lg:grid-cols-6 gap-5 lg:gap-7.5 h-full items-stretch">
                    <style>
                        .channel-stats-bg {
                            background-image: url('{{ asset('assets/media/images/2600x1600/bg-3.png') }}');
                        }
                        .dark .channel-stats-bg {
                            background-image: url('{{ asset('assets/media/images/2600x1600/bg-3-dark.png') }}');
                        }
                    </style>
                    <!-- Products -->
                    <div class="kt-card flex-col justify-between gap-6 h-full bg-cover rtl:bg-[left_top_-1.7rem] bg-[right_top_-1.7rem] bg-no-repeat channel-stats-bg">
                        <i class="ki-filled ki-abstract-26 text-2xl text-primary w-7 mt-4 ms-5"></i>
                        <div class="flex flex-col gap-1 pb-4 px-5">
                            <span class="text-3xl font-semibold text-mono">
                                {{ $stats['products']['total'] }}
                            </span>
                            <span class="text-sm font-normal text-secondary-foreground">
                                Products
                            </span>
                        </div>
                    </div>

                    <!-- Versions -->
                    <div class="kt-card flex-col justify-between gap-6 h-full bg-cover rtl:bg-[left_top_-1.7rem] bg-[right_top_-1.7rem] bg-no-repeat channel-stats-bg">
                        <i class="ki-filled ki-code text-2xl text-success w-7 mt-4 ms-5"></i>
                        <div class="flex flex-col gap-1 pb-4 px-5">
                            <span class="text-3xl font-semibold text-mono">
                                {{ $stats['versions']['total'] }}
                            </span>
                            <span class="text-sm font-normal text-secondary-foreground">
                                Versions
                            </span>
                        </div>
                    </div>

                    <!-- Performance Data -->
                    <div class="kt-card flex-col justify-between gap-6 h-full bg-cover rtl:bg-[left_top_-1.7rem] bg-[right_top_-1.7rem] bg-no-repeat channel-stats-bg">
                        <i class="ki-filled ki-chart-simple text-2xl text-info w-7 mt-4 ms-5"></i>
                        <div class="flex flex-col gap-1 pb-4 px-5">
                            <span class="text-3xl font-semibold text-mono">
                                {{ number_format($stats['performance_data']['total']) }}
                            </span>
                            <span class="text-sm font-normal text-secondary-foreground">
                                Performance Records
                            </span>
                        </div>
                    </div>

                    <!-- Temperature Profiles -->
                    <div class="kt-card flex-col justify-between gap-6 h-full bg-cover rtl:bg-[left_top_-1.7rem] bg-[right_top_-1.7rem] bg-no-repeat channel-stats-bg">
                        <i class="ki-filled ki-thermometer text-2xl text-warning w-7 mt-4 ms-5"></i>
                        <div class="flex flex-col gap-1 pb-4 px-5">
                            <span class="text-3xl font-semibold text-mono">
                                {{ $stats['temperature_profiles']['total'] }}
                            </span>
                            <span class="text-sm font-normal text-secondary-foreground">
                                Temperature Profiles
                            </span>
                        </div>
                    </div>

                    <!-- Version Categories -->
                    <div class="kt-card flex-col justify-between gap-6 h-full bg-cover rtl:bg-[left_top_-1.7rem] bg-[right_top_-1.7rem] bg-no-repeat channel-stats-bg">
                        <i class="ki-filled ki-category text-2xl text-purple-600 w-7 mt-4 ms-5"></i>
                        <div class="flex flex-col gap-1 pb-4 px-5">
                            <span class="text-3xl font-semibold text-mono">
                                {{ $stats['categories']['total'] }}
                            </span>
                            <span class="text-sm font-normal text-secondary-foreground">
                                Version Categories
                            </span>
                        </div>
                    </div>

                    <!-- Vessel Configurations -->
                    <div class="kt-card flex-col justify-between gap-6 h-full bg-cover rtl:bg-[left_top_-1.7rem] bg-[right_top_-1.7rem] bg-no-repeat channel-stats-bg">
                        <i class="ki-filled ki-design-1 text-2xl text-orange-600 w-7 mt-4 ms-5"></i>
                        <div class="flex flex-col gap-1 pb-4 px-5">
                            <span class="text-3xl font-semibold text-mono">
                                {{ $stats['vessel_configurations']['total'] }}
                            </span>
                            <span class="text-sm font-normal text-secondary-foreground">
                                Vessel Configurations
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Charts and Data Visualization -->
                <div class="grid lg:grid-cols-2 gap-5 lg:gap-7.5">
                    <!-- Versions by Product -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Versions by Product</h3>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="flex items-center justify-center" style="height: 300px;">
                                <canvas id="versionsByProductChart"></canvas>
                            </div>
                            <div class="mt-6 space-y-2">
                                @foreach($stats['versions']['by_product'] as $product => $count)
                                    @php
                                        $percentage = $stats['versions']['total'] > 0 ? round(($count / $stats['versions']['total']) * 100) : 0;
                                    @endphp
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">{{ $product }}</span>
                                        <span class="text-sm text-gray-600">{{ $count }} ({{ number_format($percentage, 1) }}%)</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Performance Data by Product -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Performance Data Distribution</h3>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="flex items-center justify-center" style="height: 300px;">
                                <canvas id="performanceDataChart"></canvas>
                            </div>
                            <div class="mt-6 space-y-2">
                                @foreach($stats['performance_data']['by_product'] as $product => $count)
                                    @php
                                        $percentage = $stats['performance_data']['total'] > 0 ? round(($count / $stats['performance_data']['total']) * 100) : 0;
                                    @endphp
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">{{ $product }}</span>
                                        <span class="text-sm text-gray-600">{{ number_format($count) }} ({{ number_format($percentage, 1) }}%)</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health and Data Quality -->
                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Data Completeness -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Data Completeness</h3>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="space-y-4">
                                <!-- Versions with Performance Data -->
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-gray-600">Versions with Performance Data</span>
                                        <span class="kt-badge kt-badge-sm kt-badge-{{ $systemHealth['data_completeness']['versions_with_performance']['status'] === 'excellent' ? 'success' : ($systemHealth['data_completeness']['versions_with_performance']['status'] === 'good' ? 'info' : 'warning') }}">
                                            {{ $systemHealth['data_completeness']['versions_with_performance']['percentage'] }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-{{ $systemHealth['data_completeness']['versions_with_performance']['status'] === 'excellent' ? 'success' : ($systemHealth['data_completeness']['versions_with_performance']['status'] === 'good' ? 'info' : 'warning') }} h-2 rounded-full"
                                             style="width: {{ $systemHealth['data_completeness']['versions_with_performance']['percentage'] }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $systemHealth['data_completeness']['versions_with_performance']['count'] }} of {{ $systemHealth['data_completeness']['versions_with_performance']['total'] }} versions
                                    </div>
                                </div>

                                <!-- Profile Utilization -->
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-gray-600">Temperature Profile Usage</span>
                                        <span class="kt-badge kt-badge-sm kt-badge-{{ $systemHealth['data_completeness']['profiles_utilization']['status'] === 'excellent' ? 'success' : ($systemHealth['data_completeness']['profiles_utilization']['status'] === 'good' ? 'info' : 'warning') }}">
                                            {{ $systemHealth['data_completeness']['profiles_utilization']['percentage'] }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-{{ $systemHealth['data_completeness']['profiles_utilization']['status'] === 'excellent' ? 'success' : ($systemHealth['data_completeness']['profiles_utilization']['status'] === 'good' ? 'info' : 'warning') }} h-2 rounded-full"
                                             style="width: {{ $systemHealth['data_completeness']['profiles_utilization']['percentage'] }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $systemHealth['data_completeness']['profiles_utilization']['count'] }} of {{ $systemHealth['data_completeness']['profiles_utilization']['total'] }} profiles
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Quality Issues -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Data Quality</h3>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="space-y-3">
                                @if($systemHealth['data_consistency']['uncategorized_versions'] > 0)
                                    <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded">
                                        <div class="flex items-center gap-2">
                                            <i class="ki-filled ki-warning text-yellow-600"></i>
                                            <span class="text-sm text-yellow-800">Uncategorized Versions</span>
                                        </div>
                                        <span class="kt-badge kt-badge-sm kt-badge-warning">{{ $systemHealth['data_consistency']['uncategorized_versions'] }}</span>
                                    </div>
                                @endif

                                @if($systemHealth['data_consistency']['unused_profiles'] > 0)
                                    <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded">
                                        <div class="flex items-center gap-2">
                                            <i class="ki-filled ki-information text-blue-600"></i>
                                            <span class="text-sm text-blue-800">Unused Profiles</span>
                                        </div>
                                        <span class="kt-badge kt-badge-sm kt-badge-info">{{ $systemHealth['data_consistency']['unused_profiles'] }}</span>
                                    </div>
                                @endif

                                @if($systemHealth['data_consistency']['inactive_versions'] > 0)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded">
                                        <div class="flex items-center gap-2">
                                            <i class="ki-filled ki-cross-circle text-gray-600"></i>
                                            <span class="text-sm text-gray-800">Inactive Versions</span>
                                        </div>
                                        <span class="kt-badge kt-badge-sm kt-badge-secondary">{{ $systemHealth['data_consistency']['inactive_versions'] }}</span>
                                    </div>
                                @endif

                                @if($systemHealth['data_consistency']['uncategorized_versions'] == 0 &&
                                    $systemHealth['data_consistency']['unused_profiles'] == 0 &&
                                    $systemHealth['data_consistency']['inactive_versions'] == 0)
                                    <div class="flex items-center justify-center p-6">
                                        <div class="text-center">
                                            <i class="ki-filled ki-check-circle text-4xl text-success"></i>
                                            <div class="text-sm text-gray-600 mt-2">All data looks good!</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Recent Activity</h3>
                            <div class="text-sm text-gray-500">{{ $systemHealth['recent_additions']['versions_last_week'] }} versions this week</div>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="space-y-3">
                                @forelse(array_slice($recentActivity, 0, 5) as $activity)
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-{{ $activity['type'] === 'version_created' ? 'primary' : ($activity['type'] === 'performance_added' ? 'success' : 'info') }}-light flex items-center justify-center flex-shrink-0">
                                            <i class="ki-filled ki-{{ $activity['type'] === 'version_created' ? 'plus' : ($activity['type'] === 'performance_added' ? 'chart-simple' : 'category') }} text-{{ $activity['type'] === 'version_created' ? 'primary' : ($activity['type'] === 'performance_added' ? 'success' : 'info') }}"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <a href="{{ $activity['url'] }}" class="text-sm font-medium text-gray-900 hover:text-primary">
                                                {{ $activity['title'] }}
                                            </a>
                                            <div class="text-xs text-gray-500">{{ $activity['subtitle'] }}</div>
                                            <div class="text-xs text-gray-400">{{ $activity['time']->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <div class="text-sm text-gray-500">No recent activity</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Temperature and Performance Ranges -->
                <div class="grid lg:grid-cols-2 gap-5 lg:gap-7.5">
                    <!-- Temperature Ranges -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Temperature Ranges</h3>
                            <div class="text-sm text-gray-500">System-wide temperature coverage</div>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center p-4 bg-red-50 rounded-lg">
                                        <div class="text-2xl font-bold text-red-600">
                                            {{ $stats['temperature_profiles']['temp_range']['primary_min'] }}° - {{ $stats['temperature_profiles']['temp_range']['primary_max'] }}°
                                        </div>
                                        <div class="text-sm text-red-700">Primary Flow Range</div>
                                    </div>
                                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                                        <div class="text-2xl font-bold text-blue-600">
                                            {{ $stats['temperature_profiles']['temp_range']['secondary_min'] }}° - {{ $stats['temperature_profiles']['temp_range']['secondary_max'] }}°
                                        </div>
                                        <div class="text-sm text-blue-700">Secondary Flow Range</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Ranges -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Performance Ranges</h3>
                            <div class="text-sm text-gray-500">Heat input and pressure specifications</div>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center p-4 bg-orange-50 rounded-lg">
                                        <div class="text-2xl font-bold text-orange-600">
                                            {{ number_format($stats['performance_data']['heat_range']['min'], 0) }} - {{ number_format($stats['performance_data']['heat_range']['max'], 0) }}
                                        </div>
                                        <div class="text-sm text-orange-700">Heat Input (kW)</div>
                                        <div class="text-xs text-orange-600">Avg: {{ $stats['performance_data']['heat_range']['avg'] }} kW</div>
                                    </div>
                                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                                        <div class="text-2xl font-bold text-purple-600">
                                            {{ number_format($stats['performance_data']['pressure_range']['min'], 0) }} - {{ number_format($stats['performance_data']['pressure_range']['max'], 0) }}
                                        </div>
                                        <div class="text-sm text-purple-700">Pressure Drop (kPa)</div>
                                        <div class="text-xs text-purple-600">Avg: {{ $stats['performance_data']['pressure_range']['avg'] }} kPa</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Versions by Product Pie Chart
    const versionsByProductCtx = document.getElementById('versionsByProductChart').getContext('2d');
    const versionsByProductData = {
        labels: {!! json_encode(array_keys($stats['versions']['by_product'])) !!},
        datasets: [{
            data: {!! json_encode(array_values($stats['versions']['by_product'])) !!},
            backgroundColor: [
                '#3B82F6', // Blue
                '#10B981', // Green
                '#F59E0B', // Amber
                '#EF4444', // Red
                '#8B5CF6', // Purple
                '#EC4899', // Pink
                '#14B8A6', // Teal
                '#F97316'  // Orange
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };
    
    new Chart(versionsByProductCtx, {
        type: 'pie',
        data: versionsByProductData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            label += value + ' (' + percentage + '%)';
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Performance Data Distribution Pie Chart
    const performanceDataCtx = document.getElementById('performanceDataChart').getContext('2d');
    const performanceDataData = {
        labels: {!! json_encode(array_keys($stats['performance_data']['by_product'])) !!},
        datasets: [{
            data: {!! json_encode(array_values($stats['performance_data']['by_product'])) !!},
            backgroundColor: [
                '#10B981', // Green
                '#3B82F6', // Blue
                '#F59E0B', // Amber
                '#EF4444', // Red
                '#8B5CF6', // Purple
                '#EC4899', // Pink
                '#14B8A6', // Teal
                '#F97316'  // Orange
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    };
    
    new Chart(performanceDataCtx, {
        type: 'pie',
        data: performanceDataData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            label += value + ' (' + percentage + '%)';
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
