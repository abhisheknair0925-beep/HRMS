<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompOffRequest;
use App\Models\Employee;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompOffRequestController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $requests = CompOffRequest::with('employee')
            ->latest('worked_date')
            ->get()
            ->map(fn (CompOffRequest $request) => $this->formatRequest($request));

        return $this->successResponse($requests, 'Comp-off requests retrieved.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'worked_date' => 'required|date',
            'reason' => 'required|string|max:1000',
        ]);

        $employee = Employee::find($validated['employee_id']);
        if (!$employee) {
            return $this->errorResponse('Employee not found.', 404);
        }

        $compOff = CompOffRequest::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'worked_date' => $validated['worked_date'],
            'reason' => $validated['reason'],
            'status' => 'Pending',
        ])->load('employee');

        return $this->successResponse($this->formatRequest($compOff), 'Comp-off request created.', 201);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $compOff = CompOffRequest::with('employee')->find($id);
        if (!$compOff) {
            return $this->errorResponse('Comp-off request not found.', 404);
        }

        $compOff->update([
            'status' => 'Approved',
            'processed_by' => $request->user()?->id,
        ]);

        return $this->successResponse($this->formatRequest($compOff->fresh('employee')), 'Comp-off request approved.');
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $compOff = CompOffRequest::with('employee')->find($id);
        if (!$compOff) {
            return $this->errorResponse('Comp-off request not found.', 404);
        }

        $compOff->update([
            'status' => 'Rejected',
            'processed_by' => $request->user()?->id,
            'rejection_reason' => $validated['rejection_reason'] ?? 'Rejected by administrator.',
        ]);

        return $this->successResponse($this->formatRequest($compOff->fresh('employee')), 'Comp-off request rejected.');
    }

    private function formatRequest(CompOffRequest $request): array
    {
        return [
            'id' => $request->id,
            'employee_id' => $request->employee_id,
            'employee_name' => $request->employee
                ? trim("{$request->employee->first_name} {$request->employee->last_name}")
                : 'Employee',
            'worked_date' => $request->worked_date?->toDateString(),
            'reason' => $request->reason,
            'status' => $request->status,
        ];
    }
}
