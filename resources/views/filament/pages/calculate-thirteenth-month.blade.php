<x-filament-panels::page>
    <div class="space-y-4">
        <div class="text-xs text-gray-500 dark:text-gray-400 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
            💡 <strong>Pro-Tip:</strong> All monthly columns are directly editable inline. Click any value, type an adjustment (e.g., to override a 0), and press <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 border rounded shadow-sm text-xs font-sans">Enter</kbd> to see totals instantly update on-screen.
        </div>

        <div class="w-full overflow-x-auto rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
            {{ $this->table }}
        </div>

        {{-- @php $totals = $this->getGrandTotals(); @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grand Total Basic Earned</span>
                <span class="text-xl font-bold text-gray-900 dark:text-white">₱{{ number_format($totals['basic'], 2) }}</span>
            </div>
            
            <div class="p-4 bg-success-50/50 dark:bg-success-950/10 rounded-xl border border-success-200/60 dark:border-success-800/30 flex justify-between items-center">
                <span class="text-sm font-semibold text-success-700 dark:text-success-400 uppercase tracking-wider">Grand Total 13th Month Pay</span>
                <span class="text-2xl font-black text-success-600 dark:text-success-400">₱{{ number_format($totals['thirteenth'], 2) }}</span>
            </div>
        </div>
    </div> --}}

    {{-- Catch and handle printing redirection scripts natively --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('open-print-preview', () => {
                window.open("{{ route('thirteenth-month.print') }}", '_blank');
            });
        });
    </script>
</x-filament-panels::page>