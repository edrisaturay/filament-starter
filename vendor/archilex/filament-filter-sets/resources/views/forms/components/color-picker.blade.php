@php
    use Filament\Support\Facades\FilamentView;
    use Filament\Support\Enums\Alignment;
    use Filament\Support\Enums\Size;
    use Filament\Support\Enums\IconSize;
    use Filament\Support\View\Components\ButtonComponent;
    use Filament\Support\Icons\Heroicon;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'actions',
    'color' => null,
    'label' => __('filament-support::actions/group.trigger.label'),
])

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :hint="$getHint()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div
        x-data="{
            state: $wire.entangle('{{ $getStatePath() }}')
        }"
        @class([
            'flex flex-wrap items-center ' . $getSpacing(),
            'justify-between' => $getAlignment() === Alignment::Justify,
        ])
    >    
        @php
            $size = $getSize();
            $colors = collect(Archilex\AdvancedTables\Support\Config::getQuickSaveColors())
                ->when(! $shouldIncludeGray(), function ($colors) {
                    return $colors->filter(fn ($color) => $color !== 'gray');
                })
                ->toArray();
        @endphp

        @foreach ($colors as $color)   
            <x-advanced-tables::color-button
                x-on:click="
                    state = (state !== '{{ $color }}')
                        ? '{{ $color }}'
                        : null
                "
                :color="$color"
                :size="$size"
            />
        @endforeach

        @if ($shouldIncludeBlack())
            <x-advanced-tables::color-button
                x-on:click="
                    state = (state !== 'black')
                        ? 'black'
                        : null
                "
                color="black"
                :size="$size"
            />
        @endif

        @if ($shouldIncludeCustomPicker())
            <div class="fi-fo-color-picker">
                <div
                    @if (FilamentView::hasSpaMode())
                        {{-- format-ignore-start --}}x-load="visible || event (x-modal-opened)"{{-- format-ignore-end --}}
                    @else
                        x-load
                    @endif
                    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('color-picker', 'filament/forms') }}"
                    x-data="colorPickerFormComponent({
                                isAutofocused: false,
                                isDisabled: false,
                                isLive: false,
                                isLiveDebounced: false,
                                isLiveOnBlur: false,
                                liveDebounce: false,
                                state: $wire.entangle('{{ $getStatePath() }}'),
                            })"
                    x-on:keydown.esc="isOpen() && $event.stopPropagation()"
                    class="fi-input-wrp-content relative"
                >
                    <div
                        class="advanced-tables-color-button advanced-tables-color-button-size-sm"
                        x-ref="input"
                        x-on:click="togglePanelVisibility()"
                        x-bind:class="{
                            'fi-empty': ! state,
                        }"
                        x-bind:style="{ 'background-color': ['gray', 'black'].includes(state) ? '' : state }"
                    >
                        {{
                            \Filament\Support\generate_icon_html(Heroicon::Check, attributes: (new ComponentAttributeBag([
                                'x-show' => "String(state || '').startsWith('#')",
                            ]))->class(['advanced-tables-color-button-icon text-white']), size: IconSize::Small)
                        }}
                    </div>

                    <div
                        x-on:click="togglePanelVisibility()"
                        class="absolute top-0 left-0"
                    >
                        {{
                            \Filament\Support\generate_icon_html(Heroicon::OutlinedSwatch, attributes: (new ComponentAttributeBag([
                                'x-show' => "!String(state || '').startsWith('#')",
                            ]))->class(['text-gray-500']), size: IconSize::ExtraLarge)
                        }}
                    </div>

                    <div
                        wire:ignore.self
                        wire:key="{{ $getLivewireKey() }}.panel"
                        x-cloak
                        x-float.placement.bottom-start.offset.flip.shift="{ offset: 8 }"
                        x-ref="panel"
                        class="fi-fo-color-picker-panel"
                    >
                        <hex-color-picker color="{{ $getState() }}" />
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-dynamic-component>
