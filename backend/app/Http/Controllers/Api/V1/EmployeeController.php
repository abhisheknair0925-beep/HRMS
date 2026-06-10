<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Exports\EmployeeExport;
use App\Imports\EmployeeImport;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of employees.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $employees = Employee::query()
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->paginate((int) ($request->per_page ?? 15));

        return $this->successResponse($employees, 'Employee profiles list retrieved.');
    }

    /**
     * Store a newly created employee.
     *
     * @param StoreEmployeeRequest $request
     * @return JsonResponse
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = Employee::create($request->validated());
        return $this->successResponse($employee, 'Employee profile created successfully.', 201);
    }

    /**
     * Display the specified employee.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $employee = Employee::with('documents')->find($id);

        if (!$employee) {
            return $this->errorResponse('Employee not found or access denied.', 404);
        }

        return $this->successResponse($employee, 'Employee details retrieved.');
    }

    /**
     * Update the specified employee in storage.
     *
     * @param StoreEmployeeRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(StoreEmployeeRequest $request, string $id): JsonResponse
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return $this->errorResponse('Employee not found or access denied.', 404);
        }

        $employee->update($request->validated());

        return $this->successResponse($employee, 'Employee profile updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return $this->errorResponse('Employee not found or access denied.', 404);
        }

        $employee->delete();

        return $this->successResponse(null, 'Employee profile soft deleted.');
    }

    /**
     * Upload profile picture.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function uploadPhoto(Request $request, string $id): JsonResponse
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return $this->errorResponse('Employee not found.', 404);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->file('photo')) {
            if ($employee->profile_picture_url) {
                $oldPath = str_replace('/storage/', '', $employee->profile_picture_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('photo')->store('employees/photos/' . $employee->company_id, 'public');
            $url = Storage::url($path);

            $employee->update(['profile_picture_url' => $url]);

            return $this->successResponse(['profile_picture_url' => $url], 'Profile picture updated.');
        }

        return $this->errorResponse('Failed to upload profile picture.', 400);
    }

    /**
     * Upload employee document.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function uploadDocument(Request $request, string $id): JsonResponse
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return $this->errorResponse('Employee not found.', 404);
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:100',
        ]);

        if ($request->file('document')) {
            $path = $request->file('document')->store('employees/documents/' . $employee->id, 'public');
            $url = Storage::url($path);

            $document = EmployeeDocument::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'name' => $request->name,
                'type' => $request->type,
                'file_url' => $url,
            ]);

            return $this->successResponse($document, 'Document uploaded successfully.', 201);
        }

        return $this->errorResponse('Failed to upload document.', 400);
    }

    /**
     * Export employee registry to Excel.
     *
     * @return BinaryFileResponse
     */
    public function export(): BinaryFileResponse
    {
        return Excel::download(new EmployeeExport, 'employees.xlsx');
    }

    /**
     * Import employee database from Excel/CSV.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        Excel::import(new EmployeeImport, $request->file('file'));

        return $this->successResponse(null, 'Employee database imported successfully.');
    }
}
