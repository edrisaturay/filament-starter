@php
    use Archilex\AdvancedTables\Enums\FavoritesBarTheme;
    use Archilex\AdvancedTables\Support\Config;
    use Filament\Support\Enums\IconPosition;
    use Filament\Support\Enums\Size;
    use Filament\Support\Enums\IconSize;
@endphp

@props([
    'badge' => null,
    'badgeColor' => null,
    'userView' => null,
    'presetViewName' => null,
    'theme' => FavoritesBarTheme::Links,
    'icon' => null,
    'tooltip' => null,
    'iconPosition' => IconPosition::Before,
    'size' => Size::Medium,
    'color' => null,
])

@php
    $size = match ($size) {
        Size::Small, 'sm' => Size::Small,
        Size::Medium, 'md' => Size::Medium,
    };

    $iconPosition = match ($iconPosition) {
        IconPosition::Before, 'before' => IconPosition::Before,
        IconPosition::After, 'after' => IconPosition::After,
    };
    
    $iconSize ??= match ($size) {
        Size::Small, 'sm' => IconSize::Small,
        Size::Medium, 'md' => IconSize::Medium,
    };

    $lockIconSize ??= match ($size) {
        Size::Small, 'sm' => IconSize::Small,
        Size::Medium, 'md' => IconSize::Medium,
    };

    $lockIcon = Config::getPresetViewLockIcon();

    $iconSize = match ($iconSize) {
        IconSize::Small => 'h-4 w-4',
        IconSize::Medium => 'h-5 w-5',
    };

    $iconClasses = \Illuminate\Support\Arr::toCssClasses([
        'advanced-tables-fav-bar-item-button-icon shrink-0',
        match (true) {
            $iconPosition === IconPosition::Before && $size === Size::Small => 'me-1 -ms-1',
            $iconPosition === IconPosition::Before && $size === Size::Medium => 'me-1 -ms-1',
            $iconPosition === IconPosition::After && $size === Size::Small => 'ms-1 -me-1',
            $iconPosition === IconPosition::After && $size === Size::Medium => 'ms-1 -me-1',
        }
    ]);

    $lockIconSize = match ($lockIconSize) {
        IconSize::Small => 'h-3 w-3',
        IconSize::Medium => 'h-4 w-4',
    };

    $lockIconClasses = \Illuminate\Support\Arr::toCssClasses([
        'advanced-tables-fav-bar-item-button-lock-icon opacity-40 shrink-0',
        match (true) {
            $iconPosition === IconPosition::Before && $size === Size::Small => 'ms-1 -me-1',
            $iconPosition === IconPosition::Before && $size === Size::Medium => 'ms-1 -me-1',
            $iconPosition === IconPosition::After && $size === Size::Small => 'me-1 -ms-1',
            $iconPosition === IconPosition::After && $size === Size::Medium => 'me-1 -ms-1',
        }
    ]);

    if ($theme === FavoritesBarTheme::Links) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'border-b-2 font-medium',
            match (true) {
                $size === Size::Small && $presetViewName && $lockIcon => 'pr-2',
                $size === Size::Medium && $presetViewName && $lockIcon => 'pr-2.5',
                default => 'pr-1',
            },
            match (true) {
                $size === Size::Small && $icon => 'min-h-[2rem] pl-2 text-sm',
                $size === Size::Medium && $icon => 'min-h-[2.25rem] pl-2.5 text-sm',
                $size === Size::Small && ! $icon => 'min-h-[2rem] pl-1 text-sm',
                $size === Size::Medium && ! $icon => 'min-h-[2.25rem] pl-1 text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'border-primary-500 text-primary-600 dark:border-primary-300 dark:text-primary-300',
                default => "fi-color fi-color-{$color} border-color-500 text-color-600 dark:border-color-300 dark:text-color-300",
            }
        ]);

        $currentClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'border-gray-500 text-gray-600 dark:border-gray-300 dark:text-gray-300',
                default => "fi-color fi-color-{$color} border-color-500 text-color-600 dark:border-color-300 dark:text-color-300",
            }
        ]);

        $inActiveClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-500 focus:border-gray-500 dark:text-gray-400 dark:hover:border-gray-300 dark:hover:text-gray-300 dark:focus:border-gray-300',
                default => "fi-color fi-color-{$color} border-transparent text-color-500 hover:text-color-600 hover:border-color-500 focus:border-color-500 dark:text-color-400 dark:hover:border-color-300 dark:hover:text-color-300 dark:focus:border-color-300",
            }
        ]);
    } elseif ($theme === FavoritesBarTheme::SimpleLinks) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'font-medium',
            match (true) {
                $size === Size::Small && $presetViewName && $lockIcon => 'pr-1.5',
                $size === Size::Medium && $presetViewName && $lockIcon => 'pr-2',
                default => 'pr-1',
            },
            match (true) {
                $size === Size::Small && $icon => 'min-h-[2rem] pl-2 text-sm',
                $size === Size::Medium && $icon => 'min-h-[2.25rem] pl-2.5 text-sm',
                $size === Size::Small && ! $icon => 'min-h-[2rem] text-sm',
                $size === Size::Medium && ! $icon => 'min-h-[2.25rem] text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'text-primary-600 dark:text-primary-300',
                default => "fi-color fi-color-{$color} text-color-600 dark:text-color-300",
            }
        ]);

        $currentClasses = \Illuminate\Support\Arr::toCssClasses([
            'text-gray-600 dark:text-gray-300'
        ]);

        $inActiveClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color)=> 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                default => "fi-color fi-color-{$color} text-color-500 hover:text-color-600 focus:border-color-500 dark:text-color-400 dark:hover:text-color-300",
            }
        ]);

    } elseif ($theme === FavoritesBarTheme::Tabs) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'font-medium rounded-lg',
            match ($size) {
                Size::Small => 'min-h-[2rem] px-3 text-sm',
                Size::Medium => 'min-h-[2.25rem] px-4 text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'bg-gray-200/50 text-gray-800 dark:bg-gray-700/50 dark:text-gray-100',
                default => "fi-color fi-color-{$color} bg-color-500 text-white",
            }
        ]);

        $currentClasses = \Illuminate\Support\Arr::toCssClasses([
            'bg-gray-200/50 text-gray-500 dark:bg-gray-800/50 dark:text-gray-400'
        ]);

        $inActiveClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'text-gray-500 hover:text-gray-800 hover:bg-gray-200/50 focus:bg-gray-200/50 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700/50 dark:focus:bg-gray-700/50',
                default => "fi-color fi-color-{$color} text-color-500 hover:text-white hover:bg-color-500 focus:bg-color-500 focus:text-white",
            }
        ]);

    } elseif ($theme === FavoritesBarTheme::BrandedTabs) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'font-medium rounded-lg',
            match ($size) {
                Size::Small => 'min-h-[2rem] px-3 text-sm',
                Size::Medium => 'min-h-[2.25rem] px-4 text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'bg-primary-500 text-white dark:bg-white/5',
                default => "fi-color fi-color-{$color} bg-color-500 text-white",
            }
        ]);

        $currentClasses = \Illuminate\Support\Arr::toCssClasses([
            'bg-gray-200/50 text-gray-500 dark:bg-gray-800/50 dark:text-gray-400'
        ]);

        $inActiveClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'text-gray-500 hover:text-gray-800 hover:bg-gray-200/50 focus:bg-gray-200/50 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-white/5 dark:focus:bg-white/5',
                default => "fi-color fi-color-{$color} text-color-500 hover:text-white hover:bg-color-500 focus:bg-color-500 focus:text-white",
            }
        ]);

    } elseif ($theme === FavoritesBarTheme::Github) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'relative mb-2 rounded-lg',
            match ($size) {
                Size::Small => 'min-h-[2.25rem] px-2.5 text-sm',
                Size::Medium => 'min-h-[2.25rem] px-3 text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            'text-gray-800 font-semibold hover:bg-gray-200/50 dark:text-gray-100 dark:hover:bg-gray-800/50 after:rounded-full after:-bottom-2 after:absolute after:w-full after:content[""] after:h-0.5 after:right-0',
            match (true) {
                blank($color) => 'after:bg-primary-500',
                default => "fi-color fi-color-{$color} after:bg-color-500",
            }
        ]);

        $currentClasses = \Illuminate\Support\Arr::toCssClasses([
            'text-gray-800 font-semibold hover:bg-gray-200/50 dark:text-gray-100 dark:hover:bg-gray-800/50 after:rounded-full after:-bottom-2 after:absolute after:w-full after:content[""] after:h-0.5 after:right-0 after:bg-gray-300 dark:after:bg-gray-700',
        ]);

        $inActiveClasses = 'font-medium text-gray-700 hover:bg-gray-200/50 dark:text-gray-100 dark:hover:bg-gray-900';

        $iconClasses = \Illuminate\Support\Arr::toCssClasses([
            'text-gray-400 dark:text-gray-500',
            $iconClasses,
        ]);
    } elseif ($theme === FavoritesBarTheme::Filament) {
        $themeClasses = \Illuminate\Support\Arr::toCssClasses([
            'font-medium gap-x-2 rounded-lg hover:bg-gray-50 focus:bg-gray-50 dark:hover:bg-white/5 dark:focus:bg-white/5',
            match ($size) {
                Size::Small => 'min-h-[2rem] px-3 text-sm',
                Size::Medium => 'min-h-[2.25rem] px-4 text-sm',
            }
        ]);

        $activeClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'advanced-tables-fav-bar-theme-filament',
                default => "fi-color fi-color-{$color} bg-color-50 text-color-600 dark:bg-white/5 dark:text-color-400",
            }
        ]);

        $currentClasses = \Illuminate\Support\Arr::toCssClasses([
            'bg-gray-50 text-gray-500 dark:bg-white/5 dark:text-gray-400',
        ]);

        $inActiveClasses = \Illuminate\Support\Arr::toCssClasses([
            match (true) {
                blank($color) => 'text-gray-500 hover:text-gray-700 focus:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 dark:focus:text-gray-200',
                default => "fi-color fi-color-{$color} text-color-500 hover:text-color-700 focus:text-color-700 dark:text-color-400 dark:hover:text-color-200 dark:focus:text-color-200",
            }
        ]);

        $iconClasses = "advanced-tables-fav-bar-item-button-icon shrink-0";
    }

    $buttonClasses = \Illuminate\Support\Arr::toCssClasses([
        'advanced-tables-fav-bar-item-button flex items-center justify-center gap-1 py-1 transition duration-75 outline-none',
        $themeClasses,
    ]);
