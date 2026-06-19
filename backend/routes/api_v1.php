<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\CompanySettingController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\DesignationController;
use App\Http\Controllers\Api\V1\OrgChartController;
use App\Http\Controllers\Api\V1\ShiftController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\LeavePolicyController;
use App\Http\Controllers\Api\V1\LeaveRequestController;
use App\Http\Controllers\Api\V1\LeaveEncashmentController;
use App\Http\Controllers\Api\V1\PerformanceAppraisalController;
use App\Http\Middleware\TenantMiddleware;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/

// Public Auth Endpoints
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

// Public Tenant Registration
Route::post('companies', [CompanyController::class, 'store']);

// Protected Tenant & Auth Scope Group
Route::middleware(['auth:sanctum', TenantMiddleware::class])->group(function () {
    
    // Auth Control
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::post('profile/update', [EssApiController::class, 'updateProfile']);

    // RBAC Configurations (Restricted to Admins in production via policies)
    Route::middleware('role:Admin')->group(function () {
        Route::get('roles', [RoleController::class, 'index']);
        Route::post('roles', [RoleController::class, 'store']);
        Route::delete('roles/{id}', [RoleController::class, 'destroy']);
        Route::post('roles/assign', [RoleController::class, 'assignRole']);
        Route::post('roles/{id}/permissions', [RoleController::class, 'syncPermissions']);
        Route::get('permissions', [PermissionController::class, 'index']);
    });

    // Company Profiles & Settings
    Route::get('company', [CompanyController::class, 'show']);
    Route::get('settings', [CompanySettingController::class, 'show']);
    Route::middleware('role:Admin|HR')->group(function () {
        Route::put('company', [CompanyController::class, 'update']);
        Route::post('company/logo', [CompanyController::class, 'uploadLogo']);
        Route::put('settings', [CompanySettingController::class, 'update']);
    });

    // Branches CRUD (GET open, write restricted)
    Route::get('branches', [BranchController::class, 'index']);
    Route::get('branches/{id}', [BranchController::class, 'show']);
    Route::middleware('role:Admin|HR|Manager')->group(function () {
        Route::post('branches', [BranchController::class, 'store']);
        Route::put('branches/{id}', [BranchController::class, 'update']);
        Route::delete('branches/{id}', [BranchController::class, 'destroy']);
    });

    // Employee Master
    Route::middleware('role:Admin|HR')->group(function () {
        Route::post('employees/import', [EmployeeController::class, 'import']);
        Route::get('employees/export', [EmployeeController::class, 'export']);
        Route::post('employees/transfer', [OrgChartController::class, 'transfer']);
    });
    
    // Employee photo and documents (has internal BOLA checks, allows Employee/Manager/HR/Admin)
    Route::post('employees/{id}/photo', [EmployeeController::class, 'uploadPhoto']);
    Route::post('employees/{id}/documents', [EmployeeController::class, 'uploadDocument']);
    Route::apiResource('employees', EmployeeController::class);

    // Organization Structure
    Route::get('departments', [DepartmentController::class, 'index']);
    Route::get('departments/{id}', [DepartmentController::class, 'show']);
    Route::middleware('role:Admin|HR|Manager')->group(function () {
        Route::post('departments', [DepartmentController::class, 'store']);
        Route::put('departments/{id}', [DepartmentController::class, 'update']);
        Route::delete('departments/{id}', [DepartmentController::class, 'destroy']);
    });

    Route::get('designations', [DesignationController::class, 'index']);
    Route::get('designations/{id}', [DesignationController::class, 'show']);
    Route::middleware('role:Admin|HR|Manager')->group(function () {
        Route::post('designations', [DesignationController::class, 'store']);
        Route::put('designations/{id}', [DesignationController::class, 'update']);
        Route::delete('designations/{id}', [DesignationController::class, 'destroy']);
    });
    Route::get('org-chart', [OrgChartController::class, 'index']);

    // Attendance Management
    Route::get('shifts', [ShiftController::class, 'index']);
    Route::get('shifts/{id}', [ShiftController::class, 'show']);
    Route::middleware('role:Admin|HR|Manager')->group(function () {
        Route::post('shifts', [ShiftController::class, 'store']);
        Route::put('shifts/{id}', [ShiftController::class, 'update']);
        Route::delete('shifts/{id}', [ShiftController::class, 'destroy']);
    });
    Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('attendance/regularize', [AttendanceController::class, 'regularize']);
    Route::get('attendance/regularizations', [AttendanceController::class, 'regularizationIndex']);
    Route::post('attendance/regularizations/{id}/approve', [AttendanceController::class, 'approveRegularization']);
    Route::get('attendance/report', [AttendanceController::class, 'report']);

    // Leave Management
    Route::get('leave-policies', [LeavePolicyController::class, 'index']);
    Route::get('leave-policies/{id}', [LeavePolicyController::class, 'show']);
    Route::middleware('role:Admin|HR')->group(function () {
        Route::post('leave-policies', [LeavePolicyController::class, 'store']);
        Route::put('leave-policies/{id}', [LeavePolicyController::class, 'update']);
        Route::delete('leave-policies/{id}', [LeavePolicyController::class, 'destroy']);
    });
    Route::post('leaves/apply', [LeaveRequestController::class, 'apply']);
    Route::get('leaves/pending', [LeaveRequestController::class, 'pendingList']);
    Route::post('leaves/{id}/approve', [LeaveRequestController::class, 'approve']);
    Route::post('leaves/{id}/reject', [LeaveRequestController::class, 'reject']);
    Route::get('leaves/balances/{employee_id}', [LeaveRequestController::class, 'balances']);
    Route::post('leaves/encash', [LeaveEncashmentController::class, 'requestEncashment'])->middleware('role:Employee');
    Route::get('leaves/encashments/pending', [LeaveEncashmentController::class, 'indexPending'])->middleware('role:Admin|HR|Manager');
    Route::post('leaves/encashments/{id}/approve', [LeaveEncashmentController::class, 'approveEncashment'])->middleware('role:Admin|HR|Manager');

    // Performance Appraisals
    Route::get('employees/{employee_id}/appraisals', [PerformanceAppraisalController::class, 'index']);
    Route::post('employees/{employee_id}/appraisals', [PerformanceAppraisalController::class, 'store']);
    Route::get('employees/{employee_id}/appraisals/report', [PerformanceAppraisalController::class, 'report']);

    // ESS API V1 Routes
    Route::get('ess/dashboard', [\App\Http\Controllers\Api\V1\EssApiController::class, 'dashboard']);
    Route::get('ess/announcements', [\App\Http\Controllers\Api\V1\EssApiController::class, 'announcements']);
    Route::put('ess/profile', [\App\Http\Controllers\Api\V1\EssApiController::class, 'updateProfile']);
    Route::get('ess/attendance', [\App\Http\Controllers\Api\V1\EssApiController::class, 'attendance']);
    Route::get('ess/leaves', [\App\Http\Controllers\Api\V1\EssApiController::class, 'leaves']);
});
