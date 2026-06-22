<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanyHoliday;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyHolidayController extends Controller
{
    use ApiResponseTrait;

    public function index(): JsonResponse
    {
        $holidays = CompanyHoliday::orderBy('holiday_date')->get();

        return $this->successResponse($holidays, 'Company holidays retrieved.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'holiday_date' => 'required|date',
            'type' => 'required|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $holiday = CompanyHoliday::create($validated);

        return $this->successResponse($holiday, 'Company holiday created.', 201);
    }
}
