@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Performance Data',
        'subTitle' => "View and analyze heat exchanger performance metrics and test results",
        'buttonText' => 'Add Performance Data',
        'buttonUrl' => route('performance-data.create'),
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
                        <form method="GET" action="{{ route('performance-data.index') }}" class="flex flex-wrap gap-4 items-end">
                            <!-- Product Filter -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Product</label>
                                <select name="product_id" class="kt-select w-48" onchange="loadVersions(this.value)">
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
                                            {{ $version->model_number }} - {{ $version->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Temperature Profile Filter -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Temperature Profile</label>
                                <select name="temperature_profile_id" class="kt-select w-48"
                                        class="kt-select"
                                        data-kt-select="true"
                                        data-kt-select-enable-search="true"
                                        data-kt-select-search-placeholder="Search..."
                                        data-kt-select-placeholder="Select a record..."
                                >
                                    <option value="">All Profiles</option>
                                    @foreach($temperatureProfiles as $profile)
                                        <option value="{{ $profile->id }}" {{ request('temperature_profile_id') == $profile->id ? 'selected' : '' }}>
                                            {{ $profile->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Heat Input Range -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Heat Input (kW)</label>
                                <div class="flex gap-2 items-center">
                                    <input type="number" name="heat_min" value="{{ request('heat_min') }}" placeholder="Min" class="kt-input w-20" step="0.1">
                                    <span class="text-gray-500">-</span>
                                    <input type="number" name="heat_max" value="{{ request('heat_max') }}" placeholder="Max" class="kt-input w-20" step="0.1">
                                </div>
                            </div>

                            <!-- Pressure Drop Range -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Pressure Drop (kPa)</label>
                                <div class="flex gap-2 items-center">
                                    <input type="number" name="pressure_min" value="{{ request('pressure_min') }}" placeholder="Min" class="kt-input w-20" step="0.1">
                                    <span class="text-gray-500">-</span>
                                    <input type="number" name="pressure_max" value="{{ request('pressure_max') }}" placeholder="Max" class="kt-input w-20" step="0.1">
                                </div>
                            </div>

                            <!-- Search -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Search</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="kt-input w-48">
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button type="submit" class="kt-btn kt-btn-primary">
                                    <i class="ki-filled ki-magnifier"></i>
                                    Filter
                                </button>
                                <a href="{{ route('performance-data.index') }}" class="kt-btn kt-btn-secondary">
                                    <i class="ki-filled ki-cross"></i>
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="kt-card kt-card-grid h-full min-w-full">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">
                            Performance Data
                        </h3>
{{--                        <div class="kt-input max-w-48">--}}
{{--                            <i class="ki-filled ki-magnifier">--}}
{{--                            </i>--}}
{{--                            <input data-kt-datatable-search="#performance_data_table" placeholder="Search Performance Data" type="text">--}}
{{--                            </input>--}}
{{--                        </div>--}}
                    </div>
                    <div class="kt-card-table">
                        <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="5" id="performance_data_datatable">
                            <div class="kt-scrollable-x-auto">
                                <table class="kt-table kt-table-border table-fixed" data-kt-datatable-table="true" id="performance_data_table">
                                    <thead>
                                    <tr>
                                        <th class="w-[200px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Product & Models
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                        <th class="w-[150px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Heat Input (kW)
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                        <th class="w-[130px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Flow Rates (l/s)
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                        <th class="w-[120px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Pressure (kPa)
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
                                    @foreach($performanceData as $data)
                                        <tr>
                                            <td>
                                                <div class="flex flex-col gap-2">
                                                    <span class="leading-none font-medium text-sm text-mono">
                                                        {{ $data->version->product->name }}
                                                    </span>
                                                    <a class="text-xs text-primary hover:underline" href="{{ route('versions.show', $data->version->id) }}">
                                                        {{ $data->version->name ?: $data->version->model_number }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="kt-badge kt-badge-sm kt-badge-primary">
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
                                                <a href="{{ route('performance-data.edit', $data->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
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
    function loadVersions(productId) {
        const versionSelect = document.getElementById('version_select');

        // Clear current options
        versionSelect.innerHTML = '<option value="">All Models</option>';

        if (!productId) {
            return;
        }

        // Fetch versions for the selected product
        fetch(`/api/versions-by-product/${productId}`)
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
