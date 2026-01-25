@php
    use Filament\Support\Facades\FilamentView;
    use Filament\Support\Enums\Size;
    use Filament\Support\Enums\IconSize;
    use Filament\Support\View\Components\ButtonComponent;
    use Filament\Support\Icons\Heroicon;
    use Illuminate\View\ComponentAttributeBag;

    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributeBag = $getExtraAttributeBag();
    $isDisabled = $isDisabled();
    $isLive = $isLive();
    $isLiveOnBlur = $isLiveOnBlur();
    $isLiveDebounced = $isLiveDebounced();
    $isPrefixInline = $isPrefixInline();
    $isSuffixInline = $isSuffixInline();
    $liveDebounce = $getLiveDebounce();
    $prefixActions = $getPrefixActions();
    $prefixIcon = $getPrefixIcon();
    $prefixIconColor = $getPrefixIconColor();
    $prefixLabel = $getPrefixLabel();
    $suffixActions = $getSuffixActions();
    $suffixIcon = $getSuffixIcon();
    $suffixIconColor = $getSuffixIconColor();
    $suffixLabel = $getSuffixLabel();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Center"
>
    <div class="fi-fo-color-picker">
        <div
            @if (FilamentView::hasSpaMode())
                {{-- format-ignore-start --}}x-load="visible || event (x-modal-opened)"{{-- format-ignore-end --}}
            @else
                x-load
            @endif
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('color-picker', 'filament/forms') }}"
            x-data="colorPickerFormComponent({
                        isAutofocused: @js($isAutofocused()),
                        isDisabled: @js($isDisabled),
                        isLive: @js($isLive),
                        isLiveDebounced: @js($isLiveDebounced),
                        isLiveOnBlur: @js($isLiveOnBlur),
                        liveDebounce: @js($liveDebounce),
                        state: $wire.$entangle('{{ $statePath }}'),
                    })"
            x-on:keydown.esc="isOpen() && $event.stopPropagation()"
            {{ $getExtraAlpineAttributeBag()->class(['fi-input-wrp-content relative']) }}
        >
            <div
                class="advanced-tables-color-button advanced-tables-color-button-size-sm"
                x-ref="input"
                x-on:click="togglePanelVisibility()"
                x-bind:class="{
                    'fi-empty': ! state,
                }"
                x-bind:style="{ 'background-color': state }"
            >
                {{
                    \Filament\Support\generate_icon_html(Heroicon::Check, attributes: (new ComponentAttributeBag([
                        'x-show' => "state",
                    ]))->class(['advanced-tables-color-button-icon text-white']), size: IconSize::Small)
                }}
            </div>

            <div
                x-on:click="togglePanelVisibility()"
                class="absolute top-0 left-0"
            >
                {{
                    \Filament\Support\generate_icon_html(Heroicon::OutlinedSwatch, attributes: (new ComponentAttributeBag([
                        'x-show' => "! state",
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
                @php
                    $tag = match ($getFormat()) {
                        'hsl' => 'hsl-string',
                        'rgb' => 'rgb-string',
                        'rgba' => 'rgba-string',
                        default => 'hex',
                    } . '-color-picker';
                @endphp

                <{{ $tag }} color="{{ $getState() }}" />
            </div>
        </div>
    </div>
</x-dynamic-component>
