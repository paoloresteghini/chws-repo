@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Vessel Configurations',
        'subTitle' => "Manage tank and vessel capacity options for DHW systems",
        'buttonText' => 'Create Configuration',
        'buttonUrl' => route('vessel-configurations.create'),
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
                        <form method="GET" action="{{ route('vessel-configurations.index') }}" class="flex flex-wrap gap-4 items-end">
                            <!-- Product Filter -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Product</label>
                                <select name="product_id" class="kt-select w-48" onchange="loadVesselVersions(this.value)">
                                    <option value="">All Products</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Version Filter -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Models</label>
                                <select name="version_id" id="version_select" class="kt-select w-48"
                                        class="kt-select"
                                        data-kt-select="true"
                                        data-kt-select-enable-search="true"
                                        data-kt-select-search-placeholder="Search..."
                                        data-kt-select-placeholder="Select a record..."
                                >
                                    <option value="">All Models</option>
                                    @foreach($versions as $version)
                                        <option value="{{ $version->id }}" {{ request('version_id') == $version->id ? 'selected' : '' }}>
                                            {{ $version->product->name }} - {{ $version->model_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Capacity Range -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Capacity</label>
                                <div class="flex gap-2 items-center">
                                    <input type="number" name="capacity_min" value="{{ request('capacity_min') }}" placeholder="Min" class="kt-input w-20" step="1">
                                    <span class="text-gray-500">-</span>
                                    <input type="number" name="capacity_max" value="{{ request('capacity_max') }}" placeholder="Max" class="kt-input w-20" step="1">
                                </div>
                            </div>

                            <!-- Capacity Unit Filter -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Unit</label>
                                <select name="capacity_unit" class="kt-select w-24">
                                    <option value="">All Units</option>
                                    @foreach($capacityUnits as $unit)
                                        <option value="{{ $unit }}" {{ request('capacity_unit') == $unit ? 'selected' : '' }}>
                                            {{ $unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Search -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Search</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search configurations..." class="kt-input w-48">
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
                    </div>
                </div>

                <!-- Statistics Card -->
{{--                @if(isset($stats))--}}
{{--                <div class="kt-card">--}}
{{--                    <div class="kt-card-body">--}}
{{--                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">--}}
{{--                            <div class="flex flex-col">--}}
{{--                                <span class="text-xs text-gray-500 uppercase">Total Configurations</span>--}}
{{--                                <span class="text-2xl font-semibold">{{ $stats['total_configurations'] }}</span>--}}
{{--                            </div>--}}
{{--                            <div class="flex flex-col">--}}
{{--                                <span class="text-xs text-gray-500 uppercase">Used in Tests</span>--}}
{{--                                <span class="text-2xl font-semibold text-primary">{{ $stats['used_configurations'] }}</span>--}}
{{--                            </div>--}}
{{--                            <div class="flex flex-col">--}}
{{--                                <span class="text-xs text-gray-500 uppercase">Capacity Range</span>--}}
{{--                                <span class="text-sm">{{ $stats['capacity_range']['min'] }} - {{ $stats['capacity_range']['max'] }}</span>--}}
{{--                            </div>--}}
{{--                            <div class="flex flex-col">--}}
{{--                                <span class="text-xs text-gray-500 uppercase">Products with Vessels</span>--}}
{{--                                <span class="text-2xl font-semibold text-info">{{ $stats['products_with_vessels'] }}</span>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                @endif--}}

                <div class="kt-card kt-card-grid h-full min-w-full">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">
                            Vessel Configurations
                        </h3>
                        <div class="kt-input max-w-48">
                            <i class="ki-filled ki-magnifier">
                            </i>
                            <input data-kt-datatable-search="#vessel_configurations_table" placeholder="Search Configurations" type="text">
                            </input>
                        </div>
                    </div>
                    <div class="kt-card-table">
                        <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="5" id="vessel_configurations_datatable">
                            <div class="kt-scrollable-x-auto">
                                <table class="kt-table kt-table-border table-fixed" data-kt-datatable-table="true" id="vessel_configurations_table">
                                    <thead>
                                    <tr>

                                        <th class="w-[150px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Product
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                        <th class="w-[130px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Version
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                        <th class="w-[180px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Configuration
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                        <th class="w-[100px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Capacity
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                        <th class="w-[125px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Actions
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($configurations as $config)
                                        <tr>
                                            <td>
                                                <div class="flex flex-col gap-2">
                                                    <span class="leading-none font-medium text-sm text-mono">
                                                        {{ $config->version->product->name }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-2">
                                                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="{{ route('versions.show', $config->version->id) }}">
                                                        {{ $config->version->name ?: $config->version->model_number }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-2">
                                                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="{{ route('vessel-configurations.show', $config->id) }}">
                                                        {{ $config->name }}
                                                    </a>
                                                    @if($config->description)
                                                        <span class="text-xs text-gray-500">{{ Str::limit($config->description, 40) }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($config->capacity)
                                                    <span class="kt-badge kt-badge-outline kt-badge-info rounded-full">
                                                        {{ number_format($config->capacity, 0) }}{{ $config->capacity_unit }}
                                                    </span>
                                                @else
                                                    <span class="text-sm text-gray-400">{{ $config->capacity_unit }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('vessel-configurations.edit', $config->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                                                    <i class="ki-filled ki-pencil"></i>
                                                    Edit
                                                </a>
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

@push('scripts')
<script>
    function loadVesselVersions(productId) {
        const versionSelect = document.getElementById('version_select');

        // Clear current options
        versionSelect.innerHTML = '<option value="">All Versions</option>';

        if (!productId) {
            return;
        }

        // Fetch versions for the selected product that have vessel options
        fetch(`/api/versions-by-product/${productId}?has_vessel_options=1`)
            .then(response => response.json())
            .then(versions => {
                versions.forEach(version => {
                    const option = document.createElement('option');
                    option.value = version.id;
                    option.text = `${version.model_number} - ${version.name || ''}`;
                    versionSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading versions:', error));
    }
</script>
@endpush
