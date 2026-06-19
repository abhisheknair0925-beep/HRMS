<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PerformanceAppraisal;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerformanceAppraisalController extends Controller
{
    use ApiResponseTrait;

    /**
     * Retrieve appraisals for a specific employee.
     */
    public function index(string $employeeId): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return $this->errorResponse('Employee not found or access denied.', 404);
        }

        // Tenant validation check
        if ($employee->company_id !== $user->company_id) {
            return $this->errorResponse('Employee not found or access denied.', 404);
        }

        // Role and IDOR authorization check
        if (!$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            $userEmployee = $user->employee;
            if (!$userEmployee || $userEmployee->id !== $employeeId) {
                return $this->errorResponse('Access denied. You can only view your own performance appraisals.', 403);
            }
        }

        $appraisals = PerformanceAppraisal::where('employee_id', $employeeId)
            ->orderBy('review_date', 'desc')
            ->get();

        return $this->successResponse($appraisals, 'Performance appraisals retrieved successfully.');
    }

    /**
     * Store a new appraisal for an employee.
     */
    public function store(Request $request, string $employeeId): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return $this->errorResponse('Employee not found.', 404);
        }

        // Tenant validation check
        if ($employee->company_id !== $user->company_id) {
            return $this->errorResponse('Employee not found.', 404);
        }

        // Role authorization check (Only Admins, HR, or Managers can grade scorecards)
        if (!$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            return $this->errorResponse('Access denied. Only Admins, HR, or Managers can grade scorecards.', 403);
        }

        $validated = $request->validate([
            'reviewer_name' => 'required|string|max:100',
            'quality_score' => 'required|integer|min:1|max:5',
            'productivity_score' => 'required|integer|min:1|max:5',
            'teamwork_score' => 'required|integer|min:1|max:5',
            'communication_score' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:2000',
        ]);

        $overall = ($validated['quality_score'] + $validated['productivity_score'] + $validated['teamwork_score'] + $validated['communication_score']) / 4;

        $appraisal = PerformanceAppraisal::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employeeId,
            'reviewer_name' => $validated['reviewer_name'],
            'review_date' => now()->toDateString(),
            'overall_score' => round($overall, 2),
            'quality_score' => $validated['quality_score'],
            'productivity_score' => $validated['productivity_score'],
            'teamwork_score' => $validated['teamwork_score'],
            'communication_score' => $validated['communication_score'],
            'comment' => $validated['comment'],
        ]);

        return $this->successResponse($appraisal, 'Performance appraisal score submitted successfully.', 201);
    }

    /**
     * Export appraisals for a specific employee as CSV.
     */
    public function report(string $employeeId): StreamedResponse
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            abort(404, 'Employee not found.');
        }

        // Tenant validation check
        if ($employee->company_id !== $user->company_id) {
            abort(404, 'Employee not found.');
        }

        // Role authorization check
        if (!$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            $userEmployee = $user->employee;
            if (!$userEmployee || $userEmployee->id !== $employeeId) {
                abort(403, 'Access denied. You can only export your own report.');
            }
        }

        $appraisals = PerformanceAppraisal::where('employee_id', $employeeId)
            ->orderBy('review_date', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="performance_report_' . $employee->employee_id . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($appraisals, $employee) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper excel rendering
            fputs($file, "\xEF\xBB\xBF");

            // Title block
            fputcsv($file, ['HumaNode Performance Evaluation Report']);
            fputcsv($file, ['Employee:', $employee->first_name . ' ' . $employee->last_name . ' (' . $employee->employee_id . ')']);
            fputcsv($file, ['Exported Date:', now()->toDateTimeString()]);
            fputcsv($file, []);

            // Table headers
            fputcsv($file, [
                'Appraisal ID', 
                'Reviewer', 
                'Review Date', 
                'Quality Score', 
                'Productivity Score', 
                'Teamwork Score', 
                'Communication Score', 
                'Overall Score', 
                'Comments'
            ]);

            // Table rows
            foreach ($appraisals as $appraisal) {
                fputcsv($file, [
                    $appraisal->id,
                    $appraisal->reviewer_name,
                    $appraisal->review_date->toDateString(),
                    $appraisal->quality_score,
                    $appraisal->productivity_score,
                    $appraisal->teamwork_score,
                    $appraisal->communication_score,
                    $appraisal->overall_score,
                    $appraisal->comment
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
