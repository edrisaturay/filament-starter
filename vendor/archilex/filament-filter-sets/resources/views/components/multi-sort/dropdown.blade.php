@php
    use Archilex\AdvancedTables\Support\Config;

    $hasMultiSortBadge = Config::hasMultiSortBadge();
    $multiSortCount = count($this->tableMultiSort['multiSort'] ?? []);
    $badge = $hasMultiSortBadge && ($multiSortCount > 0) ? $multiSortCount : null;
@endphp

@props([
    'offset' => 8,
    'color' => 'gray',
    'icon' => "heroicon-s-arrows-up-down",
    'iconPosition' => Config::getMultiSortIconPosition(),
    'outlined' => Config::showMultiSortButtonOutlined(),
    'label' => Config::getMultiSortButtonLabel(),
    'size' => Config::getMultiSortButtonSize(),
    'placement' => 'bottom-end',
])

<x-filament::dropdown
    :placement="$placement"
    width="xs"
    :offset="$offset"
>
    <x-slot name="trigger">
        @if (Config::showMultiSortAsButton())
            <x-filament::button
                :badge="$badge"
                badge-color="gray"
                :icon="$icon"
                :icon-position="$iconPosition"
                :color="$color"
                :size="$size"
                :outlined="$outlined"
                class="advanced-tables-show-multi-sort"
            >
                {{ $label }}
            </x-filament::button>
        @else
            <x-filament::icon-button 
                :badge="$badge"
                badge-color="gray"
                :icon="$icon"
                :color="$color"
                :size="$size"
                class="h-9 w-9 advanced-tables-show-view-manager"
            />
        @endif   
    </x-slot>
    
    <div class="p-6">
        <x-advanced-tables::multi-sort 
            :form="$this->getTableMultiSortForm()"
        />
    </div>
</x-filament::dropdown>