@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Vessel Configurations',
        'subTitle' => 'Manage vessel sizes and capacities for heat exchanger systems',
        'buttonText' => 'Create Configuration',
        'buttonUrl' => route('vessel-configurations.create'),
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
                                    <div class="text-2xl font-bold text-primary">{{ $stats['total_configurations'] }}</div>
                                    <div class="text-sm text-gray-600">Total Configurations</div>
                                </div>
                                <i class="ki-filled ki-bucket text-3xl text-primary"></i>
                            </div>
                        </div>
                    </div>

                    <div class="kt-card">
                        <div class="kt-card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-success">{{ $stats['used_configurations'] }}</div>
                                    <div class="text-sm text-gray-600">In Use</div>
                                </div>
                                <i class="ki-filled ki-chart-simple text-3xl text-success"></i>
                            </div>
                        </div>
                    </div>

                    <div class="kt-card">
                        <div class="kt-card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-info">{{ $stats['products_with_vessels'] }}</div>
                                    <div class="text-sm text-gray-600">Products with Vessels</div>
                                </div>
                                <i class="ki-filled ki-package text-3xl text-info"></i>
                            </div>
                        </div>
                    </div>

                    <div class="kt-card">
                        <div class="kt-card-body">
                            <div>
                                <div class="text-sm text-gray-600 mb-2">Capacity Range</div>
                                @if($stats['capacity_range']['min'] && $stats['capacity_range']['max'])
                                    <div class="text-lg font-bold text-warning">
                                        {{ number_format($stats['capacity_range']['min'], 0) }} - {{ number_format($stats['capacity_range']['max'], 0) }}L
                                    </div>
                                @else
                                    <div class="text-sm text-gray-400">No data</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Filters</h3>
                    </div>
                    <div class="kt-card-body">
                        <form method="GET" action="{{ route('vessel-configurations.index') }}" class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 items-end">
                            <!-- Product Filter -->
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

                            <!-- Version Filter -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Version</label>
                                <select name="version_id" id="version-filter" class="kt-select">
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

                            <!-- Capacity Range -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Min Capacity</label>
                                <input type="number" name="capacity_min" value="{{ request('capacity_min') }}"
                                       class="kt-input" placeholder="Min" step="1">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Max Capacity</label>
                                <input type="number" name="capacity_max" value="{{ request('capacity_max') }}"
                                       class="kt-input" placeholder="Max" step="1">
                            </div>

                            <!-- Capacity Unit -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Unit</label>
                                <select name="capacity_unit" class="kt-select">
                                    <option value="">All Units</option>
                                    @foreach($capacityUnits as $unit)
                                        <option value="{{ $unit }}" {{ request('capacity_unit') == $unit ? 'selected' : '' }}>
                                            {{ $unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button type="submit" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-magnifier"></i>
                                    Filter
                                </button>
                                <a href="{{ route('vessel-configurations.index') }}" class="kt-btn kt-btn-secondary">
                                    <i class="ki-filled ki-cross"></i>
                                    Reset
                                </a>
                            </div>
                        </form>

                        <!-- Search -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <form method="GET" action="{{ route('vessel-configurations.index') }}" class="flex gap-3 max-w-md">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="kt-input flex-1" placeholder="Search configurations...">
                                <button type="submit" class="kt-btn kt-btn-primary">Search</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Vessel Configurations Table -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">
                            Vessel Configurations
                            <span class="text-sm text-gray-500 font-normal">({{ $configurations->total() }} total)</span>
                        </h3>
                        <div class="flex gap-2">
                            <a href="{{ route('vessel-configurations.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                <i class="ki-filled ki-plus"></i>
                                Create Configuration
                            </a>
                            <button onclick="toggleBulkActions()" class="kt-btn kt-btn-sm kt-btn-secondary">
                                <i class="ki-filled ki-setting-2"></i>
                                Bulk Actions
                            </button>
                            <a href="{{ route('vessel-configurations.export', request()->query()) }}" class="kt-btn kt-btn-sm kt-btn-info">
                                <i class="ki-filled ki-download"></i>
                                Export
                            </a>
                        </div>
                    </div>
                    <div class="kt-card-body">
                        <!-- Bulk Actions (Hidden by default) -->
                        <div id="bulk-actions" class="mb-4 p-4 bg-gray-50 rounded border" style="display: none;">
                            <form method="POST" action="{{ route('vessel-configurations.bulk-action') }}" onsubmit="return confirmBulkAction()">
                                @csrf
                                <div class="flex items-center gap-4">
                                    <select name="action" class="kt-select w-48" required>
                                        <option value="">Select Action</option>
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
                                    <th>Product</th>
                                    <th>Version</th>
                                    <th>Configuration Name</th>
                                    <th>Capacity</th>
                                    <th>Performance Records</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($configurations as $config)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="configuration_ids[]" value="{{ $config->id }}"
                                                   class="kt-checkbox configuration-checkbox" onchange="updateSelectedCount()">
                                        </td>
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900">{{ $config->version->product->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $config->version->product->type }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex flex-col">
                                                <a href="{{ route('versions.show', $config->version->id) }}"
                                                   class="font-medium text-primary hover:underline">
                                                    {{ $config->version->name ?: $config->version->model_number }}
                                                </a>
                                                <span class="text-xs text-gray-500 font-mono">{{ $config->version->model_number }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex flex-col">
                                                <a href="{{ route('vessel-configurations.show', $config->id) }}"
                                                   class="font-medium text-gray-900 hover:text-primary">
                                                    {{ $config->name }}
                                                </a>
                                                @if($config->description)
                                                    <span class="text-xs text-gray-500">{{ Str::limit($config->description, 40) }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                @if($config->capacity)
                                                    <span class="kt-badge kt-badge-lg kt-badge-info">
                                                            {{ number_format($config->capacity, 0) }}{{ $config->capacity_unit }}
                                                        </span>
                                                @else
                                                    <span class="text-sm text-gray-400">{{ $config->capacity_unit }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                @if($config->performance_data_count > 0)
                                                    <span class="kt-badge kt-badge-sm kt-badge-success">
                                                            {{ $config->performance_data_count }} records
                                                        </span>
                                                @else
                                                    <span class="kt-badge kt-badge-sm kt-badge-secondary">Not used</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <a href="{{ route('vessel-configurations.show', $config->id) }}"
                                                   class="kt-btn kt-btn-xs kt-btn-secondary" title="View Details">
                                                    <i class="ki-filled ki-eye"></i>
                                                </a>
                                                <a href="{{ route('vessel-configurations.edit', $config->id) }}"
                                                   class="kt-btn kt-btn-xs kt-btn-primary" title="Edit">
                                                    <i class="ki-filled ki-pencil"></i>
                                                </a>
                                                @if($config->performance_data_count == 0)
                                                    <form method="POST" action="{{ route('vessel-configurations.destroy', $config->id) }}"
                                                          class="inline" onsubmit="return confirm('Are you sure?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="kt-btn kt-btn-xs kt-btn-danger" title="Delete">
                                                            <i class="ki-filled ki-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-8">
                                            <div class="flex flex-col items-center gap-3">
                                                <i class="ki-filled ki-bucket text-4xl text-gray-400"></i>
                                                <div class="text-gray-500">
                                                    <p class="font-medium">No vessel configurations found</p>
                                                    <p class="text-sm">Try adjusting your filters or create a new configuration</p>
                                                </div>
                                                <a href="{{ route('vessel-configurations.create') }}" class="kt-btn kt-btn-primary kt-btn-sm">
                                                    <i class="ki-filled ki-plus"></i>
                                                    Create Vessel Configuration
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($configurations->hasPages())
                            <div class="mt-6">
                                {{ $configurations->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function loadVersionsForProduct(productId) {
            const versionSelect = document.getElementById('version-filter');
            const allOptions = versionSelect.querySelectorAll('option');

            // Show all options first
            allOptions.forEach(option => {
                option.style.display = 'block';
            });

            // If a product is selected, hide non-matching versions
            if (productId) {
                allOptions.forEach(option => {
                    if (option.value && option.dataset.productId != productId) {
                        option.style.display = 'none';
                    }
                });

                // Reset version selection if it doesn't belong to selected product
                const currentVersion = versionSelect.value;
                if (currentVersion) {
                    const currentOption = versionSelect.querySelector(`option[value="${currentVersion}"]`);
                    if (currentOption && currentOption.dataset.productId != productId) {
                        versionSelect.value = '';
                    }
                }
            }
        }

        function toggleBulkActions() {
            const bulkActions = document.getElementById('bulk-actions');
            bulkActions.style.display = bulkActions.style.display === 'none' ? 'block' : 'none';

            if (bulkActions.style.display === 'none') {
                // Reset checkboxes when hiding bulk actions
                document.getElementById('select-all').checked = false;
                document.querySelectorAll('.configuration-checkbox').forEach(cb => cb.checked = false);
                updateSelectedCount();
            }
        }

        function toggleAllCheckboxes() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.configuration-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });

            updateSelectedCount();
        }

        function updateSelectedCount() {
            const selectedCheckboxes = document.querySelectorAll('.configuration-checkbox:checked');
            const count = selectedCheckboxes.length;
            document.getElementById('selected-count').textContent = `${count} selected`;

            // Update select all checkbox state
            const totalCheckboxes = document.querySelectorAll('.configuration-checkbox');
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
            const selectedCheckboxes = document.querySelectorAll('.configuration-checkbox:checked');
            const action = document.querySelector('select[name="action"]').value;

            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one vessel configuration.');
                return false;
            }

            if (!action) {
                alert('Please select an action.');
                return false;
            }

            const count = selectedCheckboxes.length;

            return confirm(`Are you sure you want to ${action} ${count} vessel configuration(s)?`);
        }

        // Add selected configuration IDs to bulk action form
        document.querySelector('form[action*="bulk-action"]').addEventListener('submit', function(e) {
            const selectedCheckboxes = document.querySelectorAll('.configuration-checkbox:checked');

            // Remove existing hidden inputs
            this.querySelectorAll('input[name="configuration_ids[]"]').forEach(input => input.remove());

            // Add selected IDs as hidden inputs
            selectedCheckboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'configuration_ids[]';
                input.value = checkbox.value;
                this.appendChild(input);
            });
        });

        // Initialize product filter on page load
        document.addEventListener('DOMContentLoaded', function() {
            const productSelect = document.querySelector('select[name="product_id"]');
            if (productSelect.value) {
                loadVersionsForProduct(productSelect.value);
            }
        });
    </script>
@endsection
