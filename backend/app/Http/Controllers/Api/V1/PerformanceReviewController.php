<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PerformanceReview;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceReviewController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'nullable|uuid|exists:employees,id',
        ]);

        $reviews = PerformanceReview::with('reviewer')
            ->when($request->employee_id, fn ($query) => $query->where('employee_id', $request->employee_id))
            ->latest('review_date')
            ->get()
            ->map(fn (PerformanceReview $review) => $this->formatReview($review));

        return $this->successResponse($reviews, 'Performance reviews retrieved.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'overall_score' => 'required|numeric|min:0|max:5',
            'metrics' => 'required|array',
            'metrics.*.name' => 'required|string|max:100',
            'metrics.*.score' => 'required|numeric|min:0|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $employee = Employee::find($validated['employee_id']);
        if (!$employee) {
            return $this->errorResponse('Employee not found.', 404);
        }

        $review = PerformanceReview::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'reviewer_id' => $request->user()?->id,
            'review_date' => now()->toDateString(),
            'overall_score' => $validated['overall_score'],
            'metrics' => $validated['metrics'],
            'comment' => $validated['comment'] ?? null,
        ])->load('reviewer');

        return $this->successResponse($this->formatReview($review), 'Performance review saved.', 201);
    }

    private function formatReview(PerformanceReview $review): array
    {
        return [
            'id' => $review->id,
            'reviewer' => $review->reviewer?->name ?? 'System Administrator',
            'date' => $review->review_date?->toDateString(),
            'overall' => (float) $review->overall_score,
            'metrics' => $review->metrics ?? [],
            'comment' => $review->comment,
        ];
    }
}
