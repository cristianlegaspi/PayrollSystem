@php
    $record = $getRecord();
    $currentValue = $this->overrides[$record->id][$monthNumber] ?? 0;
@endphp

<div class="w-full block px-0.5" wire:key="matrix-cell-{{ $record->id }}-{{ $monthNumber }}">
    <x-filament::input.wrapper class="w-full !shadow-none border border-gray-200 dark:border-gray-700 focus-within:!border-primary-500 rounded-md">
        <x-filament::input
            type="number"
            :value="$currentValue"
            {{-- Proportional layout settings: matches standard financial text-inputs --}}
            class="text-right w-full block font-mono font-medium px-2 py-1 text-[13px] tracking-tight bg-transparent [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none focus:ring-0"
            wire:change="$set('overrides.{{ $record->id }}.{{ $monthNumber }}', $event.target.value)"
        />
    </x-filament::input.wrapper>
</div>