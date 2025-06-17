@extends('layouts.app')
@section('content')
    @include('partials.toolbar', [
        'title' => 'Users',
        'subTitle' => 'Create and manage your users',
        'buttonText' => 'Previous',
        'buttonUrl' => "/users",
    ])
    <main class="grow" id="content" role="content">
        <div class="kt-container-fixed" id="contentContainer">
        </div>
        <div class="kt-container-fixed">
            @include('partials.errorsbag')
            <div class="grid gap-5 lg:gap-7.5">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="kt-card kt-card-grid h-full min-w-full">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">
                            User Details
                        </h3>
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check-circle">
                            </i>
                            Save
                        </button>
                    </div>
                    <div class="kt-card-table kt-scrollable-x-auto pb-3">
                        @include('users.form')
                    </div>
                </div>
            </form>
            </div>
        </div>
    </main>
@endsection
