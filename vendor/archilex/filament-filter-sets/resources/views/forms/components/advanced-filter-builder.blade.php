<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $containers = $getChildComponentContainers();
        $blockPickerColumns = $getBlockPickerColumns();
        $blockPickerWidth = $getBlockPickerWidth();
        $blockPickerMaxHeight = $getBlockPickerMaxHeight();
        $blockPickerHasSearch = $blockPickerHasSearch();

        $addAction = $getAction($getAddActionName());
        $deleteAction = $getAction($getDeleteActionName());
        $deleteIconAction = $getAction('deleteIcon');
        $expandViewAction = $getAction('expandView');

        $addActionComponentName = $addAction->getSchemaComponent()->getName();

        $isDeletable = $isDeletable();
        $hasWideFilterLayout = $hasWideFilterLayout();
        $isOrGroup = $addActionComponentName === 'or_group';

        $key = $getKey();

        $expandViewStyles = \Illuminate\Support\Arr::toCssStyles(Archilex\AdvancedTables\Support\Config::getExpandViewStyles());
    @endphp

    @if ($isOrGroup && ! $hasWideFilterLayout)
        <span 
            x-on:click="close"
            class="absolute"
            style="{{ $expandViewStyles }}"
        >
            {{ $expandViewAction }}
        </span>
    @endif

    <div
        x-data="{}"
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['fi-fo-builder grid gap-y-6 mt-2'])
        }}
    >
        @if (count($containers))
            <ul class="space-y-6">
                @foreach ($containers as $uuid => $item)
                    <li
                        wire:ignore.self
                        wire:key="{{ $item->getLivewireKey() }}.item"
                        class="relative rounded-xl bg-white dark:bg-gray-900"
                    >
                        @if (! $isOrGroup)
                            <span 
                                @class([
                                    'absolute end-0 -top-[1px]',
                                    'sm:hidden' => $hasWideFilterLayout,
                                ])
                            >
                                {{ $deleteAction(['item' => $uuid]) }}
                            </span>
                        @endif

                        <div
                            @class([
                                'sm:flex items-end gap-4 dark:border-white/10' => ! $isOrGroup
                            ])
                        >
                            <div class="w-full">
                                {{ $item }}
                            </div>
                            
                            @if (! $isOrGroup && $hasWideFilterLayout)
                                <div 
                                    @class([
                                        'hidden sm:block sm:mb-2',
                                        '-me-[6px]' => $isModalLayout()
                                    ])
                                >                                    
                                    {{ $deleteIconAction(['item' => $uuid]) }}
                                </div>
                            @endif
                        </div>
                    </li>

                    @if ($isOrGroup && ! $loop->last)
                        <li class="relative flex items-center text-sm -mx-6">
                            <span class="flex-grow border-t border-gray-200 dark:border-gray-600"></span>
                            <span class="flex-shrink text-gray-400 mx-2">
                                {{ __('advanced-tables::filter-builder.form.or') }}
                            </span>
                            <span class="flex-grow border-t border-gray-200 dark:border-gray-600"></span>
                        </li>
                    @endif
                    
                @endforeach
            </ul>
        @endif

        @if ($isOrGroup)
            @php
                $block = $getBlocks()[0];

                $wireClickActionArguments = ['block' => $block->getName()];

                $wireClickActionArguments = \Illuminate\Support\Js::from($wireClickActionArguments);

                $wireClickAction = "mountAction('{$addAction->getName()}', {$wireClickActionArguments}, { schemaComponent: '{$key}' })";
            @endphp

            @if (! count($containers))
                <div class="flex justify-center">
                    <x-filament::button
                        :icon="$block->getIcon()"
                        color="gray"
                        :wire:click="$wireClickAction"
                    >
                        {{ $addAction->getLabel() }}
                    </x-filament::button>
                </div>
            @endif            
        @else
            <x-advanced-tables::filter-builder.block-picker
                :action="$addAction"
                :blocks="$getBlocks()"
                :key="$key"
                :width="$blockPickerWidth"
                :columns="$blockPickerColumns"
                :max-height="$blockPickerMaxHeight"
                :has-search="$blockPickerHasSearch"
                class="flex justify-center"
            >
                <x-slot name="trigger">
                    {{ $addAction }}
                </x-slot>
            </x-advanced-tables::filter-builder.block-picker>
        @endif
    </div>
</x-dynamic-component>
