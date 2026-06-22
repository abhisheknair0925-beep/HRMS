<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GeneratedDocumentController extends Controller
{
    use ApiResponseTrait;

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'template' => 'required|string|max:100',
            'title' => 'required|string|max:150',
            'content' => 'required|string',
        ]);

        $employee = Employee::find($validated['employee_id']);
        if (!$employee) {
            return $this->errorResponse('Employee not found.', 404);
        }

        $document = EmployeeDocument::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'name' => $validated['title'],
            'type' => $validated['template'],
            'file_url' => 'generated://letters/' . Str::uuid(),
        ]);

        return $this->successResponse($document, 'Generated letter saved to employee document locker.', 201);
    }
}
