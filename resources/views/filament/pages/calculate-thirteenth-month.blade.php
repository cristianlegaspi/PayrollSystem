<x-filament-panels::page>
    <div class="space-y-6">
        {{-- This wrapper captures your real-time input fields and links them to the Livewire form pipeline --}}
        <form wire:submit.prevent="">
            {{ $this->table }}
        </form>
    </div>

    {{-- Script handler to open your clean print preview window --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-print-preview', () => {
                window.open('/admin/print-thirteenth-month-summary', '_blank');
            });
        });
    </script>
</x-filament-panels::page>