<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display listing of company shifts.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $shifts = Shift::all();
        return $this->successResponse($shifts, 'Shifts list retrieved.');
    }

    /**
     * Store a newly created shift.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_period_minutes' => 'sometimes|integer|min:0',
            'half_day_minutes' => 'sometimes|integer|min:0',
            'full_day_minutes' => 'sometimes|integer|min:0',
        ]);

        $shift = Shift::create($validated);

        return $this->successResponse($shift, 'Shift created successfully.', 201);
    }

    /**
     * Display the specified shift.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $shift = Shift::find($id);

        if (!$shift) {
            return $this->errorResponse('Shift not found.', 404);
        }

        return $this->successResponse($shift, 'Shift details retrieved.');
    }

    /**
     * Update the specified shift.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $shift = Shift::find($id);

        if (!$shift) {
            return $this->errorResponse('Shift not found.', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i',
            'grace_period_minutes' => 'sometimes|required|integer|min:0',
            'half_day_minutes' => 'sometimes|required|integer|min:0',
            'full_day_minutes' => 'sometimes|required|integer|min:0',
        ]);

        $shift->update($validated);

        return $this->successResponse($shift, 'Shift updated successfully.');
    }

    /**
     * Remove the specified shift.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $shift = Shift::find($id);

        if (!$shift) {
            return $this->errorResponse('Shift not found.', 404);
        }

        $shift->delete();

        return $this->successResponse(null, 'Shift deleted successfully.');
    }
}
