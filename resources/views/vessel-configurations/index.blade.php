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
                                        <th class="w-[50px]">
                                            <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-check="true" type="checkbox">
                                            </input>
                                        </th>
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
                                                <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="1">
                                                </input>
                                            </td>
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
                                                    <span class="kt-badge kt-badge-sm kt-badge-info">
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
