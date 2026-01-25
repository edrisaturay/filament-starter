@php
    use Filament\Support\Facades\FilamentView;

    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributes = $getExtraAttributes();
    $extraInputAttributeBag = $getExtraInputAttributeBag();
    $color = $getColor() ?? 'primary';
    $hasInlineLabel = $hasInlineLabel();
    $id = $getId();
    $isAutofocused = $isAutofocused();
    $isDisabled = $isDisabled();
    $isPrefixInline = $isPrefixInline();
    $isReorderable = (! $isDisabled) && $isReorderable();
    $isSuffixInline = $isSuffixInline();
    $placeholder = $getPlaceholder();
    $prefixActions = $getPrefixActions();
    $prefixIcon = $getPrefixIcon();
    $prefixIconColor = $getPrefixIconColor();
    $prefixLabel = $getPrefixLabel();
    $statePath = $getStatePath();
    $suffixActions = $getSuffixActions();
    $suffixIcon = $getSuffixIcon();
    $suffixIconColor = $getSuffixIconColor();
    $suffixLabel = $getSuffixLabel();
@endphp

<x-dynamic-component 
    :component="$fieldWrapperView"
    :field="$field"
    :has-inline-label="$hasInlineLabel"
    class="advanced-tables-summary fi-fo-tags-input-wrp"
>
    <x-filament::input.wrapper
        :disabled="$isDisabled"
        :inline-prefix="$isPrefixInline"
        :inline-suffix="$isSuffixInline"
        :prefix="$prefixLabel"
        :prefix-actions="$prefixActions"
        :prefix-icon="$prefixIcon"
        :prefix-icon-color="$prefixIconColor"
        :suffix="$suffixLabel"
        :suffix-actions="$suffixActions"
        :suffix-icon="$suffixIcon"
        :suffix-icon-color="$suffixIconColor"
        :valid="! $errors->has($statePath)"
        :attributes="
            \Filament\Support\prepare_inherited_attributes($attributes)
                ->merge($extraAttributes, escape: false)
                ->class([
                    'fi-fo-tags-input',
                    'fi-disabled' => $isDisabled,
                ])
        "
        >
        <div
            @if (FilamentView::hasSpaMode())
                {{-- format-ignore-start --}}x-load="visible || event (x-modal-opened)"{{-- format-ignore-end --}}
            @else
                x-load
            @endif
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('tags-input', 'filament/forms') }}"
            x-data="tagsInputFormComponent({
                        state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
                        splitKeys: @js($getSplitKeys()),
                    })"
            {{ $getExtraAlpineAttributeBag() }}
        >
            <datalist id="{{ $id }}-suggestions">
                @foreach ($getSuggestions() as $suggestion)
                    <template
                        x-bind:key="@js($suggestion)"
                        x-if="! state.includes(@js($suggestion))"
                    >
                        <option value="{{ $suggestion }}" />
                    </template>
                @endforeach
            </datalist>

            <div wire:ignore>
                <template x-cloak x-if="state?.length">
                    <div class="fi-fo-tags-input-tags-ctn">
                        <template
                            x-for="(tag, index) in state"
                            x-bind:key="`${tag}-${index}`"
                        >
                            <x-filament::badge>
                                {{ $getTagPrefix() }}

                                <span x-text="tag"></span>

                                {{ $getTagSuffix() }}

                                @if (! $isDisabled)
                                    <x-slot
                                        name="deleteButton"
                                        x-on:click.stop="deleteTag(tag)"
                                    ></x-slot>
                                @endif
                            </x-filament::badge>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </x-filament::input.wrapper>
</x-dynamic-component>