@endphp

<li 
    @if (filled($tooltip))
        x-data="{}"
        x-tooltip="{
            content: @js($tooltip),
            theme: $store.theme,
        }"
    @endif
    wire:key="{{ $userView ? $userView->id : $presetViewName }}"
    {{ $attributes->merge(['class' => 'advanced-tables-fav-bar-item']) }}
>
    <button
        type="button"
        @if (filled($presetViewName))
            x-on:click="
                $wire.call('loadPresetView', '{{ $presetViewName }}')
            "
        @elseif ($userView)
            x-on:click="
                $wire.call('loadUserView', {{ $userView->id }}, {{ json_encode($userView->filters) }} )
            "
        @endif

        @if (filled($presetViewName))
            :class="
                (! activeUserView) && activePresetView == '{{ $presetViewName }}' ? '{{ $activeClasses }}' :
                ((! activeUserView) && currentPresetView == '{{ $presetViewName }}') ? '{{ $currentClasses }}' : '{{ $inActiveClasses }}'
            "
        @elseif ($userView)
            :class="
                activeUserView == {{ $userView->id }} ? '{{ $activeClasses }}' :
                (currentUserView == {{ $userView->id }}) ? '{{ $currentClasses }}' : '{{ $inActiveClasses }}'
            "
        @endif

        class="{{ $buttonClasses }}"
    >
        @if ($icon && $iconPosition === IconPosition::Before)
            <x-filament::icon
                :icon="$icon"
                :class="$iconClasses . ' ' . $iconSize"
            /> 
        @endif

        @if ($presetViewName && $lockIcon && $iconPosition === IconPosition::After) 
            <x-filament::icon
                :icon="$lockIcon"
                :class="$lockIconClasses . ' ' . $lockIconSize"
            />    
        @endif

        <span
            {!! $theme === FavoritesBarTheme::Github ? 'data-content="' . $slot .'"' : '' !!}
            @class([
                'whitespace-nowrap',
                'before:block before:content-[attr(data-content)] before:font-bold before:h-0 before:invisible' => $theme === FavoritesBarTheme::Github,
            ])
        >
            {{ $slot }}
        </span>

        @if ($icon && $iconPosition === IconPosition::After)
            <x-filament::icon
                :icon="$icon"
                :class="$iconClasses . ' ' . $iconSize"
            /> 
        @endif

        @if ($presetViewName && $lockIcon && $iconPosition === IconPosition::Before) 
            <x-filament::icon
                :icon="$lockIcon"
                :class="$lockIconClasses . ' ' . $lockIconSize"
            />   
        @endif

        @if (filled($badge) && $theme === FavoritesBarTheme::Github)
            <div class="bg-gray-300/50 text-xs font-medium px-2 py-0.5 rounded-xl flex items-center justify-center ms-1 -me-1 dark:bg-gray-800">
                {{ $badge }}
            </div>
        @elseif (filled($badge))
            <x-filament::badge 
                class="ms-1"
                size="sm"
                color="{{ $theme === FavoritesBarTheme::Tabs ? ($badgeColor ?? 'gray') : ($badgeColor ?? 'primary') }}"
            >
                {{ $badge }}
            </x-filament::badge>
        @endif
    </button>
</li>
