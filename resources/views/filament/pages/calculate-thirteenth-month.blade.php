<x-filament-panels::page>
    {{-- High-End Financial Grid Layout Blueprint --}}
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
            width: 80px !important;
            min-width: 80px !important;
            max-width: 80px !important;
            padding: 0.5rem 0.2rem !important;
            vertical-align: middle !important;
        }
        /* Sticky Employee Name Column stays wider to prevent text overlapping */
        .fi-ta-table th:first-child, .fi-ta-table td:first-child {
            width: 240px !important;
            min-width: 240px !important;
            max-width: 240px !important;
            padding-left: 1rem !important;
            text-align: left !important;
        }
        /* Financial Calculation Summary Columns at the end */
        .fi-ta-table th:nth-last-child(-n+2), .fi-ta-table td:nth-last-child(-n+2) {
            width: 140px !important;
            min-width: 140px !important;
            max-width: 140px !important;
            padding-right: 1rem !important;
        }
        /* Aligns column header labels cleanly over the right-aligned inputs */
        .fi-ta-header-cell-label {
            justify-content: flex-end !important;
            width: 100%;
        }
        /* Keeps the employee name header label aligned left */
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