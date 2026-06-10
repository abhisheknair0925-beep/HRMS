<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\EssService;
use App\Traits\ApiResponseTrait;
use App\Models\AttendanceLog;
use App\Models\LeaveRequest;
use App\Exceptions\BusinessException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EssApiController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected EssService $essService) {}

    /**
     * Get the ESS dashboard data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function dashboard(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return $this->errorResponse('Employee profile not found.', 404);
        }

        $data = $this->essService->getDashboardData($employee->id);
        return $this->successResponse($data, 'ESS Dashboard retrieved.');
    }

    /**
     * Get active company announcements.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function announcements(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;
        if (!$companyId) {
            return $this->errorResponse('Company context not found.', 400);
        }

        $data = $this->essService->getAnnouncements($companyId);
        return $this->successResponse($data, 'Company announcements retrieved.');
    }

    /**
     * Update employee profile details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return $this->errorResponse('Employee profile not found.', 404);
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'personal_info' => 'nullable|array',
            'emergency_contacts' => 'nullable|array',
            'bank_details' => 'nullable|array',
        ]);

        $updatedEmployee = $this->essService->updateProfile($employee->id, $validated);
        return $this->successResponse($updatedEmployee, 'Profile updated successfully.');
    }

    /**
     * Retrieve attendance log history for the current employee.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attendance(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return $this->errorResponse('Employee profile not found.', 404);
        }

        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->orderBy('log_date', 'desc')
            ->limit(30)
            ->get();

        return $this->successResponse($logs, 'Employee attendance history retrieved.');
    }

    /**
     * Retrieve leave request history for the current employee.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function leaves(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return $this->errorResponse('Employee profile not found.', 404);
        }

        $requests = LeaveRequest::where('employee_id', $employee->id)
            ->with('leavePolicy')
            ->orderBy('start_date', 'desc')
            ->get();

        return $this->successResponse($requests, 'Employee leave request history retrieved.');
    }
}
