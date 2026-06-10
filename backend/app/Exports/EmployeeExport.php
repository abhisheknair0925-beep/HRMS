<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Return collection of employees to export.
     * Automatically scoped by TenantScope inside active company context.
     *
     * @return Collection
     */
    public function collection(): Collection
    {
        return Employee::all();
    }

    /**
     * Map each row of the export.
     *
     * @param mixed $employee
     * @return array
     */
    public function map($employee): array
    {
        return [
            $employee->employee_id,
            $employee->first_name,
            $employee->last_name,
            $employee->email,
            $employee->phone,
            $employee->joining_date->format('Y-m-d'),
            $employee->status,
        ];
    }

    /**
     * Excel headings row.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Employee ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Joining Date',
            'Status',
        ];
    }
}
