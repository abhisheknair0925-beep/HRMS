<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\OrgStructureService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgChartController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected OrgStructureService $orgStructureService) {}

    /**
     * Retrieve hierarchical organization chart.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $tree = $this->orgStructureService->getOrganizationTree();
        return $this->successResponse($tree, 'Organization hierarchy tree resolved.');
    }

    /**
     * Transfer an employee profile to a new department/designation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'department_id' => 'nullable|uuid|exists:departments,id',
            'designation_id' => 'nullable|uuid|exists:designations,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $employee = $this->orgStructureService->transferEmployee(
            $validated['employee_id'],
            $validated['department_id'] ?? null,
            $validated['designation_id'] ?? null,
            $validated['reason'] ?? null
        );

        return $this->successResponse($employee->load(['department', 'designation']), 'Employee transferred successfully.');
    }
}
