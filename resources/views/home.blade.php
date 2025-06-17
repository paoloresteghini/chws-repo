@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Products',
        'subTitle' => 'Create and manage your products',
        'buttonText' => 'Create Product',
        'buttonUrl' => '#',
    ])
    <main class="grow" id="content" role="content">
    <div class="kt-container-fixed" id="contentContainer">
    </div>
    <div class="kt-container-fixed">
        <div class="grid gap-5 lg:gap-7.5">
            <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5 items-stretch">
                <div class="lg:col-span-3">
                    <div class="grid">
                        <div class="kt-card kt-card-grid h-full min-w-full">
                            <div class="kt-card-header">
                                <h3 class="kt-card-title">
                                    Teams
                                </h3>
                                <div class="kt-input max-w-48">
                                    <i class="ki-filled ki-magnifier">
                                    </i>
                                    <input data-kt-datatable-search="#kt_datatable_1" placeholder="Search Teams" type="text">
                                    </input>
                                </div>
                            </div>
                            <div class="kt-card-table">
                                <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="5" id="teams_datatable">
                                    <div class="kt-scrollable-x-auto">
                                        <table class="kt-table kt-table-border table-fixed" data-kt-datatable-table="true" id="kt_datatable_1">
                                            <thead>
                                            <tr>
                                                <th class="w-[50px]">
                                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-check="true" type="checkbox">
                                                    </input>
                                                </th>
                                                <th class="w-[280px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Team
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                                </th>
                                                <th class="w-[125px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Rating
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                                </th>
                                                <th class="w-[135px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Last Modified
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                                </th>
                                                <th class="w-[125px]">
                 <span class="kt-table-col">
                  <span class="kt-table-col-label">
                   Members
                  </span>
                  <span class="kt-table-col-sort">
                  </span>
                 </span>
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>
                                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="1">
                                                    </input>
                                                </td>
                                                <td>
                                                    <div class="flex flex-col gap-2">
                                                        <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                                                            Product Management
                                                        </a>
                                                        <span class="text-2sm text-secondary-foreground font-normal leading-3">
                   Product development & lifecycle
                  </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="kt-rating">
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    21 Oct, 2024
                                                </td>
                                                <td>
                                                    <div class="flex -space-x-2">
                                                        <div class="flex">
                                                            <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-[30px]" src="assets/media/avatars/300-4.png"/>
                                                        </div>
                                                        <div class="flex">
                                                            <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-[30px]" src="assets/media/avatars/300-1.png"/>
                                                        </div>
                                                        <div class="flex">
                                                            <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-[30px]" src="assets/media/avatars/300-2.png"/>
                                                        </div>
                                                        <div class="flex">
                   <span class="relative inline-flex items-center justify-center shrink-0 rounded-full ring-1 font-semibold leading-none text-2xs size-[30px] text-white ring-background bg-green-500">
                    +10
                   </span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="2">
                                                    </input>
                                                </td>
                                                <td>
                                                    <div class="flex flex-col gap-2">
                                                        <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                                                            Marketing Team
                                                        </a>
                                                        <span class="text-2sm text-secondary-foreground font-normal leading-3">
                   Campaigns & market analysis
                  </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="kt-rating">
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label indeterminate">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none" style="width: 50.0%">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    15 Oct, 2024
                                                </td>
                                                <td>
                                                    <div class="flex -space-x-2">
                                                        <div class="flex">
                                                            <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-[30px]" src="assets/media/avatars/300-4.png"/>
                                                        </div>
                                                        <div class="flex">
                   <span class="hover:z-5 relative inline-flex items-center justify-center shrink-0 rounded-full ring-1 font-semibold leading-none text-2xs size-[30px] uppercase text-white ring-background bg-yellow-500">
                    g
                   </span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="3">
                                                    </input>
                                                </td>
                                                <td>
                                                    <div class="flex flex-col gap-2">
                                                        <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                                                            HR Department
                                                        </a>
                                                        <span class="text-2sm text-secondary-foreground font-normal leading-3">
                   Talent acquisition, employee welfare
                  </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="kt-rating">
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    10 Oct, 2024
                                                </td>
                                                <td>
                                                    <div class="flex -space-x-2">
                                                        <div class="flex">
                                                            <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-[30px]" src="assets/media/avatars/300-4.png"/>
                                                        </div>
                                                        <div class="flex">
                                                            <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-[30px]" src="assets/media/avatars/300-1.png"/>
                                                        </div>
                                                        <div class="flex">
                                                            <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-[30px]" src="assets/media/avatars/300-2.png"/>
                                                        </div>
                                                        <div class="flex">
                   <span class="relative inline-flex items-center justify-center shrink-0 rounded-full ring-1 font-semibold leading-none text-2xs size-[30px] text-white ring-background bg-violet-500">
                    +A
                   </span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="4">
                                                    </input>
                                                </td>
                                                <td>
                                                    <div class="flex flex-col gap-2">
                                                        <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                                                            Sales Division
                                                        </a>
                                                        <span class="text-2sm text-secondary-foreground font-normal leading-3">
                   Customer relations, sales strategy
                  </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="kt-rating">
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                        <div class="kt-rating-label checked">
                                                            <i class="kt-rating-on ki-solid ki-star text-base leading-none">
                                                            </i>
                                                            <i class="kt-rating-off ki-outline ki-star text-base leading-none">
                                                            </i>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    05 Oct, 2024
                                                </td>
                                                <td>
                                                    <div class="flex -space-x-2">
                                                        <div class="flex">
                                                            <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-[30px]" src="assets/media/avatars/300-24.png"/>
                                                        </div>
                                                        <div class="flex">
                                                            <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-[30px]" src="assets/media/avatars/300-7.png"/>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
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
            </div>
        </div>
    </div>
    </main>
@endsection
