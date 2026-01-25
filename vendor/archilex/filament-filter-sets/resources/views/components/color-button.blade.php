@php
    use Filament\Support\Enums\Size;
    use Filament\Support\Enums\IconSize;
    use Filament\Support\View\Components\ButtonComponent;
    use Filament\Support\Icons\Heroicon;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'color' => 'primary',
    'disabled' => false,
    'label' => null,
    'size' => Size::Medium,
])

<x-filament::button
    :color="$color"
    :disabled="$disabled"
    :size="$size"
    {{
        $attributes
            ->class([
                'advanced-tables-color-button advanced-tables-color-button-size-' . $size->value,
                'bg-gray-300 hover:bg-gray-200 focus-visible:ring-gray-200/50 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-100 dark:focus-visible:ring-gray-500/50' => $color === 'gray',
                'bg-gray-950 hover:bg-gray-800 focus-visible:ring-gray-800/50 dark:bg-white dark:hover:bg-gray-200 dark:text-gray-100 dark:focus-visible:ring-gray-200/50' => $color === 'black',
            ])
    }}
>
    @if ($label)
        <span class="sr-only">
            {{ $label }}
        </span>
    @endif

    @php
        $iconSize = match ($size) {
            Size::ExtraSmall => IconSize::ExtraSmall,
            Size::Small => IconSize::Small,
            Size::Medium => IconSize::Medium,
            Size::Large => IconSize::Large,
            Size::ExtraLarge => IconSize::ExtraLarge,
        };
    @endphp

    {{
        \Filament\Support\generate_icon_html(Heroicon::Check, attributes: (new ComponentAttributeBag([
            'x-show' => "state === '{$color}' || '{$color}' === 'none'",
        ]))->class(['advanced-tables-color-button-icon text-white']), size: $iconSize)
    }}
</x-filament::button>
