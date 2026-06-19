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
            
            // Flattened parameters sent by Profile.tsx
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'emergency_name' => 'nullable|string|max:100',
            'emergency_relationship' => 'nullable|string|max:50',
            'emergency_phone' => 'nullable|string|max:20',

            // Nested parameters
            'personal_info' => 'nullable|array',
            'emergency_contacts' => 'nullable|array',
            'bank_details' => 'nullable|array',

            // Work history
            'employment_history' => 'nullable|array',
            'employment_history.*.company_name' => 'required_with:employment_history|string|max:150',
            'employment_history.*.designation' => 'required_with:employment_history|string|max:100',
            'employment_history.*.start_date' => 'required_with:employment_history|date',
            'employment_history.*.end_date' => 'nullable|date|after_or_equal:employment_history.*.start_date',
            'employment_history.*.description' => 'nullable|string|max:1000',
        ]);

        $profileData = [];
        if (isset($validated['phone'])) {
            $profileData['phone'] = $validated['phone'];
        }

        // Map bank details
        if (isset($validated['bank_name']) || isset($validated['account_number']) || isset($validated['ifsc_code'])) {
            $profileData['bank_details'] = [
                'bank_name' => $validated['bank_name'] ?? ($employee->bank_details['bank_name'] ?? ''),
                'account_number' => $validated['account_number'] ?? ($employee->bank_details['account_number'] ?? ''),
                'ifsc_code' => $validated['ifsc_code'] ?? ($employee->bank_details['ifsc_code'] ?? ''),
            ];
        } elseif (isset($validated['bank_details'])) {
            $profileData['bank_details'] = $validated['bank_details'];
        }

        // Map emergency contacts
        if (isset($validated['emergency_name']) || isset($validated['emergency_relationship']) || isset($validated['emergency_phone'])) {
            $profileData['emergency_contacts'] = [
                [
                    'name' => $validated['emergency_name'] ?? '',
                    'relationship' => $validated['emergency_relationship'] ?? '',
                    'phone' => $validated['emergency_phone'] ?? '',
                ]
            ];
        } elseif (isset($validated['emergency_contacts'])) {
            $profileData['emergency_contacts'] = $validated['emergency_contacts'];
        }

        if (isset($validated['personal_info'])) {
            $profileData['personal_info'] = $validated['personal_info'];
        }

        if (isset($validated['employment_history'])) {
            $profileData['employment_history'] = $validated['employment_history'];
        }

        $updatedEmployee = $this->essService->updateProfile($employee->id, $profileData);
        $updatedEmployee->load(['department', 'designation', 'shift', 'user']);

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
            ->limit(30)
            ->get();

        return $this->successResponse($requests, 'Employee leave request history retrieved.');
    }
}
