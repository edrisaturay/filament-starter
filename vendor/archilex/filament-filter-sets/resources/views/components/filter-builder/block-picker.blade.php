@php
    use Filament\Support\Enums\GridDirection;
    use Illuminate\Support\Js;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'action',
    'afterItem' => null,
    'blocks',
    'columns' => null,
    'key',
    'trigger',
    'width' => null,
    'maxHeight' => null,
    'hasSearch' => false,
])

<div
    x-data="{
        search: '',
        filters: {{ Js::from(collect($blocks)->map(fn ($block) => strtolower($block->getLabel()))->values()) }}
    }"
    wire:ignore
>
    <x-filament::dropdown
        :max-height="$maxHeight"    
        :width="$width"
        {{ $attributes->class(['fi-fo-builder-block-picker']) }}
    >
        <x-slot name="trigger">
            {{ $trigger }}
        </x-slot>

        @if ($hasSearch)
            <x-filament::dropdown.list>
                <div class="items-center gap-x-2 px-2 flex">    
                    <x-filament::icon 
                        icon="heroicon-o-magnifying-glass" 
                        class="fi-input-wrp-icon flex-shrink-0 h-5 w-5 text-gray-400 dark:text-gray-500"
                    />
                    <x-filament::input
                        autocomplete="off"
                        inline-prefix
                        :placeholder="__('filament-panels::global-search.field.placeholder')"
                        type="search"
                        x-bind:id="$id('input')"
                        x-model="search"
                    />
                </div>
            </x-filament::dropdown.list>
        @endif

        <x-filament::dropdown.list>
            <div
                {{ (new ComponentAttributeBag)->grid($columns, GridDirection::Column) }}
            >
                @foreach ($blocks as $block)
                    @php
                        $blockIcon = $block->getIcon();
                        $blockLabel = $block->getLabel();

                        $wireClickActionArguments = ['block' => $block->getName()];

                        if (filled($afterItem)) {
                            $wireClickActionArguments['afterItem'] = $afterItem;
                        }

                        $wireClickActionArguments = \Illuminate\Support\Js::from($wireClickActionArguments);

                        $wireClickAction = "mountAction('{$action->getName()}', {$wireClickActionArguments}, { schemaComponent: '{$key}' })";
                    @endphp

                    <x-filament::dropdown.list.item
                        x-show="
                            const label = '{{ $blockLabel }}';
                            return label.toLowerCase().includes(search.toLowerCase())
                        "
                        :icon="$blockIcon"
                        x-on:click="close; setTimeout(() => {search = ''}, 500);"
                        :wire:click="$wireClickAction"
                    >
                        {{ $blockLabel }}
                    </x-filament::dropdown.list.item>
                @endforeach
            </div>

            @if ($hasSearch)
                <x-filament::dropdown.list.item
                    x-show="! filters.filter(filter => filter.includes(search.toLowerCase())).length"
                >
                    {{ __('filament-panels::global-search.no_results_message') }}
                </x-filament::dropdown.list.item>             
            @endif
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>

