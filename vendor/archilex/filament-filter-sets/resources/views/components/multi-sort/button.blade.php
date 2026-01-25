@props([
    'placement' => 'bottom-end'
])

<div>
    <x-advanced-tables::multi-sort.dropdown 
        :icon="Archilex\AdvancedTables\Support\Config::getMultiSortIcon()"    
        :placement="$placement"
    />
</div>