<?php

namespace App\Exports;

use App\Models\DailyTimeRecord;
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
            'Time In',
            'Break Out',
            'Break In',
            'Time Out',
            'Late Minutes',
            'Undertime Minutes',
            'Overtime Hours',
            'Status',
            'Remarks',
        ];
    }

    public function map($row): array
    {
        return [
            $row->employee_id,
            $row->employee?->full_name,
            $row->employee?->branch?->branch_name,
            $row->employee?->position?->name,
            $row->work_date,
            $row->time_in,
            $row->break_out,
            $row->break_in,
            $row->time_out,
            $row->late_minutes ?? 0,
            $row->undertime_minutes ?? 0,
            $row->overtime_hours ?? 0,
            $row->status,
            $row->remarks,
        ];
    }
}