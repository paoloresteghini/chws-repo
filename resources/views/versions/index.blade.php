@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Product Models',
        'subTitle' => "Manage product models, specifications, and performance configurations",
        'buttonText' => 'Create Version',
        'buttonUrl' => "/versions/create",
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed" id="contentContainer">
        </div>
        <div class="kt-container-fixed">
            <div class="grid gap-5 lg:gap-7.5">
                <!-- Filter Section -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Filters</h3>
                    </div>
                    <div class="kt-card-body px-5 py-5">
                        <form method="GET" action="{{ route('versions.index') }}" class="flex flex-wrap gap-4 items-end">
                            <!-- Product Filter -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Product</label>
                                <select name="product_id" class="kt-select w-48">
                                    <option value="">All Products</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Category Filter -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Category</label>
                                <select name="category_id" class="kt-select w-48">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->product->name }} - {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Status</label>
                                <select name="status" class="kt-select w-32">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- Version Number Search -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Model Number</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search model number" class="kt-input w-48">
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button type="submit" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-magnifier"></i>
                                    Filter
                                </button>
                                <a href="{{ route('versions.index') }}" class="kt-btn kt-btn-secondary">
                                    <i class="ki-filled ki-cross"></i>
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Versions Table -->
                <div class="kt-card kt-card-grid h-full min-w-full">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">
                            Product Versions
                        </h3>
{{--                        <div class="kt-input max-w-48">--}}
{{--                            <i class="ki-filled ki-magnifier"></i>--}}
{{--                            <input data-datatable-search="true" placeholder="Search Versions" type="text">--}}
{{--                        </div>--}}
                    </div>
                    <div class="kt-card-table">
                        <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10" id="versions_datatable">
                            <div class="kt-scrollable-x-auto">
                                <table class="kt-table kt-table-border table-fixed" data-kt-datatable-table="true" id="versions_table">
                                    <thead>
                                    <tr>
                                        <th class="w-[50px]">
                                            <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-check="true" type="checkbox">
                                        </th>
                                        <th class="w-[120px]">
                                            <span class="kt-table-col">
                                                <span class="kt-table-col-label">Product</span>
                                                <span class="kt-table-col-sort"></span>
                                            </span>
                                        </th>
                                        <th class="w-[100px]">
                                            <span class="kt-table-col">
                                                <span class="kt-table-col-label">Model</span>
                                                <span class="kt-table-col-sort"></span>
                                            </span>
                                        </th>
                                        <th class="w-[180px]">
                                            <span class="kt-table-col">
                                                <span class="kt-table-col-label">Name</span>
                                                <span class="kt-table-col-sort"></span>
                                            </span>
                                        </th>
                                        <th class="w-[150px]">
                                            <span class="kt-table-col">
                                                <span class="kt-table-col-label">Category</span>
                                                <span class="kt-table-col-sort"></span>
                                            </span>
                                        </th>
                                        <th class="w-[80px]">
                                            <span class="kt-table-col">
                                                <span class="kt-table-col-label">Vessels</span>
                                                <span class="kt-table-col-sort"></span>
                                            </span>
                                        </th>
                                        <th class="w-[80px]">
                                            <span class="kt-table-col">
                                                <span class="kt-table-col-label">Performance</span>
                                                <span class="kt-table-col-sort"></span>
                                            </span>
                                        </th>
                                        <th class="w-[80px]">
                                            <span class="kt-table-col">
                                                <span class="kt-table-col-label">Status</span>
                                                <span class="kt-table-col-sort"></span>
                                            </span>
                                        </th>
                                        <th class="w-[120px]">
                                            <span class="kt-table-col">
                                                <span class="kt-table-col-label">Actions</span>
                                            </span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($versions as $version)
                                        <tr>
                                            <td>
                                                <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="{{ $version->id }}">
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-medium text-gray-900">{{ $version->product->name }}</span>
                                                        <span class="text-xs text-gray-500">{{ $version->product->type }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-mono font-medium text-gray-900">{{ $version->model_number }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col">
                                                    <a class="text-sm font-medium text-gray-900 hover:text-primary" href="{{ route('versions.show', $version->id) }}">
                                                        {{ $version->name }}
                                                    </a>
                                                    @if($version->description)
                                                        <span class="text-xs text-gray-500">{{ Str::limit($version->description, 40) }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($version->category)
                                                    <span class="kt-badge kt-badge-xs kt-badge-outline">
                                                        {{ $version->category->name }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-400">No category</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    @if($version->has_vessel_options)
                                                        <span class="kt-badge kt-badge-xs kt-badge-info">
                                                            {{ $version->vesselConfigurations->count() }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-gray-400">-</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <span class="kt-badge kt-badge-xs {{ $version->performanceData->count() > 0 ? 'kt-badge-success' : 'kt-badge-secondary' }}">
                                                        {{ $version->performanceData->count() }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($version->status)
                                                    <span class="kt-badge kt-badge-xs kt-badge-success">Active</span>
                                                @else
                                                    <span class="kt-badge kt-badge-xs kt-badge-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="flex gap-1">
                                                    <a href="{{ route('versions.show', $version->id) }}" class="kt-btn kt-btn-xs kt-btn-secondary" title="View Details">
                                                        <i class="ki-filled ki-eye"></i>
                                                    </a>
                                                    <a href="{{ route('versions.edit', $version->id) }}" class="kt-btn kt-btn-xs kt-btn-primary" title="Edit">
                                                        <i class="ki-filled ki-pencil"></i>
                                                    </a>
                                                    @if($version->performanceData->count() > 0)
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

                            <div class="kt-card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-secondary-foreground text-sm font-medium">
                                <div class="flex items-center gap-2 order-2 md:order-1">
                                    Show
                                    <select class="kt-select w-16" data-kt-datatable-size="true" data-kt-select="" name="perpage">
                                    </select>
                                    per page
                                </div>
                                <div class="flex items-center gap-4 order-1 md:order-2">
                                    <span data-kt-datatable-info="true">
                                    </span>
                                    <div class="kt-datatable-pagination" data-kt-datatable-pagination="true">
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
