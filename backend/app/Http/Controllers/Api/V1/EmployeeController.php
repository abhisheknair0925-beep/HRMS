<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Exports\EmployeeExport;
use App\Imports\EmployeeImport;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            ->with(['user.roles', 'department', 'designation', 'manager', 'managerProfile'])
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->paginate((int) ($request->per_page ?? 50));

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
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['Admin', 'HR'])) {
            return $this->errorResponse('Access denied. Only Admins or HR can register new employees.', 403);
        }

        $validated = $request->validated();
        $roleName = $validated['role_name'] ?? null;
        unset($validated['role_name']);

        if (!empty($validated['email']) && empty($validated['user_id'])) {
            $newUser = User::create([
                'company_id' => $user->company_id,
                'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                'email' => $validated['email'],
                'password' => Hash::make('Welcome@HumaNode123'),
                'is_active' => true,
            ]);

            if ($roleName) {
                $newUser->syncRoles([$roleName]);
            }

            $validated['user_id'] = $newUser->id;
        }

        $employee = Employee::create($validated)->load(['user.roles', 'department', 'designation', 'manager']);
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
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $employee = Employee::with('documents')->find($id);

        if (!$employee || $employee->company_id !== $user->company_id) {
            return $this->errorResponse('Employee not found or access denied.', 404);
        }

        // BOLA / IDOR check: Employees can only view their own master record
        if (!$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            $userEmployee = $user->employee;
            if (!$userEmployee || $userEmployee->id !== $id) {
                return $this->errorResponse('Access denied. You can only view your own profile details.', 403);
            }
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
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $employee = Employee::find($id);

        if (!$employee || $employee->company_id !== $user->company_id) {
            return $this->errorResponse('Employee not found or access denied.', 404);
        }

        // Only Admins or HR can edit master employee records
        if (!$user->hasAnyRole(['Admin', 'HR'])) {
            return $this->errorResponse('Access denied. Only Admins or HR can edit master employee records.', 403);
        }

        $validated = $request->validated();
        $roleName = $validated['role_name'] ?? null;
        unset($validated['role_name']);

        $employee->update($validated);

        if ($employee->user) {
            $employee->user->update([
                'name' => trim("{$employee->first_name} {$employee->last_name}"),
                'email' => $employee->email ?: $employee->user->email,
            ]);

            if ($roleName) {
                $employee->user->syncRoles([$roleName]);
            }
        }

        return $this->successResponse(
            $employee->fresh(['user.roles', 'department', 'designation', 'manager', 'managerProfile']),
            'Employee profile updated successfully.'
        );
    }

    /**
     * Remove the specified employee from storage.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $employee = Employee::find($id);

        if (!$employee || $employee->company_id !== $user->company_id) {
            return $this->errorResponse('Employee not found or access denied.', 404);
        }

        if (!$user->hasAnyRole(['Admin', 'HR'])) {
            return $this->errorResponse('Access denied. Only Admins or HR can delete employees.', 403);
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
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $employee = Employee::find($id);

        if (!$employee || $employee->company_id !== $user->company_id) {
            return $this->errorResponse('Employee not found.', 404);
        }

        // Standard employees can only update their own photo
        if (!$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            $userEmployee = $user->employee;
            if (!$userEmployee || $userEmployee->id !== $id) {
                return $this->errorResponse('Access denied. You can only update your own photo.', 403);
            }
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
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $employee = Employee::find($id);

        if (!$employee || $employee->company_id !== $user->company_id) {
            return $this->errorResponse('Employee not found.', 404);
        }

        // Standard employees can only upload documents for themselves
        if (!$user->hasAnyRole(['Admin', 'HR', 'Manager'])) {
            $userEmployee = $user->employee;
            if (!$userEmployee || $userEmployee->id !== $id) {
                return $this->errorResponse('Access denied. You can only upload documents for yourself.', 403);
            }
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
     * @param Request $request
     * @return mixed
     */
    public function export(Request $request): mixed
    {
        $user = $request->user() ?: auth()->user();
        if (!$user || !$user->hasAnyRole(['Admin', 'HR'])) {
            return $this->errorResponse('Access denied.', 403);
        }
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
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['Admin', 'HR'])) {
            return $this->errorResponse('Access denied.', 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        Excel::import(new EmployeeImport, $request->file('file'));

        return $this->successResponse(null, 'Employee database imported successfully.');
    }
}
