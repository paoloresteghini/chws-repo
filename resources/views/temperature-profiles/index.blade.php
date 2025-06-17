@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Temperature Profiles',
        'subTitle' => 'Manage flow and return temperature configurations',
        'buttonText' => 'Create Profile',
        'buttonUrl' => route('temperature-profiles.create'),
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
                                    <div class="text-2xl font-bold text-primary">{{ $stats['total_profiles'] }}</div>
                                    <div class="text-sm text-gray-600">Total Profiles</div>
                                </div>
                                <i class="ki-filled ki-thermometer text-3xl text-primary"></i>
                            </div>
                        </div>
                    </div>

                    <div class="kt-card">
                        <div class="kt-card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-success">{{ $stats['active_profiles'] }}</div>
                                    <div class="text-sm text-gray-600">Active Profiles</div>
                                </div>
                                <i class="ki-filled ki-check-circle text-3xl text-success"></i>
                            </div>
                        </div>
                    </div>

                    <div class="kt-card">
                        <div class="kt-card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-2xl font-bold text-info">{{ $stats['used_profiles'] }}</div>
                                    <div class="text-sm text-gray-600">In Use</div>
                                </div>
                                <i class="ki-filled ki-chart-simple text-3xl text-info"></i>
                            </div>
                        </div>
                    </div>

                    <div class="kt-card">
                        <div class="kt-card-body">
                            <div>
                                <div class="text-sm text-gray-600 mb-2">Temperature Range</div>
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span>Primary:</span>
                                        <span class="font-medium">{{ $stats['temp_range']['primary_min'] }}° - {{ $stats['temp_range']['primary_max'] }}°</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span>Secondary:</span>
                                        <span class="font-medium">{{ $stats['temp_range']['secondary_min'] }}° - {{ $stats['temp_range']['secondary_max'] }}°</span>
                                    </div>
                                </div>
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
                        <form method="GET" action="{{ route('temperature-profiles.index') }}" class="grid md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 items-end">
                            <!-- Primary Temperature Range -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Primary Min (°C)</label>
                                <input type="number" name="primary_temp_min" value="{{ request('primary_temp_min') }}"
                                       class="kt-input" placeholder="Min">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Primary Max (°C)</label>
                                <input type="number" name="primary_temp_max" value="{{ request('primary_temp_max') }}"
                                       class="kt-input" placeholder="Max">
                            </div>

                            <!-- Secondary Temperature Range -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Secondary Min (°C)</label>
                                <input type="number" name="secondary_temp_min" value="{{ request('secondary_temp_min') }}"
                                       class="kt-input" placeholder="Min">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Secondary Max (°C)</label>
                                <input type="number" name="secondary_temp_max" value="{{ request('secondary_temp_max') }}"
                                       class="kt-input" placeholder="Max">
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Status</label>
                                <select name="status" class="kt-select">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button type="submit" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-magnifier"></i>
                                    Filter
                                </button>
                                <a href="{{ route('temperature-profiles.index') }}" class="kt-btn kt-btn-secondary">
                                    <i class="ki-filled ki-cross"></i>
                                    Reset
                                </a>
                            </div>
                        </form>

                        <!-- Search -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <form method="GET" action="{{ route('temperature-profiles.index') }}" class="flex gap-3 max-w-md">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="kt-input flex-1" placeholder="Search profiles...">
                                <button type="submit" class="kt-btn kt-btn-primary">Search</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Temperature Profiles Table -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">
                            Temperature Profiles
                            <span class="text-sm text-gray-500 font-normal">({{ $profiles->total() }} total)</span>
                        </h3>
                        <div class="flex gap-2">
                            <a href="{{ route('temperature-profiles.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                <i class="ki-filled ki-plus"></i>
                                Create Profile
                            </a>
                            <button onclick="toggleBulkActions()" class="kt-btn kt-btn-sm kt-btn-secondary">
                                <i class="ki-filled ki-setting-2"></i>
                                Bulk Actions
                            </button>
                        </div>
                    </div>
                    <div class="kt-card-body">
                        <!-- Bulk Actions (Hidden by default) -->
                        <div id="bulk-actions" class="mb-4 p-4 bg-gray-50 rounded border" style="display: none;">
                            <form method="POST" action="{{ route('temperature-profiles.bulk-action') }}" onsubmit="return confirmBulkAction()">
                                @csrf
                                <div class="flex items-center gap-4">
                                    <select name="action" class="kt-select w-48" required>
                                        <option value="">Select Action</option>
                                        <option value="activate">Activate Selected</option>
                                        <option value="deactivate">Deactivate Selected</option>
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
                                    <th>Name</th>
                                    <th>Primary Temperatures</th>
                                    <th>Secondary Temperatures</th>
                                    <th>Temperature Differences</th>
                                    <th>Usage</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($profiles as $profile)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="profile_ids[]" value="{{ $profile->id }}"
                                                   class="kt-checkbox profile-checkbox" onchange="updateSelectedCount()">
                                        </td>
                                        <td>
                                            <div class="flex flex-col">
                                                <a href="{{ route('temperature-profiles.show', $profile->id) }}"
                                                   class="font-medium text-gray-900 hover:text-primary">
                                                    {{ $profile->name }}
                                                </a>
                                                @if($profile->description)
                                                    <span class="text-xs text-gray-500">{{ Str::limit($profile->description, 40) }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <span class="kt-badge kt-badge-sm kt-badge-info">{{ $profile->primary_flow_temp }}°</span>
                                                <i class="ki-filled ki-arrow-right text-xs text-gray-400"></i>
                                                <span class="kt-badge kt-badge-sm kt-badge-secondary">{{ $profile->primary_return_temp }}°</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <span class="kt-badge kt-badge-sm kt-badge-success">{{ $profile->secondary_flow_temp }}°</span>
                                                <i class="ki-filled ki-arrow-right text-xs text-gray-400"></i>
                                                <span class="kt-badge kt-badge-sm kt-badge-warning">{{ $profile->secondary_return_temp }}°</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-sm space-y-1">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Primary ΔT:</span>
                                                    <span class="font-medium">{{ $profile->primary_temp_difference }}°</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600">Secondary ΔT:</span>
                                                    <span class="font-medium">{{ $profile->secondary_temp_difference }}°</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                @if($profile->performance_data_count > 0)
                                                    <span class="kt-badge kt-badge-sm kt-badge-success">
                                                            {{ $profile->performance_data_count }} records
                                                        </span>
                                                @else
                                                    <span class="kt-badge kt-badge-sm kt-badge-secondary">Not used</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($profile->is_active)
                                                <span class="kt-badge kt-badge-sm kt-badge-success">Active</span>
                                            @else
                                                <span class="kt-badge kt-badge-sm kt-badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <a href="{{ route('temperature-profiles.show', $profile->id) }}"
                                                   class="kt-btn kt-btn-xs kt-btn-secondary" title="View Details">
                                                    <i class="ki-filled ki-eye"></i>
                                                </a>
                                                <a href="{{ route('temperature-profiles.edit', $profile->id) }}"
                                                   class="kt-btn kt-btn-xs kt-btn-primary" title="Edit">
                                                    <i class="ki-filled ki-pencil"></i>
                                                </a>
                                                @if($profile->performance_data_count == 0)
                                                    <form method="POST" action="{{ route('temperature-profiles.destroy', $profile->id) }}"
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
                                        <td colspan="8" class="text-center py-8">
                                            <div class="flex flex-col items-center gap-3">
                                                <i class="ki-filled ki-thermometer text-4xl text-gray-400"></i>
                                                <div class="text-gray-500">
                                                    <p class="font-medium">No temperature profiles found</p>
                                                    <p class="text-sm">Try adjusting your filters or create a new profile</p>
                                                </div>
                                                <a href="{{ route('temperature-profiles.create') }}" class="kt-btn kt-btn-primary kt-btn-sm">
                                                    <i class="ki-filled ki-plus"></i>
                                                    Create Temperature Profile
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($profiles->hasPages())
                            <div class="mt-6">
                                {{ $profiles->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleBulkActions() {
            const bulkActions = document.getElementById('bulk-actions');
            bulkActions.style.display = bulkActions.style.display === 'none' ? 'block' : 'none';

            if (bulkActions.style.display === 'none') {
                // Reset checkboxes when hiding bulk actions
                document.getElementById('select-all').checked = false;
                document.querySelectorAll('.profile-checkbox').forEach(cb => cb.checked = false);
                updateSelectedCount();
            }
        }

        function toggleAllCheckboxes() {
            const selectAll = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.profile-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });

            updateSelectedCount();
        }

        function updateSelectedCount() {
            const selectedCheckboxes = document.querySelectorAll('.profile-checkbox:checked');
            const count = selectedCheckboxes.length;
            document.getElementById('selected-count').textContent = `${count} selected`;

            // Update select all checkbox state
            const totalCheckboxes = document.querySelectorAll('.profile-checkbox');
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
            const selectedCheckboxes = document.querySelectorAll('.profile-checkbox:checked');
            const action = document.querySelector('select[name="action"]').value;

            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one temperature profile.');
                return false;
            }

            if (!action) {
                alert('Please select an action.');
                return false;
            }

            const count = selectedCheckboxes.length;
            const actionText = action === 'delete' ? 'delete' : action;

            return confirm(`Are you sure you want to ${actionText} ${count} temperature profile(s)?`);
        }

        // Add selected profile IDs to bulk action form
        document.querySelector('form[action*="bulk-action"]').addEventListener('submit', function(e) {
            const selectedCheckboxes = document.querySelectorAll('.profile-checkbox:checked');

            // Remove existing hidden inputs
            this.querySelectorAll('input[name="profile_ids[]"]').forEach(input => input.remove());

            // Add selected IDs as hidden inputs
            selectedCheckboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'profile_ids[]';
                input.value = checkbox.value;
                this.appendChild(input);
            });
        });
    </script>
@endsection
