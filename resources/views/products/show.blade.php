@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => $product->name,
        'subTitle' => 'Product details and specifications',
        'buttonText' => 'Edit Product',
        'buttonUrl' => route('products.edit', $product->id),
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-6">
                <a href="{{ route('products.index') }}" class="hover:text-primary">Products</a>
                <i class="ki-filled ki-right text-xs"></i>
                <span class="text-gray-900">{{ $product->name }}</span>
            </div>

            <div class="grid gap-5 lg:gap-7.5">
                <!-- Product Overview -->
                <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
                    <!-- Main Details -->
                    <div class="lg:col-span-2">
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Product Information</h3>
                                <div class="flex gap-2">
                                    <a href="{{ route('products.edit', $product->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                        <i class="ki-filled ki-pencil"></i>
                                        Edit
                                    </a>
                                </div>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Product Name</label>
                                            <div class="mt-1 text-2xl font-bold text-primary">{{ $product->name }}</div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Type</label>
                                            <div class="mt-1">
                                                <span class="kt-badge kt-badge-lg kt-badge-info">{{ $product->type }}</span>
                                            </div>
                                        </div>

                                        @if($product->description)
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Description</label>
                                                <div class="mt-1 text-gray-900">{{ $product->description }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700">Features</label>
                                            <div class="mt-2 space-y-2">
                                                <div class="flex items-center gap-2">
                                                    @if($product->has_temperature_profiles)
                                                        <i class="ki-filled ki-check text-success"></i>
                                                        <span class="text-sm">Temperature Profiles</span>
                                                    @else
                                                        <i class="ki-filled ki-cross text-gray-400"></i>
                                                        <span class="text-sm text-gray-500">Temperature Profiles</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    @if($product->has_vessel_options)
                                                        <i class="ki-filled ki-check text-success"></i>
                                                        <span class="text-sm">Vessel Options</span>
                                                    @else
                                                        <i class="ki-filled ki-cross text-gray-400"></i>
                                                        <span class="text-sm text-gray-500">Vessel Options</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if($product->image)
                                            <div>
                                                <label class="text-sm font-medium text-gray-700">Product Image</label>
                                                <div class="mt-2">
                                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" 
                                                         class="w-32 h-32 object-cover rounded-lg shadow-sm">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Sidebar -->
                    <div class="space-y-5">
                        <!-- Quick Stats -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Statistics</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-4">
                                    <div>
                                        <div class="text-sm text-gray-600">Total Versions</div>
                                        <div class="text-2xl font-bold text-primary">{{ $stats['total_versions'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $stats['active_versions'] }} active</div>
                                    </div>

                                    <div>
                                        <div class="text-sm text-gray-600">Performance Records</div>
                                        <div class="text-2xl font-bold text-info">{{ $stats['total_performance_records'] }}</div>
                                    </div>

                                    @if($product->has_temperature_profiles && $stats['temperature_profiles_count'] > 0)
                                        <div>
                                            <div class="text-sm text-gray-600">Temperature Profiles</div>
                                            <div class="text-2xl font-bold text-success">{{ $stats['temperature_profiles_count'] }}</div>
                                        </div>
                                    @endif

                                    @if($product->has_vessel_options && $stats['vessel_configurations_count'] > 0)
                                        <div>
                                            <div class="text-sm text-gray-600">Vessel Configurations</div>
                                            <div class="text-2xl font-bold text-warning">{{ $stats['vessel_configurations_count'] }}</div>
                                        </div>
                                    @endif

                                    <div>
                                        <div class="text-sm text-gray-600">Heat Input Range</div>
                                        <div class="text-lg font-semibold">
                                            {{ number_format($stats['heat_input_range']['min'], 1) }} - {{ number_format($stats['heat_input_range']['max'], 1) }} kW
                                        </div>
                                    </div>

                                    <div>
                                        <div class="text-sm text-gray-600">Attachments</div>
                                        <div class="text-2xl font-bold">{{ $product->attachments->count() }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="kt-card">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">Timeline</h3>
                            </div>
                            <div class="kt-card-body px-6 py-6">
                                <div class="space-y-3">
                                    <div>
                                        <div class="text-xs text-gray-600">Created</div>
                                        <div class="text-sm">{{ $product->created_at->format('M j, Y') }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-600">Last Updated</div>
                                        <div class="text-sm">{{ $product->updated_at->format('M j, Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                @if($product->attachments->count() > 0)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Product Attachments
                                <span class="text-sm text-gray-500 font-normal">({{ $product->attachments->count() }} files)</span>
                            </h3>
                            <a href="{{ route('products.edit', $product->id) }}#attachments" class="kt-btn kt-btn-sm kt-btn-primary">
                                <i class="ki-filled ki-plus"></i>
                                Add More
                            </a>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @foreach($product->attachments as $attachment)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:border-primary transition-colors">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0">
                                                @if($attachment->is_image)
                                                    <i class="ki-duotone ki-picture text-2xl text-info">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                @elseif($attachment->is_pdf)
                                                    <i class="ki-duotone ki-file-pdf text-2xl text-danger">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                @else
                                                    <i class="ki-duotone ki-file text-2xl text-gray-500">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                @endif
                                            </div>
                                            <div class="flex-grow min-w-0">
                                                <p class="font-medium text-gray-900 truncate">{{ $attachment->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $attachment->file_name }}</p>
                                                <p class="text-xs text-gray-400">{{ number_format($attachment->file_size / 1024, 2) }} KB</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 mt-3">
                                            @if($attachment->is_image || $attachment->is_pdf)
                                                <a href="{{ route('attachments.preview', $attachment) }}" target="_blank" 
                                                   class="kt-btn kt-btn-sm kt-btn-light" title="Preview">
                                                    <i class="ki-filled ki-eye"></i>
                                                </a>
                                            @endif
                                            <a href="{{ $attachment->url }}" target="_blank" 
                                               class="kt-btn kt-btn-sm kt-btn-light w-full" title="Download">
                                                <i class="ki-filled ki-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Versions Table -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">
                            Versions
                            <span class="text-sm text-gray-500 font-normal">({{ $product->versions->count() }} total)</span>
                        </h3>
                        <a href="{{ route('versions.create') }}?product_id={{ $product->id }}" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-plus"></i>
                            Add Version
                        </a>
                    </div>
                    <div class="kt-card-body">
                        @if($product->versions->count() > 0)
                            <div class="kt-table-responsive">
                                <table class="kt-table kt-table-hover">
                                    <thead>
                                        <tr>
                                            <th>Model Number</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Performance Data</th>
                                            <th>Vessel Options</th>
                                            <th>Attachments</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($product->versions as $version)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('versions.show', $version->id) }}" class="font-mono font-medium text-primary hover:underline">
                                                        {{ $version->model_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $version->name ?: '-' }}</td>
                                                <td>
                                                    @if($version->category)
                                                        <span class="kt-badge kt-badge-sm kt-badge-outline">{{ $version->category->name }}</span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($version->status)
                                                        <span class="kt-badge kt-badge-sm kt-badge-success">Active</span>
                                                    @else
                                                        <span class="kt-badge kt-badge-sm kt-badge-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($version->performanceData->count() > 0)
                                                        <span class="kt-badge kt-badge-sm kt-badge-info">
                                                            {{ $version->performanceData->count() }} records
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($version->has_vessel_options && $version->vesselConfigurations->count() > 0)
                                                        <span class="kt-badge kt-badge-sm kt-badge-warning">
                                                            {{ $version->vesselConfigurations->count() }} options
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($version->attachments->count() > 0)
                                                        <span class="kt-badge kt-badge-sm kt-badge-outline">
                                                            {{ $version->attachments->count() }} files
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('versions.show', $version->id) }}" class="kt-btn kt-btn-sm kt-btn-light">
                                                        <i class="ki-filled ki-eye"></i>
                                                    </a>
                                                    <a href="{{ route('versions.edit', $version->id) }}" class="kt-btn kt-btn-sm kt-btn-light">
                                                        <i class="ki-filled ki-pencil"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="py-8 text-center">
                                <i class="ki-duotone ki-file-search text-6xl text-gray-300">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <p class="text-gray-500 mt-3">No versions found for this product</p>
                                <a href="{{ route('versions.create') }}?product_id={{ $product->id }}" class="kt-btn kt-btn-sm kt-btn-primary mt-4">
                                    <i class="ki-filled ki-plus"></i>
                                    Create First Version
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Available Temperature Profiles -->
                @if($product->has_temperature_profiles && $temperatureProfiles->count() > 0)
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">
                                Available Temperature Profiles
                                <span class="text-sm text-gray-500 font-normal">({{ $temperatureProfiles->count() }} profiles)</span>
                            </h3>
                        </div>
                        <div class="kt-card-body px-6 py-6">
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($temperatureProfiles as $profile)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900 mb-2">{{ $profile->name }}</h4>
                                        <p class="text-sm text-gray-600 mb-3">{{ $profile->display_name }}</p>
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            <div>
                                                <span class="text-gray-500">Primary Flow:</span>
                                                <span class="font-medium">{{ $profile->primary_flow_temp }}°C</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Primary Return:</span>
                                                <span class="font-medium">{{ $profile->primary_return_temp }}°C</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Secondary Flow:</span>
                                                <span class="font-medium">{{ $profile->secondary_flow_temp }}°C</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Secondary Return:</span>
                                                <span class="font-medium">{{ $profile->secondary_return_temp }}°C</span>
                                            </div>
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
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('products.edit', $product->id) }}" class="kt-btn kt-btn-primary">
                                <i class="ki-filled ki-pencil"></i>
                                Edit Product
                            </a>

                            <a href="{{ route('versions.create') }}?product_id={{ $product->id }}" class="kt-btn kt-btn-outline">
                                <i class="ki-filled ki-plus"></i>
                                Add Version
                            </a>

                            <a href="{{ route('products.index') }}" class="kt-btn kt-btn-outline">
                                <i class="ki-filled ki-arrow-left"></i>
                                Back to Products
                            </a>

                            <div class="ml-auto">
                                <form method="POST" action="{{ route('products.destroy', $product->id) }}" class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this product? This will also delete all associated versions and data.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="kt-btn kt-btn-destructive">
                                        <i class="ki-filled ki-trash"></i>
                                        Delete Product
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