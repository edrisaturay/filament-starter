<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $items = $getItems();
        $blockPickerBlocks = $getBlockPickerBlocks();
        $blockPickerColumns = $getBlockPickerColumns();
        $blockPickerWidth = $getBlockPickerWidth();
        $blockPickerMaxHeight = $getBlockPickerMaxHeight();
        $blockPickerHasSearch = $blockPickerHasSearch();

        $addAction = $getAction($getAddActionName());
        $deleteAction = $getAction($getDeleteActionName());
        $sortAscendingAction = $getAction('sortAscending');
        $sortDescendingAction = $getAction('sortDescending');

        $key = $getKey();
        $statePath = $getStatePath();
    @endphp

    <div
        x-data="{}"
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class(['fi-fo-builder grid gap-y-6 mt-2'])
        }}
    >
        @if (count($items))
            <ul
                x-sortable
                data-sortable-animation-duration="{{ $getReorderAnimationDuration() }}"
                wire:end.stop="mountAction('reorder', { items: $event.target.sortable.toArray() }, { schemaComponent: '{{ $key }}' })"
                class="space-y-2"
            >
                @foreach ($items as $itemKey => $item)
                    <li
                        wire:ignore.self
                        wire:key="{{ $item->getLivewireKey() }}.item"
                        x-sortable-item="{{ $itemKey }}"
                        class="flex items-center justify-between px-3 py-1 -mx-3 gap-x-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-white/5 hover:rounded-lg"
                    >
                        <div 
                            x-sortable-handle
                            x-on:click.stop    
                            class="flex flex-1 h-9 gap-x-3 items-center font-medium text-gray-950 dark:text-white cursor-move"
                        >
                            <x-filament::icon
                                icon="heroicon-o-bars-2"
                                class="h-5 w-5 text-gray-400 dark:text-gray-500"
                            />
                            {{ $item->getParentComponent()->getLabel() }}
                        </div>
                        <div class="flex gap-x-3">
                            {{ $sortAscendingAction(['item' => $itemKey]) }}
                            {{ $sortDescendingAction(['item' => $itemKey]) }}
                            {{ $deleteAction(['item' => $itemKey]) }}
                        </div>
                    </li>                    
                @endforeach
            </ul>
        @endif

        <x-advanced-tables::filter-builder.block-picker
            :action="$addAction"
            :blocks="$blockPickerBlocks"
            :columns="$blockPickerColumns"
            :key="$key"
            :state-path="$statePath"
            :width="$blockPickerWidth"
            :max-height="$blockPickerMaxHeight"
            :has-search="$blockPickerHasSearch"
            class="flex justify-center"
        >
            <x-slot name="trigger">
                {{ $addAction }}
            </x-slot>
        </x-advanced-tables::filter-builder.block-picker>
    </div>
</x-dynamic-component>
