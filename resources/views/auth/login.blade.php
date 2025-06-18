@extends('layouts.auth')

@section('content')

    <div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
        <div class="kt-card max-w-[370px] w-full">
                <form method="POST" class="kt-card-content flex flex-col gap-5 p-10" action="{{ route('login') }}">
                    @csrf
                <div class="text-center mb-2.5">
                    <h3 class="text-lg font-medium text-mono leading-none mb-2.5">
                        Sign in
                    </h3>
                    <div class="flex items-center justify-center font-medium">
       <span class="text-sm text-secondary-foreground me-1.5">
        Need an account?
       </span>

                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono">
                        Email
                    </label>
                    <input id="email" type="email" class="kt-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                    @error('email')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror
                </div>
                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between gap-1">
                        <label class="kt-form-label font-normal text-mono">
                            Password
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-sm kt-link shrink-0" href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        @endif
                    </div>
                    <div class="kt-input" data-kt-toggle-password="true">
                        <input id="password" type="password" class=" @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                        @error('password')
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                        @enderror
                        <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button">
        <span class="kt-toggle-password-active:hidden">
         <i class="ki-filled ki-eye text-muted-foreground">
         </i>
        </span>
                            <span class="hidden kt-toggle-password-active:block">
         <i class="ki-filled ki-eye-slash text-muted-foreground">
         </i>
        </span>
                        </button>
                    </div>
                </div>
                <div class="kt-form-item">
                    <div class="kt-form-control">
                        <label class="kt-form-label">
                            <input class="kt-checkbox kt-checkbox-sm" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span class="kt-checkbox-label">
               Remember me
              </span>
                        </label>
                    </div>
                </div>

                <button class="kt-btn kt-btn-primary flex justify-center grow">
                    Sign In
                </button>
            </form>
        </div>
    </div>




@endsection
