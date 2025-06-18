@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Temperature Profiles',
        'subTitle' => "Configure operating temperature conditions for performance analysis",
        'buttonText' => 'Create Profile',
        'buttonUrl' => route('temperature-profiles.create'),
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
                        <form method="GET" action="{{ route('temperature-profiles.index') }}" class="flex flex-wrap gap-4 items-end">
                            <!-- Primary Temperature Range -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Primary Temperature (°C)</label>
                                <div class="flex gap-2 items-center">
                                    <input type="number" name="primary_temp_min" value="{{ request('primary_temp_min') }}" placeholder="Min" class="kt-input w-20" step="1">
                                    <span class="text-gray-500">-</span>
                                    <input type="number" name="primary_temp_max" value="{{ request('primary_temp_max') }}" placeholder="Max" class="kt-input w-20" step="1">
                                </div>
                            </div>

                            <!-- Secondary Temperature Range -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Secondary Temperature (°C)</label>
                                <div class="flex gap-2 items-center">
                                    <input type="number" name="secondary_temp_min" value="{{ request('secondary_temp_min') }}" placeholder="Min" class="kt-input w-20" step="1">
                                    <span class="text-gray-500">-</span>
                                    <input type="number" name="secondary_temp_max" value="{{ request('secondary_temp_max') }}" placeholder="Max" class="kt-input w-20" step="1">
                                </div>
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

                            <!-- Search -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700">Search</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search profiles..." class="kt-input w-48">
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
                    </div>
                </div>

{{--                <!-- Statistics Card -->--}}
{{--                @if(isset($stats))--}}
{{--                <div class="kt-card">--}}
{{--                    <div class="kt-card-body">--}}
{{--                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">--}}
{{--                            <div class="flex flex-col">--}}
{{--                                <span class="text-xs text-gray-500 uppercase">Total Profiles</span>--}}
{{--                                <span class="text-2xl font-semibold">{{ $stats['total_profiles'] }}</span>--}}
{{--                            </div>--}}
{{--                            <div class="flex flex-col">--}}
{{--                                <span class="text-xs text-gray-500 uppercase">Active Profiles</span>--}}
{{--                                <span class="text-2xl font-semibold text-success">{{ $stats['active_profiles'] }}</span>--}}
{{--                            </div>--}}
{{--                            <div class="flex flex-col">--}}
{{--                                <span class="text-xs text-gray-500 uppercase">Used in Tests</span>--}}
{{--                                <span class="text-2xl font-semibold text-primary">{{ $stats['used_profiles'] }}</span>--}}
{{--                            </div>--}}
{{--                            <div class="flex flex-col">--}}
{{--                                <span class="text-xs text-gray-500 uppercase">Temperature Range</span>--}}
{{--                                <div class="text-sm">--}}
{{--                                    <div>Primary: {{ $stats['temp_range']['primary_min'] }}° - {{ $stats['temp_range']['primary_max'] }}°</div>--}}
{{--                                    <div>Secondary: {{ $stats['temp_range']['secondary_min'] }}° - {{ $stats['temp_range']['secondary_max'] }}°</div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                @endif--}}

                <div class="kt-card kt-card-grid h-full min-w-full">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">
                            Temperature Profiles
                        </h3>
    {{--                        <div class="kt-input max-w-48">--}}
    {{--                            <i class="ki-filled ki-magnifier">--}}
    {{--                            </i>--}}
    {{--                            <input data-kt-datatable-search="#temperature_profiles_table" placeholder="Search Profiles" type="text">--}}
    {{--                            </input>--}}
    {{--                        </div>--}}
                    </div>
                    <div class="kt-card-table">
                        <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="5" id="temperature_profiles_datatable">
                            <div class="kt-scrollable-x-auto">
                                <table class="kt-table kt-table-border table-fixed" data-kt-datatable-table="true" id="temperature_profiles_table">
                                    <thead>
                                    <tr>

                                        <th class="w-[200px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Name
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                        <th class="w-[160px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Primary Temps
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                        <th class="w-[160px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Secondary Temps
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                        <th class="w-[100px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Status
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
                                    @foreach($profiles as $profile)
                                        <tr>
                                            <td>
                                                <div class="flex flex-col gap-2">
                                                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="{{ route('temperature-profiles.show', $profile->id) }}">
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
                                                @if($profile->is_active)
                                                    <span class="kt-badge kt-badge-sm kt-badge-success">Active</span>
                                                @else
                                                    <span class="kt-badge kt-badge-sm kt-badge-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('temperature-profiles.edit', $profile->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
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
