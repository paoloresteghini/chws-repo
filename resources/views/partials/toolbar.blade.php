<div class="">
    <!-- Container -->
    <div class="kt-container-fixed">
        <div class="border-t border-border dark:border-coal-100">
        </div>
        <div class="flex items-center justify-between flex-wrap gap-2 la:gap-5 my-5">
            <div class="flex flex-col gap-1">
                <h1 class="font-medium text-lg text-mono">
                    {{ $title }}
                </h1>
                <div class="flex items-center gap-1 text-sm">
                    <a class="text-secondary-foreground hover:text-primary" href="html/demo7.html">
                        {{ $subTitle }}
                    </a>
                </div>
            </div>
            <div class="flex items-center flex-wrap gap-1.5 lg:gap-3.5">
                @if($buttonText && $buttonUrl)
                <a class="kt-btn kt-btn-outline" href="{{ $buttonUrl }}">
                    <i class="ki-filled ki-mouse-circle text-base!">
                    </i>
                    {{ $buttonText }}
                </a>
                    @endif
            </div>
        </div>
        <div class="border-b border-border mb-5 lg:mb-7.5">
        </div>
    </div>
</div>
