<?php

namespace App\Exports;

use App\Models\DailyTimeRecord;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DTRExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $employeeId;
    protected $from;
    protected $to;
    protected $user;

    public function __construct($employeeId = null, $from = null, $to = null, $user = null)
    {
        $this->employeeId = $employeeId;
        $this->from = $from;
        $this->to = $to;
        $this->user = $user;
    }

    public function collection()
    {
        $query = DailyTimeRecord::with(['employee.branch', 'employee.position']);

        $roleName = $this->user?->role?->role_name;

        if (!in_array($roleName, ['Admin', 'Super Admin', 'Owner'])) {
            $query->whereHas('employee', function ($q) {
                $q->where('branch_id', $this->user->branch_id);
            });
        }

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        if ($this->from) {
            $query->whereDate('work_date', '>=', $this->from);
        }

        if ($this->to) {
            $query->whereDate('work_date', '<=', $this->to);
        }

        return $query->orderBy('work_date', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee Name',
            'Branch',
            'Position',
            'Work Date',
            'Shift 1 Time In',
            'Shift 1 Time Out',
            'Shift 2 Time In',
            'Shift 2 Time Out',
            'Shift 3 Time In',
            'Shift 3 Time Out',
            'Overtime Hours',
            'Undertime Hours',
            'Total Hours',
            'Night Diff Hours',
            'Night Diff OT Hours',
            'Sunday OT Hours',
            'Rest Day OT Hours',
            'Status',
            'Remarks',
        ];
    }

    public function map($row): array
    {
        return [
            $row->employee_id,
            $row->employee?->full_name ?? '',
            $row->employee?->branch?->branch_name ?? '',
            $row->employee?->position?->name ?? '',
            $this->formatDate($row->work_date),

            $this->formatTime($row->shift1_time_in),
            $this->formatTime($row->shift1_time_out),
            $this->formatTime($row->shift2_time_in),
            $this->formatTime($row->shift2_time_out),
            $this->formatTime($row->shift3_time_in),
            $this->formatTime($row->shift3_time_out),

            $row->overtime_hours ?? 0,
            $row->undertime_hours ?? 0,
            $row->total_hours ?? 0,
            $row->night_diff_hours ?? 0,
            $row->night_diff_ot_hours ?? 0,
            $row->sunday_ot_hours ?? 0,
            $row->rest_day_ot_hours ?? 0,
            $row->status ?? '',
            $row->remarks ?? '',
        ];
    }

    private function formatTime($time)
    {
        if (!$time) {
            return '';
        }

        try {
            return Carbon::parse($time)->format('h:i A');
        } catch (\Exception $e) {
            return $time;
        }
    }

    private function formatDate($date)
    {
        if (!$date) {
            return '';
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return $date;
        }
    }
}