@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Performance Data',
        'subTitle' => 'Manage heat exchanger performance records and metrics',
        'buttonText' => 'Add Performance Data',
        'buttonUrl' => route('performance-data.create'),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <div class="grid gap-5 lg:gap-7.5">
                <!-- Statistics Cards -->
                <div class="grid md:grid-cols-4 gap-5">
                    <div class="kt-card">
                        <div class="kt-card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-primary">{{ number_format($stats['total_records']) }}</div>
                                    <div class="text-sm text-gray-600">Total Records</div>
                                </div>
                                <i class="ki-filled ki-chart-simple text-3xl text-primary"></i>
                            </div>
                        </div>
                    </div>

                    <div class="kt-card">
                        <div class="kt-card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-success">{{ $stats['unique_versions'] }}</div>
                                    <div class="text-sm text-gray-600">Unique Versions</div>
                                </div>
                                <i class="ki-filled ki-setting-2 text-3xl text-success"></i>
                            </div>
                        </div>
                    </div>

                    <div class="kt-card">
                        <div class="kt-card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-info">{{ $stats['unique_profiles'] }}</div>
                                    <div class="text-sm text-gray-600">Temperature Profiles</div>
                                </div>
                                <i class="ki-filled ki-thermometer text-3xl text-info"></i>
                            </div>
                        </div>
                    </div>

                    <div class="kt-card">
                        <div class="kt-card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-warning">{{ $stats['dhw_records'] }}</div>
                                    <div class="text-sm text-gray-600">DHW Records</div>
                                </div>
                                <i class="ki-filled ki-bucket text-3xl text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Range Cards -->
                <div class="grid md:grid-cols-2 gap-5">
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Heat Input Range</h3>
                        </div>
                        <div class="kt-card-body">
                            <div class="flex items-center justify-between">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-blue-600">{{ number_format($stats['heat_range']['min'], 1) }}</div>
                                    <div class="text-xs text-gray-500">Min (kW)</div>
                                </div>
                                <div class="text-2xl text-gray-300">→</div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-red-600">{{ number_format($stats['heat_range']['max'], 1) }}</div>
                                    <div class="text-xs text-gray-500">Max (kW)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Pressure Drop Range</h3>
                        </div>
                        <div class="kt-card-body">
                            <div class="flex items-center justify-between">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-green-600">{{ number_format($stats['pressure_range']['min'], 1) }}</div>
                                    <div class="text-xs text-gray-500">Min (kPa)</div>
                                </div>
                                <div class="text-2xl text-gray-300">→</div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-orange-600">{{ number_format($stats['pressure_range']['max'], 1) }}</div>
                                    <div class="text-xs text-gray-500">Max (kPa)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Advanced Filters</h3>
                        <button onclick="toggleFilters()" class="kt-btn kt-btn-sm kt-btn-secondary">
                            <i class="ki-filled ki-filter"></i>
                            Toggle Filters
                        </button>
                    </div>
                    <div class="kt-card-body" id="filters-section">
                        <form method="GET" action="{{ route('performance-data.index') }}" class="space-y-4">
                            <!-- Product and Version Filters -->
                            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Product</label>
                                    <select name="product_id" class="kt-select" onchange="loadVersionsForProduct(this.value)">
                                        <option value="">All Products</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Version</label>
                                    <select name="version_id" id="version-filter" class="kt-select" onchange="loadVesselConfigurations(this.value)">
                                        <option value="">All Versions</option>
                                        @foreach($versions as $version)
                                            <option value="{{ $version->id }}"
                                                    data-product-id="{{ $version->product_id }}"
                                                {{ request('version_id') == $version->id ? 'selected' : '' }}>
                                                {{ $version->product->name }} - {{ $version->model_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Temperature Profile</label>
                                    <select name="temperature_profile_id" class="kt-select">
                                        <option value="">All Profiles</option>
                                        @foreach($temperatureProfiles as $profile)
                                            <option value="{{ $profile->id }}" {{ request('temperature_profile_id') == $profile->id ? 'selected' : '' }}>
                                                {{ $profile->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700">Vessel Configuration</label>
                                    <select name="vessel_configuration_id" id="vessel-filter" class="kt-select">
                                        <option value="">All Vessels</option>
                                        @foreach($vesselConfigurations as $vessel)
                                            <option value="{{ $vessel->id }}"
                                                    data-version-id="{{ $vessel->version_id }}"
                                                {{ request('vessel_configuration_id') == $vessel->id ? 'selected' : '' }}>
                                                {{ $vessel->version->product->name }} {{ $vessel->version->model_number }} - {{ $vessel->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Performance Range Filters -->
                            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Min Heat Input (kW)</label>
                                    <input type="number" name="heat_min" value="{{ request('heat_min') }}"
                                           class="kt-input" placeholder="Min" step="0.1">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Max Heat Input (kW)</label>
                                    <input type="number" name="heat_max" value="{{ request('heat_max') }}"
                                           class="kt-input" placeholder="Max" step="0.1">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Min Pressure (kPa)</label>
                                    <input type="number" name="pressure_min" value="{{ request('pressure_min') }}"
                                           class="kt-input" placeholder="Min" step="0.1">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Max Pressure (kPa)</label>
                                    <input type="number" name="pressure_max" value="{{ request('pressure_max') }}"
                                           class="kt-input" placeholder="Max" step="0.1">
                                </div>
                            </div>

                            <!-- Additional Filters -->
                            <div class="grid md:grid-cols-3 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">DHW Data</label>
                                    <select name="has_dhw_data" class="kt-select">
                                        <option value="">All Records</option>
                                        <option value="1" {{ request('has_dhw_data') === '1' ? 'selected' : '' }}>Has DHW Data</option>
                                        <option value="0" {{ request('has_dhw_data') === '0' ? 'selected' : '' }}>No DHW Data</option>
                                    </select>
                                </div>

                                <div class="flex items-end">
                                    <button type="submit" class="kt-btn kt-btn-primary mr-2">
                                        <i class="ki-filled ki-magnifier"></i>
                                        Apply Filters
                                    </button>
                                    <a href="{{ route('performance-data.index') }}" class="kt-btn kt-btn-secondary">
                                        <i class="ki-filled ki-cross"></i>
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Search -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <form method="GET" action="{{ route('performance-data.index') }}" class="flex gap-3 max-w-md">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="kt-input flex-1" placeholder="Search by product, version, or profile...">
                                <button type="submit" class="kt-btn kt-btn-primary">Search</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Performance Data Table -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">
                            Performance Records
                            <span class="text-sm text-gray-500 font-normal">({{ $performanceData->total() }} total)</span>
                        </h3>
                        <div class="flex gap-2">
                            <a href="{{ route('performance-data.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                <i class="ki-filled ki-plus"></i>
                                Add Performance Data
                            </a>
                            <button onclick="toggleBulkActions()" class="kt-btn kt-btn-sm kt-btn-secondary">
                                <i class="ki-filled ki-setting-2"></i>
                                Bulk Actions
                            </button>
                            <a href="{{ route('performance-data.export', request()->query()) }}" class="kt-btn kt-btn-sm kt-btn-info">
                                <i class="ki-filled ki-download"></i>
                                Export
                            </a>
                            <a href="{{ route('performance-data.analytics') }}" class="kt-btn kt-btn-sm kt-btn-warning">
                                <i class="ki-filled ki-chart-line"></i>
                                Analytics
                            </a>
                        </div>
                    </div>
                    <div class="kt-card-body">
                        <!-- Bulk Actions (Hidden by default) -->
                        <div id="bulk-actions" class="mb-4 p-4 bg-gray-50 rounded border" style="display: none;">
                            <form method="POST" action="{{ route('performance-data.bulk-action') }}" onsubmit="return confirmBulkAction()">
                                @csrf
                                <div class="flex items-center gap-4">
                                    <select name="action" class="kt-select w-48" required>
                                        <option value="">Select Action</option>
                                        <option value="export">Export Selected</option>
                                        <option value="delete">Delete Selected</option>
                                    </select>
                                    <button type="submit" class="kt-btn kt-btn-primary">Apply</button>
                                    <button type="button" onclick="toggleBulkActions()" class="kt-btn kt-btn-secondary">Cancel</button>
                                    <span id="selected-count" class="text-sm text-gray-600">0 selected</span>
                                </div>
                            </form>
                        </div>

                        <div class="kt-scrollable-x-auto">
                            <table class="kt-table kt-table-border">
                                <thead>
                                <tr>
                                    <th class="w-[50px]">
                                        <input type="checkbox" id="select-all" class="kt-checkbox" onchange="toggleAllCheckboxes()">
                                    </th>
                                    <th>Product & Version</th>
                                    <th>Temperature Profile</th>
                                    <th>Vessel</th>
                                    <th>Heat Input (kW)</th>
                                    <th>Flow Rates (l/s)</th>
                                    <th>Pressure (kPa)</th>
                                    <th>DHW Data</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($performanceData as $data)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="performance_ids[]" value="{{ $data->id }}"
                                                   class="kt-checkbox performance-checkbox" onchange="updateSelectedCount()">
                                        </td>
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900">{{ $data->version->product->name }}</span>
                                                <a href="{{ route('versions.show', $data->version->id) }}"
                                                   class="text-sm text-primary hover:underline">
                                                    {{ $data->version->name ?: $data->version->model_number }}
                                                </a>
                                                <span class="text-xs text-gray-500 font-mono">{{ $data->version->model_number }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($data->temperatureProfile)
                                                <div class="flex flex-col">
                                                    <a href="{{ route('temperature-profiles.show', $data->temperatureProfile->id) }}"
                                                       class="font-medium text-primary hover:underline">
                                                        {{ $data->temperatureProfile->name }}
                                                    </a>
                                                    <span class="text-xs text-gray-500">{{ $data->temperatureProfile->display_name }}</span>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-400">No profile</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($data->vesselConfiguration)
                                                <a href="{{ route('vessel-configurations.show', $data->vesselConfiguration->id) }}"
                                                   class="text-sm text-primary hover:underline">
                                                    {{ $data->vesselConfiguration->name }}
                                                </a>
                                            @else
                                                <span class="text-sm text-gray-400">No vessel</span>
                                            @endif
                                        </td>
                                        <td>
                                                <span class="kt-badge kt-badge-lg kt-badge-primary">
                                                    {{ number_format($data->heat_input_kw, 1) }}
                                                </span>
                                        </td>
                                        <td>
                                            <div class="space-y-1 text-sm">
                                                <div>P: {{ number_format($data->primary_flow_rate_ls, 3) }}</div>
                                                <div>S: {{ number_format($data->secondary_flow_rate_ls, 3) }}</div>
                                            </div>
                                        </td>
                                        <td>
                                                <span class="kt-badge kt-badge-sm kt-badge-warning">
                                                    {{ number_format($data->pressure_drop_kpa, 1) }}
                                                </span>
                                        </td>
                                        <td>
                                            @if($data->is_dhw_data)
                                                <div class="space-y-1 text-xs">
                                                    @if($data->first_hour_dhw_supply)
                                                        <div>1st: {{ number_format($data->first_hour_dhw_supply, 0) }}L</div>
                                                    @endif
                                                    @if($data->subsequent_hour_dhw_supply)
                                                        <div>Sub: {{ number_format($data->subsequent_hour_dhw_supply, 0) }}L</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">No DHW</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <a href="{{ route('performance-data.show', $data->id) }}"
                                                   class="kt-btn kt-btn-xs kt-btn-secondary" title="View Details">
                                                    <i class="ki-filled ki-eye"></i>
                                                </a>
                                                <a href="{{ route('performance-data.edit', $data->id) }}"
                                                   class="kt-btn kt-btn-xs kt-btn-primary" title="Edit">
                                                    <i class="ki-filled ki-pencil"></i>
                                                </a>
                                                <form method="POST" action="{{ route('performance-data.destroy', $data->id) }}"
                                                      class="inline" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="kt-btn kt-btn-xs kt-btn-danger" title="Delete">
                                                        <i class="ki-filled ki-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-8">
                                            <div class="flex flex-col items-center gap-3">
                                                <i class="ki-filled ki-chart-simple text-4xl text-gray-400"></i>
                                                <div class="text-gray-500">
                                                    <p class="font-medium">No performance data found</p>
                                                    <p class="text-sm">Try adjusting your filters or add new performance data</p>
                                                </div>
                                                <a href="{{ route('performance-data.create') }}" class="kt-btn kt-btn-primary kt-btn-sm">
                                                    <i class="ki-filled ki-plus"></i>
                                                    Add Performance Data
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($performanceData->hasPages())
                            <div class="mt-6">
                                {{ $performanceData->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleFilters() {
            const filtersSection = document.getElementById('filters-section');
            filtersSection.style.display = filtersSection.style.display === 'none' ? 'block' : 'none';
        }

        function loadVersionsForProduct(productId) {
            const versionSelect = document.getElementById('version-filter');
            const vesselSelect = document.getElementById('vessel-filter');

            // Filter version options
            const versionOptions = versionSelect.querySelectorAll('option');
            versionOptions.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }

                const optionProductId = option.dataset.productId;
                option.style.display = (!productId || optionProductId === productId) ? 'block' : 'none';
            });

            // Reset version and vessel selections
            if (productId && versionSelect.value) {
                const selectedOption = versionSelect.querySelector(`option[value="${versionSelect.value}"]`);
                if (selectedOption && selectedOption.dataset.productId !== productId) {
                    versionSelect.value = '';
                    loadVesselConfigurations('');
                }
            }
        }

        function loadVesselConfigurations(versionId) {
            const vesselSelect = document.getElementById('vessel-filter');

            // Filter vessel options
            const vesselOptions = vesselSelect.querySelectorAll('option');
            vesselOptions.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }

                const optionVersionId = option.dataset.versionId;
                option.style.display = (!versionId || optionVersionId === versionId) ? 'block' : 'none';
            });

            // Reset vessel selection if it doesn't match
            if (versionId && vesselSelect.value) {
                const selectedOption = vesselSelect.querySelector(`option[value="${vesselSelect.value}"]`);
                if (selectedOption && selectedOption.dataset.versionId !== versionId) {
                    vesselSelect.value = '';
                }
            }
        }

        function toggleBulkActions() {
            const bulkActions = document.getElementById('bulk-actions');
            bulkActions.style.display = bulkActions.style.display === 'none' ? 'block' : 'none';

            if (bulkActions.style.display === 'none') {
                document.getElementById('select-all').checked = false;
                document.querySelectorAll('.performance-checkbox').forEach(cb => cb.checked = false);
                updateSelectedCount();
            }
        }

        function toggleAllCheckboxes() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.performance-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });

            updateSelectedCount();
        }

        function updateSelectedCount() {
            const selectedCheckboxes = document.querySelectorAll('.performance-checkbox:checked');
            const count = selectedCheckboxes.length;
            document.getElementById('selected-count').textContent = `${count} selected`;

            const totalCheckboxes = document.querySelectorAll('.performance-checkbox');
            const selectAllCheckbox = document.getElementById('select-all');

            if (count === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (count === totalCheckboxes.length) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
        }

        function confirmBulkAction() {
            const selectedCheckboxes = document.querySelectorAll('.performance-checkbox:checked');
            const action = document.querySelector('select[name="action"]').value;

            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one performance record.');
                return false;
            }

            if (!action) {
                alert('Please select an action.');
                return false;
            }

            const count = selectedCheckboxes.length;
            const actionText = action === 'delete' ? 'delete' : action;

            return confirm(`Are you sure you want to ${actionText} ${count} performance record(s)?`);
        }

        // Add selected IDs to bulk action form
        document.querySelector('form[action*="bulk-action"]').addEventListener('submit', function(e) {
            const selectedCheckboxes = document.querySelectorAll('.performance-checkbox:checked');

            this.querySelectorAll('input[name="performance_ids[]"]').forEach(input => input.remove());

            selectedCheckboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'performance_ids[]';
                input.value = checkbox.value;
                this.appendChild(input);
            });
        });

        // Initialize filters on page load
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.querySelector('select[name="product_id"]');
            const versionSelect = document.querySelector('select[name="version_id"]');

            if (productSelect.value) {
                loadVersionsForProduct(productSelect.value);
            }
            if (versionSelect.value) {
                loadVesselConfigurations(versionSelect.value);
            }
        });
    </script>
@endsection
