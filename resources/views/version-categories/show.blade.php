@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => $versionCategory->name,
        'subTitle' => 'Category details and associated versions',
        'buttonText' => 'Edit Category',
        'buttonUrl' => route('version-categories.edit', $versionCategory->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('version-categories.index') }}" class="hover:text-primary">Version Categories</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">{{ $versionCategory->name }}</span>
            </div>

            <div class="grid gap-5 lg:gap-7.5">
                <!-- Category Overview -->
                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Details -->
                    <div class="lg:col-span-2">
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Category Information</h3>
                                <div class="flex gap-2">
                                    <a href="{{ route('version-categories.edit', $versionCategory->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                        <i class="ki-filled ki-pencil"></i>
                                        Edit
                                    </a>
                                    @if($stats['total_versions'] > 0)
                                        <button onclick="showAssignVersionsModal()" class="kt-btn kt-btn-sm kt-btn-info">
                                            <i class="ki-filled ki-abstract-26"></i>
                                            Manage Versions
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Product</label>
                                            <div class="mt-1">
                                                <span class="text-lg font-medium text-gray-900">{{ $versionCategory->product->name }}</span>
                                                <span class="ml-2 kt-badge kt-badge-sm kt-badge-outline">{{ $versionCategory->product->type }}</span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Category Name</label>
                                            <div class="mt-1 text-2xl font-bold text-primary">{{ $versionCategory->name }}</div>
                                        </div>

                                        @if($versionCategory->prefix)
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Prefix</label>
                                                <div class="mt-1">
                                                    <span class="kt-badge kt-badge-lg kt-badge-info">{{ $versionCategory->prefix }}</span>
                                                </div>
                                            </div>
                                        @endif

                                        @if($versionCategory->description)
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Description</label>
                                                <div class="mt-1 text-gray-900">{{ $versionCategory->description }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Sort Order</label>
                                            <div class="mt-1 text-lg text-gray-900">{{ $versionCategory->sort_order }}</div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Created</label>
                                            <div class="mt-1 text-sm text-gray-600">{{ $versionCategory->created_at->format('M j, Y \a\t g:i A') }}</div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Last Updated</label>
                                            <div class="mt-1 text-sm text-gray-600">{{ $versionCategory->updated_at->format('M j, Y \a\t g:i A') }}</div>
                                        </div>
                                    </div>
                                </div>

                                @if($versionCategory->category_specs)
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <label class="text-sm font-medium text-gray-700">Category Specifications</label>
                                        <div class="mt-2">
                                            <pre class="bg-gray-50 p-3 rounded text-sm">{{ json_encode($versionCategory->category_specs, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="space-y-5">
                        <!-- Version Statistics -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Version Statistics</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-primary">{{ $stats['total_versions'] }}</div>
                                            <div class="text-xs text-gray-500">Total Versions</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-success">{{ $stats['active_versions'] }}</div>
                                            <div class="text-xs text-gray-500">Active Versions</div>
                                        </div>
                                    </div>

                                    @if($stats['total_versions'] > 0)
                                        <div class="pt-4 border-t border-gray-200">
                                            <div class="text-sm font-medium text-gray-700 mb-2">Performance Data</div>
                                            <div class="space-y-2">
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-600">Versions with data:</span>
                                                    <span class="font-medium">{{ $stats['versions_with_performance'] }}/{{ $stats['total_versions'] }}</span>
                                                </div>
                                                <div class="flex justify-between text-sm">
                                                    <span class="text-gray-600">Total records:</span>
                                                    <span class="font-medium">{{ $stats['total_performance_records'] }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        @if(isset($stats['total_vessel_configurations']))
                                            <div class="pt-4 border-t border-gray-200">
                                                <div class="text-sm font-medium text-gray-700 mb-2">Vessel Configurations</div>
                                                <div class="text-center">
                                                    <div class="text-xl font-bold text-info">{{ $stats['total_vessel_configurations'] }}</div>
                                                    <div class="text-xs text-gray-500">Total Configurations</div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Quick Actions</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <a href="{{ route('version-categories.edit', $versionCategory->id) }}" class="kt-btn kt-btn-sm kt-btn-primary w-full">
                                        <i class="ki-filled ki-pencil"></i>
                                        Edit Category
                                    </a>

                                    @if($stats['total_versions'] > 0)
                                        <a href="{{ route('versions.index', ['category_id' => $versionCategory->id]) }}" class="kt-btn kt-btn-sm kt-btn-info w-full">
                                            <i class="ki-filled ki-abstract-26"></i>
                                            View All Versions
                                        </a>
                                    @endif

                                    <a href="{{ route('versions.create', ['category_id' => $versionCategory->id]) }}" class="kt-btn kt-btn-sm kt-btn-success w-full">
                                        <i class="ki-filled ki-plus"></i>
                                        Add Version
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Associated Versions -->
                @if($stats['total_versions'] > 0)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Associated Versions
                                <span class="text-sm text-gray-500 font-normal">({{ $stats['total_versions'] }} total)</span>
                            </h3>
                            <div class="flex gap-2">
                                <select id="status-filter" class="kt-select w-32" onchange="filterVersions()">
                                    <option value="">All Status</option>
                                    <option value="active">Active Only</option>
                                    <option value="inactive">Inactive Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @foreach($versionsByStatus->flatten() as $version)
                                    <div class="version-card border border-gray-200 rounded-lg p-4" data-status="{{ $version->status ? 'active' : 'inactive' }}">
                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <h4 class="font-medium text-gray-900">
                                                    <a href="{{ route('versions.show', $version->id) }}" class="hover:text-primary">
                                                        {{ $version->model_number }}
                                                    </a>
                                                </h4>
                                                @if($version->name && $version->name !== $version->product->name . ' ' . $version->model_number)
                                                    <div class="text-sm text-gray-600">{{ $version->name }}</div>
                                                @endif
                                            </div>
                                            @if($version->status)
                                                <span class="kt-badge kt-badge-xs kt-badge-success">Active</span>
                                            @else
                                                <span class="kt-badge kt-badge-xs kt-badge-secondary">Inactive</span>
                                            @endif
                                        </div>

                                        <div class="space-y-2">
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-600">Performance Records:</span>
                                                <span class="font-medium">{{ $version->performanceData->count() }}</span>
                                            </div>

                                            @if($version->vesselConfigurations->count() > 0)
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-gray-600">Vessel Configurations:</span>
                                                    <span class="font-medium">{{ $version->vesselConfigurations->count() }}</span>
                                                </div>
                                            @endif

                                            <div class="flex gap-1 mt-3">
                                                <a href="{{ route('versions.show', $version->id) }}"
                                                   class="kt-btn kt-btn-xs kt-btn-secondary flex-1">
                                                    <i class="ki-filled ki-eye"></i>
                                                    View
                                                </a>
                                                <a href="{{ route('versions.edit', $version->id) }}"
                                                   class="kt-btn kt-btn-xs kt-btn-primary flex-1">
                                                    <i class="ki-filled ki-pencil"></i>
                                                    Edit
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <!-- No Versions State -->
                    <div class="kt-card">
                        <div class="kt-card-body px-6 py-6">
                            <div class="text-center py-12">
                                <i class="ki-filled ki-abstract-26 text-6xl text-gray-300"></i>
                                <h3 class="text-lg font-medium text-gray-900 mt-4">No Versions Assigned</h3>
                                <p class="text-gray-600 mt-2">This category doesn't have any versions assigned yet.</p>
                                <div class="mt-6 flex gap-3 justify-center">
                                    <a href="{{ route('versions.create', ['category_id' => $versionCategory->id]) }}" class="kt-btn kt-btn-primary">
                                        <i class="ki-filled ki-plus"></i>
                                        Create New Version
                                    </a>
                                    <button onclick="showAssignVersionsModal()" class="kt-btn kt-btn-secondary">
                                        <i class="ki-filled ki-abstract-26"></i>
                                        Assign Existing Versions
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="kt-card">
                    <div class="kt-card-body px-6 py-6">
                        <div class="flex justify-between items-center">
                            <div class="flex gap-3">
                                <a href="{{ route('version-categories.index') }}" class="kt-btn kt-btn-secondary">
                                    <i class="ki-filled ki-arrow-left"></i>
                                    Back to Categories
                                </a>
                                <a href="{{ route('version-categories.edit', $versionCategory->id) }}" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-pencil"></i>
                                    Edit Category
                                </a>
                            </div>

                            <div class="flex gap-3">
                                @if($stats['total_versions'] == 0)
                                    <form method="POST" action="{{ route('version-categories.destroy', $versionCategory->id) }}"
                                          class="inline" onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-danger">
                                            <i class="ki-filled ki-trash"></i>
                                            Delete Category
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Assign Versions Modal (placeholder) -->
    <div id="assign-versions-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Versions</h3>
                <p class="text-gray-600 mb-4">This feature allows you to assign existing versions to this category.</p>
                <div class="flex gap-3 justify-end">
                    <button onclick="hideAssignVersionsModal()" class="kt-btn kt-btn-secondary">Cancel</button>
                    <a href="{{ route('versions.index', ['product_id' => $versionCategory->product_id]) }}" class="kt-btn kt-btn-primary">
                        Go to Versions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterVersions() {
            const statusFilter = document.getElementById('status-filter').value;
            const versionCards = document.querySelectorAll('.version-card');

            versionCards.forEach(card => {
                const cardStatus = card.dataset.status;
                let show = true;

                if (statusFilter === 'active' && cardStatus !== 'active') {
                    show = false;
                } else if (statusFilter === 'inactive' && cardStatus !== 'inactive') {
                    show = false;
                }

                card.style.display = show ? 'block' : 'none';
            });
        }

        function showAssignVersionsModal() {
            document.getElementById('assign-versions-modal').classList.remove('hidden');
        }

        function hideAssignVersionsModal() {
            document.getElementById('assign-versions-modal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('assign-versions-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideAssignVersionsModal();
            }
        });
    </script>
@endsection
