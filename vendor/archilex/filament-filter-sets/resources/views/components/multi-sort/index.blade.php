@props([
    'form',
])

<div
    class="flex flex-col gap-y-2 text-sm"
>
    <div class="flex items-center justify-between">
        <h4 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
            {{ __('filament-tables::table.sorting.fields.column.label') }}
        </h4>

        <div class="flex items-center gap-x-4">
            <x-filament::link
                color="danger"
                tag="button"
                wire:click="resetTableSort"
            >
                {{ __('advanced-tables::advanced-tables.multi_sort.reset_label') }}
            </x-filament::link>
        </div>
    </div>
    <div class="flex flex-col gap-y-6">
        {{ $form }}
    </div>
</div>