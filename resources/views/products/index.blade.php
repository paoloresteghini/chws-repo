@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Products',
        'subTitle' => 'Create and manage your products',
        'buttonText' => 'Create Product',
        'buttonUrl' => "/products/create",
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed" id="contentContainer">
        </div>
        <div class="kt-container-fixed">
            <div class="grid gap-5 lg:gap-7.5">
                <div class="kt-card kt-card-grid h-full min-w-full">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">
                            System Products
                        </h3>

                    </div>
                    <div class="kt-card-table">
                        <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="5" id="products_datatable">
                            <div class="kt-scrollable-x-auto">
                                <table class="kt-table kt-table-border table-fixed" data-kt-datatable-table="true" id="products_table">
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
                                        <th class="w-[400px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Description
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                        </th>
                                        <th class="w-[135px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Created At
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
                                    @foreach($products as $product)
                                        <tr>
                                            <td>
                                                <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="1">
                                                </input>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-2">
                                                    <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                                                        {{ $product->name }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-sm text-muted-foreground">
                                                    {{ Str::limit($product->description, 100) }}
                                                </div>
                                            </td>
                                            <td>
                                                {{ $product->created_at->format('d M Y') }}
                                            </td>
                                            <td>
                                                <a href="{{ route('products.edit', $product->id) }}" class="kt-btn kt-btn-sm kt-btn-primary">
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
