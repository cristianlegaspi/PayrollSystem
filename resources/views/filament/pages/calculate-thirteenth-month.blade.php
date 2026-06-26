<x-filament-panels::page>
    {{-- Fluid Table Core Responsive Baseline Style Fixes --}}
    <style>
        .fi-ta-content {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .fi-ta-table {
            table-layout: auto !important; /* Forces layout tracking engines to prioritize cell min-widths */
            width: max-content !important;
            min-width: 100% !important;
        }
        /* Standardizes table header elements with input box alignments */
        .fi-ta-header-cell-label {
            justify-content: flex-end !important;
            width: 100%;
            padding-right: 0.25rem;
        }
        .fi-ta-table th:first-child .fi-ta-header-cell-label {
            justify-content: flex-start !important;
        }
    </style>

    <div class="space-y-4">
        <div class="text-xs text-gray-500 dark:text-gray-400 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
            💡 <strong>Pro-Tip:</strong> All monthly columns are directly editable inline. Click any value, type an adjustment (e.g., to override a 0), and press <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 border rounded shadow-sm text-xs font-sans">Enter</kbd> to see totals instantly update on-screen.
        </div>

        <div class="w-full overflow-x-auto rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
            {{ $this->table }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('open-print-preview', () => {
                window.open("{{ route('thirteenth-month.print') }}", '_blank');
            });
        });
    </script>
</x-filament-panels::page>