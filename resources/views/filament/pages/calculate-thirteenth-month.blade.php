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
            Overview: Displays each employee’s monthly gross pay and computed 13th Month Pay for the selected year.
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