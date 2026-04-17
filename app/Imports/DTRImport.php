<?php

namespace App\Imports;

use App\Models\DailyTimeRecord;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DTRImport implements ToCollection, WithHeadingRow
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row->filter()->isEmpty()) {
                continue;
            }

            $employee = null;

            // First try by employee number
            if (!empty($row['employee_no'])) {
                $employee = Employee::where('employee_number', trim($row['employee_no']))->first();
            }

            // Fallback: try by employee name
            if (!$employee && !empty($row['employee_name'])) {
                $employee = Employee::where('full_name', trim($row['employee_name']))->first();
            }

            if (!$employee) {
                continue;
            }

            // Security: non-admin can only import their own branch employees
            $roleName = $this->user?->role?->role_name;
            if (!in_array($roleName, ['Admin', 'Super Admin', 'Owner'])) {
                if ((int) $employee->branch_id !== (int) $this->user->branch_id) {
                    continue;
                }
            }

            $workDate = $this->parseDate($row['work_date'] ?? null);

            if (!$workDate) {
                continue;
            }

            DailyTimeRecord::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'work_date'   => $workDate,
                ],
                [
                    'shift1_time_in'      => $this->parseTime($row['shift_1_time_in'] ?? null),
                    'shift1_time_out'     => $this->parseTime($row['shift_1_time_out'] ?? null),
                    'shift2_time_in'      => $this->parseTime($row['shift_2_time_in'] ?? null),
                    'shift2_time_out'     => $this->parseTime($row['shift_2_time_out'] ?? null),
                    'shift3_time_in'      => $this->parseTime($row['shift_3_time_in'] ?? null),
                    'shift3_time_out'     => $this->parseTime($row['shift_3_time_out'] ?? null),
                    'overtime_hours'      => $this->parseDecimal($row['overtime_hours'] ?? 0),
                    'undertime_hours'     => $this->parseDecimal($row['undertime_hours'] ?? 0),
                    'total_hours'         => $this->parseDecimal($row['total_hours'] ?? 0),
                    'night_diff_hours'    => $this->parseDecimal($row['night_diff_hours'] ?? 0),
                    'night_diff_ot_hours' => $this->parseDecimal($row['night_diff_ot_hours'] ?? 0),
                    'sunday_ot_hours'     => $this->parseDecimal($row['sunday_ot_hours'] ?? 0),
                    'rest_day_ot_hours'   => $this->parseDecimal($row['rest_day_ot_hours'] ?? 0),
                    'status'              => $this->cleanText($row['status'] ?? null),
                    'remarks'             => $this->cleanText($row['remarks'] ?? null),
                ]
            );
        }
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('H:i:s');
            }

            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseDecimal($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (float) str_replace(',', '', $value);
    }

    protected function cleanText($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }
}