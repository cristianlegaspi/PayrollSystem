<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Advance Statement</title>

    <style>
        @page {
            size: A4;
            margin: 15px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        td, th {
            border: 1px solid #ccc;
            padding: 4px 6px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .section {
            background: #e0e0e0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .no-border td {
            border: none;
            padding: 2px 0;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            letter-spacing: 1px;
            color: #007bff;
        }

        .header p {
            margin: 1px 0 0;
            font-size: 10px;
        }

        .cash-advance-table tr:nth-child(even),
        .payment-table tr:nth-child(even),
        .summary-list-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .summary-table {
            width: 45%;
            margin-left: auto;
        }

        .balance {
            font-size: 11px;
            font-weight: bold;
            background: #007bff;
            color: #fff;
        }

        .employee-title {
            margin-top: 8px;
            margin-bottom: 4px;
            font-weight: bold;
            text-transform: uppercase;
            background: #f2f2f2;
            border: 1px solid #ccc;
            padding: 5px;
        }

        .page-break {
            page-break-after: always;
        }

        .employee-page {
            page-break-after: always;
        }

        .employee-page:last-child {
            page-break-after: auto;
        }

        .signature {
            margin-top: 30px;
        }

        .signature td {
            padding-top: 35px;
        }

        .footer {
            text-align: center;
            font-size: 8px;
            color: #777;
            margin-top: 10px;
        }

        @media print {
            body {
                margin: 0;
            }

            .page-break {
                page-break-after: always;
            }

            .employee-page {
                page-break-after: always;
            }

            .employee-page:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>

@php
    $printedDate = now()->format('M d, Y');

    $scope = $scope ?? 'employee';

    $isBatchPrint = in_array($scope, ['branch', 'all'], true);

    $reportTitle = match ($scope) {
        'all' => 'ALL EMPLOYEES',
        'branch' => 'BRANCH: ' . ($branch?->branch_name ?? 'N/A'),
        default => 'PER EMPLOYEE',
    };

    $grandTotalCashAdvance = (float) ($grandTotalCashAdvance ?? 0);
    $grandTotalPaid = (float) ($grandTotalPaid ?? 0);
    $grandTotalBalance = (float) ($grandTotalBalance ?? 0);
@endphp

<body>

@if ($isBatchPrint)
    <div class="header">
        <h1>E.A OCAMPO ENTERPRISES</h1>
        <p>CASH ADVANCE SUMMARY</p>
    </div>

    <table class="no-border">
        <tr>
            <td><strong>Report Type:</strong> {{ $reportTitle }}</td>
            <td><strong>Date Printed:</strong> {{ $printedDate }}</td>
        </tr>
        <tr>
            <td><strong>Total Employees with C.A:</strong> {{ $employees->count() }}</td>
            <td></td>
        </tr>
    </table>

    <table class="summary-list-table">
        <tr class="section">
            <td colspan="7">Summary Per Employee</td>
        </tr>

        <tr>
            <th class="center" style="width: 5%;">#</th>
            <th style="width: 15%;">Employee No.</th>
            <th style="width: 25%;">Employee Name</th>
            <th style="width: 18%;">Branch</th>
            <th class="right" style="width: 14%;">Total C.A</th>
            <th class="right" style="width: 14%;">Total Paid</th>
            <th class="right" style="width: 14%;">Balance</th>
        </tr>

        @foreach ($summary as $row)
            @php
                $summaryEmployee = $row['employee'];
            @endphp

            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td>{{ $summaryEmployee->employee_number ?? 'N/A' }}</td>
                <td>{{ $summaryEmployee->full_name ?? 'N/A' }}</td>
                <td>{{ $summaryEmployee->branch?->branch_name ?? $summaryEmployee->branch?->name ?? 'N/A' }}</td>
                <td class="right">PHP {{ number_format((float) $row['total_cash_advance'], 2) }}</td>
                <td class="right">PHP {{ number_format((float) $row['total_paid'], 2) }}</td>
                <td class="right bold">PHP {{ number_format((float) $row['total_balance'], 2) }}</td>
            </tr>
        @endforeach
    </table>

    <table class="summary-table">
        <tr>
            <td class="bold">Grand Total Cash Advance</td>
            <td class="right bold">
                PHP {{ number_format($grandTotalCashAdvance, 2) }}
            </td>
        </tr>

        <tr>
            <td class="bold">Grand Total Paid / Deducted</td>
            <td class="right bold">
                PHP {{ number_format($grandTotalPaid, 2) }}
            </td>
        </tr>

        <tr class="balance">
            <td>Grand Remaining Balance</td>
            <td class="right">
                PHP {{ number_format($grandTotalBalance, 2) }}
            </td>
        </tr>
    </table>

    <div class="footer">
        This cash advance summary is system generated.
    </div>

    <div class="page-break"></div>
@endif

@foreach ($employees as $employee)
    @php
        $employeeCashAdvances = $cashAdvances->get($employee->id, collect());
        $employeePayments = $payments->get($employee->id, collect());

        $employeeNo = $employee->employee_number ?? 'N/A';
        $employeeName = $employee->full_name ?? 'N/A';

        $branchName = $employee->branch?->name
            ?? $employee->branch?->branch_name
            ?? 'N/A';

        $positionName = $employee->position?->name
            ?? $employee->position?->position_name
            ?? 'N/A';

        $employeeStatus = $employee->status ?? 'N/A';

        $employeeTotalCashAdvance = (float) $employeeCashAdvances->sum('amount');
        $employeeTotalPaid = (float) $employeePayments->sum('amount');
        $employeeTotalBalance = $employeeTotalCashAdvance - $employeeTotalPaid;
    @endphp

    <div class="employee-page">
        <div class="header">
            <h1>E.A OCAMPO ENTERPRISES</h1>
            <p>CASH ADVANCE STATEMENT</p>
        </div>

        <table class="no-border">
            <tr>
                <td><strong>Employee No.:</strong> {{ $employeeNo }}</td>
                <td><strong>Date Printed:</strong> {{ $printedDate }}</td>
            </tr>
            <tr>
                <td><strong>Employee Name:</strong> {{ $employeeName }}</td>
                <td><strong>Branch:</strong> {{ $branchName }}</td>
            </tr>
            <tr>
                <td><strong>Position:</strong> {{ $positionName }}</td>
                <td><strong>Employee Status:</strong> {{ $employeeStatus }}</td>
            </tr>
        </table>

        <table class="cash-advance-table">
            <tr class="section">
                <td colspan="7">Cash Advance Records</td>
            </tr>

            <tr>
                <th class="center" style="width: 11%;">Date</th>
                <th class="center" style="width: 17%;">C.A No.</th>
                <th class="center" style="width: 18%;">Type</th>
                <th class="right" style="width: 14%;">Amount</th>
                <th class="right" style="width: 14%;">Paid / Deducted</th>
                <th class="right" style="width: 14%;">Balance</th>
                <th>Remarks</th>
            </tr>

            @forelse ($employeeCashAdvances as $cashAdvance)
                <tr>
                    <td class="center">
                        {{ $cashAdvance->transaction_date?->format('M d, Y') }}
                    </td>

                    <td class="center">
                        {{ $cashAdvance->ca_no }}
                    </td>

                    <td>
                        @switch($cashAdvance->type)
                            @case('previous_balance')
                                Previous Balance
                                @break

                            @case('cash_advance')
                                Cash Advance
                                @break

                            @case('motor_assistance')
                                Motor Assistance
                                @break

                            @case('adjustment_add')
                                Adjustment Add
                                @break

                            @default
                                {{ $cashAdvance->type }}
                        @endswitch
                    </td>

                    <td class="right">
                        PHP {{ number_format((float) $cashAdvance->amount, 2) }}
                    </td>

                    <td class="right">
                        PHP {{ number_format((float) $cashAdvance->paid_amount, 2) }}
                    </td>

                    <td class="right">
                        PHP {{ number_format((float) $cashAdvance->balance, 2) }}
                    </td>

                    <td>
                        {{ $cashAdvance->remarks }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">
                        No cash advance records found.
                    </td>
                </tr>
            @endforelse
        </table>

        <table class="payment-table">
            <tr class="section">
                <td colspan="6">Payment / Deduction History</td>
            </tr>

            <tr>
                <th class="center" style="width: 11%;">Date</th>
                <th class="center" style="width: 17%;">Payment No.</th>
                <th class="center" style="width: 17%;">C.A No.</th>
                <th class="center" style="width: 18%;">Type</th>
                <th class="right" style="width: 14%;">Amount</th>
                <th>Remarks</th>
            </tr>

            @forelse ($employeePayments as $payment)
                <tr>
                    <td class="center">
                        {{ $payment->payment_date?->format('M d, Y') }}
                    </td>

                    <td class="center">
                        {{ $payment->payment_no }}
                    </td>

                    <td class="center">
                        {{ $payment->cashAdvance?->ca_no ?? 'N/A' }}
                    </td>

                    <td>
                        @switch($payment->payment_type)
                            @case('payment')
                                Payment
                                @break

                            @case('deduction')
                                Deduction
                                @break

                            @case('adjustment_less')
                                Adjustment Less
                                @break

                            @default
                                {{ $payment->payment_type }}
                        @endswitch
                    </td>

                    <td class="right">
                        PHP {{ number_format((float) $payment->amount, 2) }}
                    </td>

                    <td>
                        {{ $payment->remarks }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">
                        No payment or deduction history found.
                    </td>
                </tr>
            @endforelse
        </table>

        <table class="summary-table">
            <tr>
                <td class="bold">Total Cash Advance</td>
                <td class="right bold">
                    PHP {{ number_format($employeeTotalCashAdvance, 2) }}
                </td>
            </tr>

            <tr>
                <td class="bold">Total Paid / Deducted</td>
                <td class="right bold">
                    PHP {{ number_format($employeeTotalPaid, 2) }}
                </td>
            </tr>

            <tr class="balance">
                <td>Remaining Balance</td>
                <td class="right">
                    PHP {{ number_format($employeeTotalBalance, 2) }}
                </td>
            </tr>
        </table>

        <table class="no-border signature">
            <tr>
                <td class="center">
                    _________________________<br>
                    Authorized Signature
                </td>
                <td class="center">
                    _________________________<br>
                    Employee Signature
                </td>
            </tr>
        </table>

        <div class="footer">
            This cash advance statement is system generated.
        </div>
    </div>
@endforeach

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 500);
    });

    window.addEventListener('afterprint', function () {
        window.close();
    });
</script>

</body>
</html>