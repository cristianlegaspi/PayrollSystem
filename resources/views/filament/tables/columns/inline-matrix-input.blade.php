@php
    $record = $getRecord();
    $currentValue = $this->overrides[$record->id][$monthNumber] ?? 0;
@endphp

<div class="w-full min-w-[85px] px-1 py-0.5 flex items-center" wire:key="matrix-cell-{{ $record->id }}-{{ $monthNumber }}">
    <x-filament::input.wrapper class="w-full !shadow-none border border-gray-200 dark:border-gray-700 focus-within:!border-primary-500 rounded-md">
        <x-filament::input
            type="number"
            :value="$currentValue"
            {{-- Pl-2 and pr-2 provide strict internal text defense padding buffers --}}
            class="text-right w-full block font-mono font-medium pl-2 pr-2 py-1.5 text-xs tracking-tight bg-transparent [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none focus:ring-0"
            wire:change="$set('overrides.{{ $record->id }}.{{ $monthNumber }}', $event.target.value)"
        />
    </x-filament::input.wrapper>
</div>