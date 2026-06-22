<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OnboardingCandidate;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardingCandidateController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $candidates = OnboardingCandidate::orderBy('created_at', 'desc')->get();
        return $this->successResponse($candidates, 'Onboarding candidates retrieved.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'joining_date' => 'sometimes|required|date',
        ]);

        $joiningDate = $validated['joining_date'] ?? now()->toDateString();

        // Standard default checklist based on department
        $checklist = $validated['department'] === 'Software Engineering'
            ? [
                'Codebase Access' => false,
                'Slack Channel' => false,
                'Compliance Training' => false,
                'AWS Sandbox credentials' => false,
            ]
            : [
                'Figma Access' => false,
                'Slack Channel' => false,
                'Compliance Training' => false,
                'HR Induction Session' => false,
            ];

        $candidate = OnboardingCandidate::create([
            'company_id' => $request->user()->company_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department' => $validated['department'],
            'joining_date' => $joiningDate,
            'status' => 'Incomplete',
            'emp_id' => null,
            'docs_verified' => false,
            'induction_scheduled' => false,
            'induction_details' => null,
            'assets' => [],
            'checklist' => $checklist,
        ]);

        return $this->successResponse($candidate, 'Candidate registered successfully.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $candidate = OnboardingCandidate::find($id);
        if (!$candidate) {
            return $this->errorResponse('Candidate not found.', 404);
        }
        return $this->successResponse($candidate, 'Candidate details retrieved.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $candidate = OnboardingCandidate::find($id);
        if (!$candidate) {
            return $this->errorResponse('Candidate not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'role' => 'sometimes|required|string|max:255',
            'department' => 'sometimes|required|string|max:255',
            'joining_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|string',
            'emp_id' => 'sometimes|nullable|string',
            'docs_verified' => 'sometimes|required|boolean',
            'induction_scheduled' => 'sometimes|required|boolean',
            'induction_details' => 'sometimes|nullable|array',
            'assets' => 'sometimes|nullable|array',
            'checklist' => 'sometimes|nullable|array',
        ]);

        $candidate->update($validated);

        return $this->successResponse($candidate, 'Candidate updated successfully.');
    }

    public function destroy(string $id): JsonResponse
    {
        $candidate = OnboardingCandidate::find($id);
        if (!$candidate) {
            return $this->errorResponse('Candidate not found.', 404);
        }
        $candidate->delete();
        return $this->successResponse(null, 'Candidate deleted successfully.');
    }

    public function verifyDocs(string $id): JsonResponse
    {
        $candidate = OnboardingCandidate::find($id);
        if (!$candidate) {
            return $this->errorResponse('Candidate not found.', 404);
        }

        $candidate->update(['docs_verified' => true]);

        // Evaluate if completed
        $this->checkCompleteness($candidate);

        return $this->successResponse($candidate, 'Candidate documents verified.');
    }

    public function generateId(string $id): JsonResponse
    {
        $candidate = OnboardingCandidate::find($id);
        if (!$candidate) {
            return $this->errorResponse('Candidate not found.', 404);
        }

        if ($candidate->emp_id) {
            return $this->successResponse($candidate, 'Candidate already has employee ID.');
        }

        $year = now()->format('Y');
        $count = DB::table('employees')->where('company_id', $candidate->company_id)->count()
               + DB::table('onboarding_candidates')->where('company_id', $candidate->company_id)->whereNotNull('emp_id')->count();
        
        $seq = $count + 1;
        $empId = sprintf('EMP-%s-%04d', $year, $seq);

        $candidate->update(['emp_id' => $empId]);

        // Evaluate if completed
        $this->checkCompleteness($candidate);

        return $this->successResponse($candidate, 'Employee ID generated successfully.');
    }

    protected function checkCompleteness(OnboardingCandidate $candidate): void
    {
        $allDone = true;
        if ($candidate->checklist) {
            foreach ($candidate->checklist as $done) {
                if (!$done) {
                    $allDone = false;
                    break;
                }
            }
        }

        $status = 'Incomplete';
        if ($candidate->docs_verified || $candidate->emp_id) {
            $status = 'Verified';
        }
        if ($candidate->docs_verified && $candidate->emp_id && $candidate->induction_scheduled && $allDone) {
            $status = 'Completed';
        }

        $candidate->update(['status' => $status]);
    }
}
