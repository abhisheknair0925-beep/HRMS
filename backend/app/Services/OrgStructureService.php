<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeTransfer;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;

class OrgStructureService
{
    /**
     * Transfer employee to a new department/designation.
     *
     * @param string $employeeId
     * @param string|null $newDeptId
     * @param string|null $newDesigId
     * @param string|null $reason
     * @return Employee
     * @throws BusinessException
     */
    public function transferEmployee(string $employeeId, ?string $newDeptId, ?string $newDesigId, ?string $reason = null): Employee
    {
        return DB::transaction(function () use ($employeeId, $newDeptId, $newDesigId, $reason) {
            $employee = Employee::lockForUpdate()->find($employeeId);

            if (!$employee) {
                throw new BusinessException("Employee profile not found.", 404);
            }

            $oldDeptId = $employee->department_id;
            $oldDesigId = $employee->designation_id;

            // Check if there is any change
            if ($oldDeptId === $newDeptId && $oldDesigId === $newDesigId) {
                throw new BusinessException("No transfer changes identified. New department and designation match current settings.", 422);
            }

            // Update Employee profile
            $employee->update([
                'department_id' => $newDeptId,
                'designation_id' => $newDesigId,
            ]);

            // Register Transfer History
            EmployeeTransfer::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'old_department_id' => $oldDeptId,
                'new_department_id' => $newDeptId,
                'old_designation_id' => $oldDesigId,
                'new_designation_id' => $newDesigId,
                'transfer_date' => now(),
                'reason' => $reason,
            ]);

            activity()
                ->performedOn($employee)
                ->withProperties([
                    'old_dept' => $oldDeptId,
                    'new_dept' => $newDeptId,
                    'old_desig' => $oldDesigId,
                    'new_desig' => $newDesigId,
                ])
                ->log('Employee transferred to new org division');

            return $employee;
        });
    }

    /**
     * Compute organization hierarchy tree recursively.
     *
     * @return array
     */
    public function getOrganizationTree(): array
    {
        // Fetch employees under global tenant filter
        $employees = Employee::with(['designation'])->get();

        $mapped = [];
        foreach ($employees as $employee) {
            $mapped[$employee->user_id] = [
                'id' => $employee->id,
                'user_id' => $employee->user_id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'employee_id' => $employee->employee_id,
                'designation' => $employee->designation?->title ?? 'Unassigned',
                'manager_id' => $employee->manager_id,
                'children' => []
            ];
        }

        $tree = [];
        foreach ($mapped as $userId => &$node) {
            $parentManagerId = $node['manager_id'];
            if ($parentManagerId && isset($mapped[$parentManagerId])) {
                $mapped[$parentManagerId]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }

        return $tree;
    }
}
