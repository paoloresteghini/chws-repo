<header class="flex items-center transition-[height] shrink-0 bg-background py-4 lg:py-0 lg:h-(--header-height)" data-kt-sticky="true" data-kt-sticky-class="transition-[height] fixed z-10 top-0 left-0 right-0 shadow-xs backdrop-blur-md bg-background/70" data-kt-sticky-name="header" data-kt-sticky-offset="200px" id="header">
    <!-- Container -->
    <div class="kt-container-fixed flex flex-wrap gap-2 items-center lg:gap-4" id="header_container">
        <!-- Logo -->
        <div class="flex items-stretch gap-10 grow">
            <div class="flex items-center gap-2.5">
                <a href="html/demo7.html">
                    <img class="dark:hidden min-h-[34px]" src="{{asset('assets/media/app/mini-logo-circle-primary.svg')}}"/>
                    <img class="hidden dark:inline-block min-h-[34px]" src="{{asset('assets/media/app/mini-logo-circle-primary-dark.svg')}}"/>
                </a>
                <button class="lg:hidden kt-btn kt-btn-icon kt-btn-ghost" data-kt-drawer-toggle="#mega_menu_container">
                    <i class="ki-filled ki-burger-menu-2">
                    </i>
                </button>
                <h3 class="text-mono text-lg font-medium hidden md:block">
                    CHWS
                </h3>
            </div>
            <!-- Mega Menu -->
            <div class="flex items-stretch" id="megaMenuWrapper">
                <div class="flex items-stretch [--kt-reparent-mode:prepend] lg:[--kt-reparent-mode:prepend] [--kt-reparent-target:body] lg:[--kt-reparent-target:#megaMenuWrapper]" data-kt-reparent="true">
                    <div class="hidden lg:flex lg:items-stretch [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]" data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start fixed z-10 top-0 bottom-0 w-full mr-5 max-w-[250px] p-5 lg:p-0 overflow-auto" id="mega_menu_container">
                        <div class="kt-menu flex-col lg:flex-row gap-5 lg:gap-7.5" data-kt-menu="true" id="mega_menu">

                            <div class="kt-menu-item {{ request()->routeIs('dashboard') ? 'here' : '' }}">
                                <a class="kt-menu-link border-b border-b-transparent kt-menu-item-active:border-b-gray-400 kt-menu-item-here:border-b-gray-400" href="{{ route('dashboard') }}">
                                    <span class="kt-menu-title kt-menu-link-hover:text-mono text-sm text-foreground kt-menu-item-show:text-mono kt-menu-item-here:text-mono kt-menu-item-active:font-medium kt-menu-item-here:font-medium">Dashboard</span>
                                </a>
                            </div>

                            <!-- Products -->
                            <div class="kt-menu-item {{ request()->routeIs('products.*') ? 'here' : '' }}">
                                <a class="kt-menu-link border-b border-b-transparent kt-menu-item-active:border-b-gray-400 kt-menu-item-here:border-b-gray-400" href="{{ route('products.index') }}">
                                    <span class="kt-menu-title kt-menu-link-hover:text-mono text-sm text-foreground kt-menu-item-show:text-mono kt-menu-item-here:text-mono kt-menu-item-active:font-medium kt-menu-item-here:font-medium">Products</span>
                                </a>
                            </div>

                            <!-- Versions -->
                            <div class="kt-menu-item {{ request()->routeIs('versions.*') ? 'here' : '' }}">
                                <a class="kt-menu-link border-b border-b-transparent kt-menu-item-active:border-b-gray-400 kt-menu-item-here:border-b-gray-400" href="{{ route('versions.index') }}">
                                    <span class="kt-menu-title kt-menu-link-hover:text-mono text-sm text-foreground kt-menu-item-show:text-mono kt-menu-item-here:text-mono kt-menu-item-active:font-medium kt-menu-item-here:font-medium">Models</span>
                                </a>
                            </div>




                            <!-- Performance & Configuration Dropdown -->
                            <div class="kt-menu-item {{ request()->routeIs('vessel-configurations.*') || request()->routeIs('performance-data.*') ? 'here' : '' }}"
                                 data-kt-menu-item-offset="0,0|lg:-20px,10px"
                                 data-kt-menu-item-offset-rtl="0,0|lg:20px,10px"
                                 data-kt-menu-item-overflow="true"
                                 data-kt-menu-item-placement="bottom-start"
                                 data-kt-menu-item-placement-rtl="bottom-end"
                                 data-kt-menu-item-toggle="dropdown"
                                 data-kt-menu-item-trigger="click|lg:hover">
                                <div class="kt-menu-link border-b border-b-transparent kt-menu-item-active:border-b-gray-400 kt-menu-item-here:border-b-gray-400">
                        <span class="kt-menu-title text-sm text-foreground kt-menu-item-show:text-mono kt-menu-item-here:text-mono kt-menu-item-active:font-medium kt-menu-item-here:font-medium">
                            Data & Config
                        </span>
                                    <span class="kt-menu-arrow flex lg:hidden">
                            <span class="flex kt-menu-item-show:hidden">
                                <i class="ki-filled ki-plus text-xs text-muted-foreground"></i>
                            </span>
                            <span class="hidden kt-menu-item-show:inline-flex">
                                <i class="ki-filled ki-minus text-xs text-muted-foreground"></i>
                            </span>
                        </span>
                                </div>
                                <div class="kt-menu-dropdown kt-menu-default py-2.5 w-full max-w-[260px]">
                                    <div class="kt-menu-item {{ request()->routeIs('vessel-configurations.*') ? 'here' : '' }}">
                                        <a class="kt-menu-link" href="{{ route('vessel-configurations.index') }}" tabindex="0">
                                <span class="kt-menu-icon">
                                    <i class="ki-filled ki-bucket"></i>
                                </span>
                                            <span class="kt-menu-title grow-0">Vessel Configurations</span>
                                        </a>
                                    </div>
                                    <div class="kt-menu-item {{ request()->routeIs('version-categories.*') ? 'here' : '' }}">
                                        <a class="kt-menu-link" href="{{ route('version-categories.index') }}" tabindex="0">
                                <span class="kt-menu-icon">
                                    <i class="ki-filled ki-category"></i>
                                </span>
                                            <span class="kt-menu-title grow-0">Model Categories</span>
                                        </a>
                                    </div>
                                    <div class="kt-menu-item {{ request()->routeIs('performance-data.*') ? 'here' : '' }}">
                                        <a class="kt-menu-link" href="{{ route('performance-data.index') }}" tabindex="0">
                                <span class="kt-menu-icon">
                                    <i class="ki-filled ki-chart-simple"></i>
                                </span>
                                            <span class="kt-menu-title grow-0">Performance Data</span>
                                        </a>
                                    </div>

                                    <div class="kt-menu-item {{ request()->routeIs('temperature-profiles.index.*') ? 'here' : '' }}">
                                        <a class="kt-menu-link" href="{{ route('temperature-profiles.index') }}" tabindex="0">
                                <span class="kt-menu-icon">
                                    <i class="ki-filled ki-glass"></i>
                                </span>
                                            <span class="kt-menu-title grow-0">Temperature Profiles</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Users -->
                            <div class="kt-menu-item {{ request()->routeIs('users.*') ? 'here' : '' }}">
                                <a class="kt-menu-link border-b border-b-transparent kt-menu-item-active:border-b-gray-400 kt-menu-item-here:border-b-gray-400" href="{{ route('users.index') }}">
                                    <span class="kt-menu-title kt-menu-link-hover:text-mono text-sm text-foreground kt-menu-item-show:text-mono kt-menu-item-here:text-mono kt-menu-item-active:font-medium kt-menu-item-here:font-medium">Users</span>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center flex-wrap gap-3">
            <div data-kt-dropdown="true" data-kt-dropdown-offset="10px, 10px" data-kt-dropdown-offset-rtl="-20px, 10px" data-kt-dropdown-placement="bottom-end" data-kt-dropdown-placement-rtl="bottom-start" data-kt-dropdown-trigger="click">
                <div class="cursor-pointer size-[34px] rounded-full inline-flex items-center justify-center relative text-lg font-medium border border-input bg-accent/60 text-foreground" data-kt-dropdown-toggle="true">
                    {{ auth()->user()->name[0] }}

                </div>
                <div class="kt-dropdown-menu w-[250px]" data-kt-dropdown-menu="true">
                    <div class="flex items-center justify-between px-2.5 py-1.5 gap-1.5">
                        <div class="flex items-center gap-2">
                            <img alt="" class="size-9 shrink-0 rounded-full border-2 border-green-500" src="assets/media/avatars/300-2.png"/>
                            <div class="flex flex-col gap-1.5">
           <span class="text-sm text-foreground font-semibold leading-none">
            {{ auth()->user()->name }}
           </span>
                                <a class="text-xs text-secondary-foreground hover:text-primary font-medium leading-none" href="html/demo7/account/home/get-started.html">
                                    {{ auth()->user()->email }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <ul class="kt-dropdown-menu-sub">

                        <li>
                            <div class="kt-dropdown-menu-separator">
                            </div>
                        </li>
                    </ul>
                    <div class="px-2.5 pt-1.5 mb-2.5 flex flex-col gap-3.5">
                        <div class="flex items-center gap-2 justify-between">
                          <span class="flex items-center gap-2">
                           <i class="ki-filled ki-moon text-base text-muted-foreground">
                           </i>
                           <span class="font-medium text-2sm">
                            Dark Mode
                           </span>
                          </span>
                            <input class="kt-switch" data-kt-theme-switch-state="dark" data-kt-theme-switch-toggle="true" name="check" type="checkbox" value="1"/>
                        </div>
                        <a class="kt-btn kt-btn-outline justify-center w-full" href="html/demo7/authentication/classic/sign-in.html">
                            Log out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
