<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeMessage;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeMessageController extends Controller
{
    use ApiResponseTrait;

    public function index(string $employeeId): JsonResponse
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return $this->errorResponse('Employee not found.', 404);
        }

        $messages = EmployeeMessage::where('employee_id', $employee->id)
            ->orderBy('sent_at')
            ->get()
            ->map(fn (EmployeeMessage $message) => $this->formatMessage($message));

        return $this->successResponse($messages, 'Employee messages retrieved.');
    }

    public function store(Request $request, string $employeeId): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'sender_type' => 'sometimes|string|in:admin,employee',
        ]);

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return $this->errorResponse('Employee not found.', 404);
        }

        $message = EmployeeMessage::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'sender_user_id' => $request->user()?->id,
            'sender_type' => $validated['sender_type'] ?? 'admin',
            'message' => $validated['message'],
            'sent_at' => now(),
        ]);

        return $this->successResponse($this->formatMessage($message), 'Employee message saved.', 201);
    }

    private function formatMessage(EmployeeMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender' => $message->sender_type === 'employee' ? 'employee' : 'admin',
            'text' => $message->message,
            'timestamp' => $message->sent_at?->format('h:i A') ?? $message->created_at?->format('h:i A'),
        ];
    }
}
