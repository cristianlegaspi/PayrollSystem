<x-filament-panels::page>
    {{-- Fluid Table Core Responsive Baseline Style Fixes --}}
    <style>
        .fi-ta-content {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        .fi-ta-table {
            table-layout: auto !important;
            width: max-content !important;
            min-width: 100% !important;
        }

        .fi-ta-header-cell-label {
            justify-content: flex-end !important;
            width: 100%;
            padding-right: 0.25rem;
        }

        .fi-ta-table th:first-child .fi-ta-header-cell-label {
            justify-content: flex-start !important;
        }

        .fi-ta-table td,
        .fi-ta-table th {
            white-space: nowrap;
        }
    </style>

    <div class="space-y-4">
        <div class="text-xs text-blue-700 dark:text-blue-200 p-3 bg-blue-50 dark:bg-blue-950/40 rounded-lg border border-blue-100 dark:border-blue-800">
            💡 <strong>Note:</strong>
            Monthly amounts are automatically fetched from payroll records.
            To change any amount, update the employee payroll record first, then refresh this page.
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