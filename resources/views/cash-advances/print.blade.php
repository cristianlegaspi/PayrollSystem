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
        .payment-table tr:nth-child(even) {
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

        .signature {
            margin-top: 20px;
        }

        .signature td {
            padding-top: 40px;
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
        }
    </style>
</head>

@php
    $employeeNo = $employee->employee_number ?? 'N/A';
    $employeeName = $employee->full_name ?? 'N/A';

    $branchName = $employee->branch?->name
        ?? $employee->branch?->branch_name
        ?? 'N/A';

    $positionName = $employee->position?->name
        ?? $employee->position?->position_name
        ?? 'N/A';

    $employeeStatus = $employee->status ?? 'N/A';

    $printedDate = now()->format('M d, Y');

    $totalCashAdvance = (float) ($totalCashAdvance ?? 0);
    $totalPaid = (float) ($totalPaid ?? 0);
    $totalBalance = (float) ($totalBalance ?? 0);
@endphp

<body>

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

    @forelse ($cashAdvances as $cashAdvance)
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

    @forelse ($payments as $payment)
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
            PHP {{ number_format($totalCashAdvance, 2) }}
        </td>
    </tr>

    <tr>
        <td class="bold">Total Paid / Deducted</td>
        <td class="right bold">
            PHP {{ number_format($totalPaid, 2) }}
        </td>
    </tr>

    <tr class="balance">
        <td>Remaining Balance</td>
        <td class="right">
            PHP {{ number_format($totalBalance, 2) }}
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