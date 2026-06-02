<x-filament-panels::page>
    {{-- Refined Grid Blueprint Styles --}}
    <style>
        .fi-ta-content {
            overflow-x: auto !important;
            position: relative;
        }
        .fi-ta-table {
            table-layout: fixed !important; 
            width: max-content !important;
            min-width: 100% !important;
            border-collapse: separate !important;
        }
        /* Lock Month headers and row cells together perfectly */
        .fi-ta-table th, .fi-ta-table td {
            width: 85px !important;
            min-width: 85px !important;
            max-width: 85px !important;
            padding: 0.5rem 0.25rem !important;
            vertical-align: middle !important;
        }
        /* Sticky Employee name column header & tracking cell stays wide */
        .fi-ta-table th:first-child, .fi-ta-table td:first-child {
            width: 200px !important;
            min-width: 200px !important;
            max-width: 200px !important;
            padding-left: 1rem !important;
        }
        /* Financial Calculation Columns at the end */
        .fi-ta-table th:nth-last-child(-n+2), .fi-ta-table td:nth-last-child(-n+2) {
            width: 135px !important;
            min-width: 135px !important;
            max-width: 135px !important;
            padding-right: 1rem !important;
        }
        /* Aligning text column headers directly over inputs */
        .fi-ta-header-cell-label {
            justify-content: flex-end !important;
            width: 100%;
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