@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Performance Data: ' . $version->model_number,
        'subTitle' => 'Detailed performance metrics and temperature profiles',
        'buttonText' => 'Back to Version',
        'buttonUrl' => route('versions.show', $version->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('versions.index') }}" class="hover:text-primary">Versions</a>
                <i class="ki-filled ki-right text-xs"></i>
                <a href="{{ route('versions.show', $version->id) }}" class="hover:text-primary">{{ $version->model_number }}</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">Performance Data</span>
            </div>

            <div class="space-y-6">
                <!-- Version Summary -->
                <div class="kt-card">
                    <div class="kt-card-body px-6 py-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">{{ $version->name ?: $version->product->name . ' ' . $version->model_number }}</h2>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-sm text-gray-600">{{ $version->product->name }}</span>
                                        <span class="kt-badge kt-badge-sm kt-badge-outline">{{ $version->product->type }}</span>
                                        @if($version->category)
                                            <span class="kt-badge kt-badge-sm kt-badge-info">{{ $version->category->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-primary">{{ $performanceMatrix->count() }}</div>
                                    <div class="text-xs text-gray-500">Temperature Profiles</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-info">{{ $version->performanceData->count() }}</div>
                                    <div class="text-xs text-gray-500">Performance Records</div>
                                </div>
                                @if($version->vesselConfigurations->count() > 0)
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-success">{{ $version->vesselConfigurations->count() }}</div>
                                        <div class="text-xs text-gray-500">Vessel Options</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Controls -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Filter Performance Data</h3>
                    </div>
                    <div class="kt-card-body px-6 py-6">
                        <div class="flex flex-wrap gap-4 items-end">
                            <!-- Temperature Profile Filter -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Temperature Profile</label>
                                <select id="profile-filter" class="kt-select w-64" onchange="filterData()">
                                    <option value="">All Profiles</option>
                                    @foreach($performanceMatrix as $profileId => $profileData)
                                        <option value="{{ $profileId }}">{{ $profileData['profile']->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($version->has_vessel_options)
                                <!-- Vessel Filter -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-gray-700">Vessel Configuration</label>
                                    <select id="vessel-filter" class="kt-select w-48" onchange="filterData()">
                                        <option value="">All Vessels</option>
                                        @foreach($version->vesselConfigurations as $vessel)
                                            <option value="{{ $vessel->id }}">{{ $vessel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <!-- View Mode -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">View Mode</label>
                                <select id="view-mode" class="kt-select w-32" onchange="toggleViewMode()">
                                    <option value="cards">Cards</option>
                                    <option value="table">Table</option>
                                </select>
                            </div>

                            <!-- Reset Button -->
                            <button onclick="resetFilters()" class="kt-btn kt-btn-secondary">
                                <i class="ki-filled ki-refresh"></i>
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Performance Data Display -->
                <div id="performance-data">
                    @if($performanceMatrix->count() > 0)
                        <!-- Cards View (Default) -->
                        <div id="cards-view" class="space-y-6">
                            @foreach($performanceMatrix as $profileId => $profileData)
                                <div class="kt-card profile-card" data-profile-id="{{ $profileId }}">
                                    <div class="kt-card-header">
                                        <div>
                                            <h3 class="kt-card-title">{{ $profileData['profile']->name }}</h3>
                                            <div class="text-sm text-gray-600">{{ $profileData['profile']->display_name }}</div>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $profileData['vessel_data']->count() }}
                                            {{ $version->has_vessel_options ? 'vessel configurations' : 'data point(s)' }}
                                        </div>
                                    </div>
                                    <div class="kt-card-body px-6 py-6">
                                        @if($version->has_vessel_options)
                                            <!-- Grid for vessel configurations -->
                                            <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                                @foreach($profileData['vessel_data'] as $vesselId => $vesselData)
                                                    <div class="vessel-card border border-gray-200 rounded-lg p-4"
                                                         data-vessel-id="{{ $vesselId }}">
                                                        <div class="mb-3">
                                                            <h4 class="font-medium text-gray-900">
                                                                {{ $vesselData['vessel']->name ?? 'No Vessel' }}
                                                            </h4>
                                                            @if($vesselData['vessel'] && $vesselData['vessel']->capacity)
                                                                <div class="text-sm text-gray-600">{{ $vesselData['vessel']->formatted_capacity }}</div>
                                                            @endif
                                                        </div>

                                                        @if($vesselData['performance'])
                                                            <div class="space-y-2">
                                                                <div class="flex justify-between">
                                                                    <span class="text-xs text-gray-600">Heat Input</span>
                                                                    <span class="text-xs font-medium">{{ number_format($vesselData['performance']->heat_input_kw, 1) }} kW</span>
                                                                </div>

                                                                <div class="flex justify-between">
                                                                    <span class="text-xs text-gray-600">Primary Flow</span>
                                                                    <span class="text-xs font-medium">{{ number_format($vesselData['performance']->primary_flow_rate_ls, 3) }} l/s</span>
                                                                </div>

                                                                <div class="flex justify-between">
                                                                    <span class="text-xs text-gray-600">Secondary Flow</span>
                                                                    <span class="text-xs font-medium">{{ number_format($vesselData['performance']->secondary_flow_rate_ls, 3) }} l/s</span>
                                                                </div>

                                                                <div class="flex justify-between">
                                                                    <span class="text-xs text-gray-600">Pressure Drop</span>
                                                                    <span class="text-xs font-medium">{{ number_format($vesselData['performance']->pressure_drop_kpa, 1) }} kPa</span>
                                                                </div>

                                                                @if($vesselData['performance']->is_dhw_data)
                                                                    <div class="border-t border-gray-200 pt-2 mt-2">
                                                                        <div class="text-xs font-medium text-blue-600 mb-1">DHW Performance</div>
                                                                        @if($vesselData['performance']->first_hour_dhw_supply)
                                                                            <div class="flex justify-between">
                                                                                <span class="text-xs text-gray-600">First Hour</span>
                                                                                <span class="text-xs font-medium">{{ number_format($vesselData['performance']->first_hour_dhw_supply, 0) }} L</span>
                                                                            </div>
                                                                        @endif
                                                                        @if($vesselData['performance']->subsequent_hour_dhw_supply)
                                                                            <div class="flex justify-between">
                                                                                <span class="text-xs text-gray-600">Subsequent Hour</span>
                                                                                <span class="text-xs font-medium">{{ number_format($vesselData['performance']->subsequent_hour_dhw_supply, 0) }} L</span>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="text-xs text-gray-400">No performance data</div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <!-- Single performance data point -->
                                            @php $performance = $profileData['vessel_data']->first()['performance'] ?? null @endphp
                                            @if($performance)
                                                <div class="grid md:grid-cols-3 lg:grid-cols-5 gap-4">
                                                    <div class="text-center p-3 bg-gray-50 rounded">
                                                        <div class="text-lg font-bold text-primary">{{ number_format($performance->heat_input_kw, 1) }}</div>
                                                        <div class="text-xs text-gray-600">Heat Input (kW)</div>
                                                    </div>
                                                    <div class="text-center p-3 bg-gray-50 rounded">
                                                        <div class="text-lg font-bold text-blue-600">{{ number_format($performance->primary_flow_rate_ls, 3) }}</div>
                                                        <div class="text-xs text-gray-600">Primary Flow (l/s)</div>
                                                    </div>
                                                    <div class="text-center p-3 bg-gray-50 rounded">
                                                        <div class="text-lg font-bold text-green-600">{{ number_format($performance->secondary_flow_rate_ls, 3) }}</div>
                                                        <div class="text-xs text-gray-600">Secondary Flow (l/s)</div>
                                                    </div>
                                                    <div class="text-center p-3 bg-gray-50 rounded">
                                                        <div class="text-lg font-bold text-orange-600">{{ number_format($performance->pressure_drop_kpa, 1) }}</div>
                                                        <div class="text-xs text-gray-600">Pressure Drop (kPa)</div>
                                                    </div>
                                                    <div class="text-center p-3 bg-gray-50 rounded">
                                                        <div class="text-lg font-bold text-purple-600">{{ number_format($performance->efficiency_ratio, 2) }}</div>
                                                        <div class="text-xs text-gray-600">Efficiency Ratio</div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Table View (Hidden by default) -->
                        <div id="table-view" class="kt-card" style="display: none;">
                            <div class="kt-card-body px-6 py-6">
                                <div class="kt-scrollable-x-auto">
                                    <table class="kt-table kt-table-border">
                                        <thead>
                                        <tr>
                                            <th>Temperature Profile</th>
                                            @if($version->has_vessel_options)
                                                <th>Vessel</th>
                                            @endif
                                            <th>Heat Input (kW)</th>
                                            <th>Primary Flow (l/s)</th>
                                            <th>Secondary Flow (l/s)</th>
                                            <th>Pressure Drop (kPa)</th>
                                            @if($version->performanceData->whereNotNull('first_hour_dhw_supply')->count() > 0)
                                                <th>First Hour DHW (L)</th>
                                                <th>Subsequent DHW (L)</th>
                                            @endif
                                            <th>Efficiency Ratio</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($performanceMatrix as $profileId => $profileData)
                                            @foreach($profileData['vessel_data'] as $vesselId => $vesselData)
                                                @if($vesselData['performance'])
                                                    <tr class="performance-row" data-profile-id="{{ $profileId }}" data-vessel-id="{{ $vesselData['vessel']->id ?? 'null' }}">
                                                        <td>
                                                            <div class="font-medium">{{ $profileData['profile']->name }}</div>
                                                            <div class="text-xs text-gray-500">{{ $profileData['profile']->display_name }}</div>
                                                        </td>
                                                        @if($version->has_vessel_options)
                                                            <td>{{ $vesselData['vessel']->name ?? 'N/A' }}</td>
                                                        @endif
                                                        <td>{{ number_format($vesselData['performance']->heat_input_kw, 1) }}</td>
                                                        <td>{{ number_format($vesselData['performance']->primary_flow_rate_ls, 3) }}</td>
                                                        <td>{{ number_format($vesselData['performance']->secondary_flow_rate_ls, 3) }}</td>
                                                        <td>{{ number_format($vesselData['performance']->pressure_drop_kpa, 1) }}</td>
                                                        @if($version->performanceData->whereNotNull('first_hour_dhw_supply')->count() > 0)
                                                            <td>{{ $vesselData['performance']->first_hour_dhw_supply ? number_format($vesselData['performance']->first_hour_dhw_supply, 0) : '-' }}</td>
                                                            <td>{{ $vesselData['performance']->subsequent_hour_dhw_supply ? number_format($vesselData['performance']->subsequent_hour_dhw_supply, 0) : '-' }}</td>
                                                        @endif
                                                        <td>{{ number_format($vesselData['performance']->efficiency_ratio, 2) }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- No Data State -->
                        <div class="kt-card">
                            <div class="kt-card-body px-6 py-6">
                                <div class="text-center py-12">
                                    <i class="ki-filled ki-chart-simple text-6xl text-gray-300"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mt-4">No Performance Data Available</h3>
                                    <p class="text-gray-600 mt-2">This version doesn't have any performance data yet.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('versions.edit', $version->id) }}" class="kt-btn kt-btn-primary">
                                            <i class="ki-filled ki-plus"></i>
                                            Import Performance Data
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="kt-card">
                    <div class="kt-card-body px-6 py-6">
                        <div class="flex justify-between items-center">
                            <div class="flex gap-3">
                                <a href="{{ route('versions.show', $version->id) }}" class="kt-btn kt-btn-secondary">
                                    <i class="ki-filled ki-arrow-left"></i>
                                    Back to Version
                                </a>
                                <a href="{{ route('versions.edit', $version->id) }}" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-pencil"></i>
                                    Edit Version
                                </a>
                            </div>

                            @if($version->performanceData->count() > 0)
                                <div class="flex gap-3">
                                    <button onclick="exportData()" class="kt-btn kt-btn-info">
                                        <i class="ki-filled ki-download"></i>
                                        Export Data
                                    </button>
                                    <button onclick="printData()" class="kt-btn kt-btn-secondary">
                                        <i class="ki-filled ki-printer"></i>
                                        Print
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function filterData() {
            const profileFilter = document.getElementById('profile-filter').value;
            const vesselFilter = document.getElementById('vessel-filter')?.value || '';

            // Filter profile cards
            document.querySelectorAll('.profile-card').forEach(card => {
                const profileId = card.dataset.profileId;
                const showProfile = !profileFilter || profileId === profileFilter;
                card.style.display = showProfile ? 'block' : 'none';

                if (showProfile && vesselFilter) {
                    // Filter vessel cards within this profile
                    card.querySelectorAll('.vessel-card').forEach(vesselCard => {
                        const vesselId = vesselCard.dataset.vesselId;
                        const showVessel = !vesselFilter || vesselId === vesselFilter;
                        vesselCard.style.display = showVessel ? 'block' : 'none';
                    });
                } else if (showProfile) {
                    // Show all vessel cards
                    card.querySelectorAll('.vessel-card').forEach(vesselCard => {
                        vesselCard.style.display = 'block';
                    });
                }
            });

            // Filter table rows
            document.querySelectorAll('.performance-row').forEach(row => {
                const profileId = row.dataset.profileId;
                const vesselId = row.dataset.vesselId;
                const showProfile = !profileFilter || profileId === profileFilter;
                const showVessel = !vesselFilter || vesselId === vesselFilter;
                row.style.display = (showProfile && showVessel) ? 'table-row' : 'none';
            });
        }

        function toggleViewMode() {
            const viewMode = document.getElementById('view-mode').value;
            const cardsView = document.getElementById('cards-view');
            const tableView = document.getElementById('table-view');

            if (viewMode === 'table') {
                cardsView.style.display = 'none';
                tableView.style.display = 'block';
            } else {
                cardsView.style.display = 'block';
                tableView.style.display = 'none';
            }

            console.log('View mode changed to:', viewMode);
        }

        function resetFilters() {
            document.getElementById('profile-filter').value = '';
            if (document.getElementById('vessel-filter')) {
                document.getElementById('vessel-filter').value = '';
            }
            document.getElementById('view-mode').value = 'cards';
            filterData();
            toggleViewMode();
        }

        function toggleViewMode() {
            const viewMode = document.getElementById('view-mode').value;
            const cardsView = document.getElementById('cards-view');
            const tableView = document.getElementById('table-view');

            if (viewMode === 'table') {
                cardsView.style.display = 'none';
                tableView.style.display = 'block';
            } else {
                cardsView.style.display = 'block';
                tableView.style.display = 'none';
            }
        }

        function exportData() {
            // Create CSV data
            const rows = [];
            const headers = ['Temperature Profile', 'Profile Details'];

            @if($version->has_vessel_options)
            headers.push('Vessel');
            @endif

            headers.push('Heat Input (kW)', 'Primary Flow (l/s)', 'Secondary Flow (l/s)', 'Pressure Drop (kPa)');

            @if($version->performanceData->whereNotNull('first_hour_dhw_supply')->count() > 0)
            headers.push('First Hour DHW (L)', 'Subsequent DHW (L)');
            @endif

            headers.push('Efficiency Ratio');
            rows.push(headers.join(','));

            // Add data rows
            @foreach($performanceMatrix as $profileId => $profileData)
            @foreach($profileData['vessel_data'] as $vesselId => $vesselData)
            @if($vesselData['performance'])
            const row = [
                "{{ $profileData['profile']->name }}",
                "{{ $profileData['profile']->display_name }}"
            ];

            @if($version->has_vessel_options)
            row.push("{{ $vesselData['vessel']->name ?? 'N/A' }}");
            @endif

            row.push(
                "{{ $vesselData['performance']->heat_input_kw }}",
                "{{ $vesselData['performance']->primary_flow_rate_ls }}",
                "{{ $vesselData['performance']->secondary_flow_rate_ls }}",
                "{{ $vesselData['performance']->pressure_drop_kpa }}"
            );

            @if($version->performanceData->whereNotNull('first_hour_dhw_supply')->count() > 0)
            row.push(
                "{{ $vesselData['performance']->first_hour_dhw_supply ?? '' }}",
                "{{ $vesselData['performance']->subsequent_hour_dhw_supply ?? '' }}"
            );
            @endif

            row.push("{{ $vesselData['performance']->efficiency_ratio }}");
            rows.push(row.join(','));
            @endif
            @endforeach
            @endforeach

            // Download CSV
            const csvContent = rows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'performance-data-{{ $version->model_number }}-{{ now()->format("Y-m-d") }}.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        function printData() {
            window.print();
        }

        // Debugging function - call this in browser console to check filter setup
        function debugFilters() {
            console.log('=== FILTER DEBUG ===');

            const profileFilter = document.getElementById('profile-filter');
            const vesselFilter = document.getElementById('vessel-filter');
            const viewMode = document.getElementById('view-mode');

            console.log('Profile filter element:', profileFilter);
            console.log('Profile filter value:', profileFilter?.value);
            console.log('Profile filter options:', Array.from(profileFilter?.options || []).map(o => ({value: o.value, text: o.text})));

            console.log('Vessel filter element:', vesselFilter);
            console.log('Vessel filter value:', vesselFilter?.value);
            console.log('Vessel filter options:', Array.from(vesselFilter?.options || []).map(o => ({value: o.value, text: o.text})));

            console.log('View mode element:', viewMode);
            console.log('View mode value:', viewMode?.value);

            console.log('Profile cards found:', document.querySelectorAll('.profile-card').length);
            console.log('Vessel cards found:', document.querySelectorAll('.vessel-card').length);
            console.log('Table rows found:', document.querySelectorAll('.performance-row').length);

            // Check data attributes
            document.querySelectorAll('.profile-card').forEach((card, index) => {
                console.log(`Profile card ${index}:`, {
                    profileId: card.dataset.profileId,
                    vesselCards: card.querySelectorAll('.vessel-card').length
                });
            });

            document.querySelectorAll('.vessel-card').forEach((card, index) => {
                console.log(`Vessel card ${index}:`, {
                    vesselId: card.dataset.vesselId,
                    name: card.querySelector('h4')?.textContent
                });
            });
        }
    </script>

    <style>
        @media print {
            .kt-container-fixed { max-width: none; margin: 0; }
            .kt-card { border: 1px solid #ddd; margin-bottom: 20px; }
            .kt-btn { display: none; }
            #performance-data { display: block !important; }
            #cards-view { display: block !important; }
            #table-view { display: none !important; }
        }
    </style>
@endsection
