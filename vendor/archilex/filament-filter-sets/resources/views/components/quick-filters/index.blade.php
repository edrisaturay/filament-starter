@php
    $usesAdvancedTables = method_exists($this, 'bootedAdvancedTables');
@endphp
<div class="fi-ta-filter-indicators">
    <div>
        <span class="fi-ta-filter-indicators-label">
            {{ __('filament-tables::table.filters.indicator') }}
        </span>

        <div class="fi-ta-filter-indicators-badges-ctn">
            @foreach ($filterIndicators ?? [] as $indicator)
                @if ($usesAdvancedTables && $indicator->isAdvanced() && $indicator->getRemoveLivewireClickHandler() !== 'resetTableSearch')
                    <x-filament::badge 
                        :color="$indicator->isActive() ? $indicator->getColor() : 'gray'"
                        style="padding: 0;"
                    >
                        <x-filament::dropdown
                            width="sm"
                            placement="bottom-start"
                            offset="7"
                        >
                            <x-slot 
                                name="trigger"
                                @class([
                                    'ps-2 py-1',
                                    'pe-2' => ! $indicator->isRemovable(),
                                ])
                            >
                                {{ $indicator->getLabel() }}
                            </x-slot>
                            <div class="grid gap-y-4 p-6">
                                {{ $this->getQuickFilterForm($indicator) }}
                            </div>
                        </x-filament::dropdown>

                        @if ($indicator->isRemovable())
                            <x-slot
                                name="deleteButton"
                                :label="__('filament-tables::table.filters.actions.remove.label')"
                                wire:click="{{ $indicator->getRemoveLivewireClickHandler() }}"
                                wire:loading.attr="disabled"
                                wire:target="removeTableFilter"
                                style="margin-inline-end: 0;"
                            ></x-slot>
                        @endif
                    </x-filament::badge>
                @else
                    @php
                        $indicatorColor = $indicator->getColor();
                    @endphp

                    <x-filament::badge :color="$indicatorColor">
                        {{ $indicator->getLabel() }}

                        @if ($indicator->isRemovable())
                            @php
                                $indicatorRemoveLivewireClickHandler = $indicator->getRemoveLivewireClickHandler();
                            @endphp

                            <x-slot
                                name="deleteButton"
                                :label="__('filament-tables::table.filters.actions.remove.label')"
                                :wire:click="$indicatorRemoveLivewireClickHandler"
                                wire:loading.attr="disabled"
                                wire:target="removeTableFilter"
                            ></x-slot>
                        @endif
                    </x-filament::badge>
                @endif
            @endforeach
        </div>
    </div>

    @if (collect($filterIndicators)->contains(fn (\Filament\Tables\Filters\Indicator $indicator): bool => $indicator->isRemovable()))
        <button
            type="button"
            x-tooltip="{
                content: @js(__('filament-tables::table.filters.actions.remove_all.tooltip')),
                theme: $store.theme,
            }"
            wire:click="removeTableFilters"
            wire:loading.attr="disabled"
            wire:target="removeTableFilters,removeTableFilter"
            class="fi-icon-btn fi-size-sm"
        >
            {{ \Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::XMark, alias: \Filament\Tables\View\TablesIconAlias::FILTERS_REMOVE_ALL_BUTTON, size: \Filament\Support\Enums\IconSize::Small) }}
        </button>
    @endif
</div>