<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\AuthService;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json(['message' => 'HumaNode HRMS Backend running. Please access V1 API.']);
});

// Password recovery UI pages
Route::get('password/reset', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('password/email', function (Request $request, AuthService $authService) {
    $request->validate(['email' => 'required|email']);
    try {
        $token = $authService->sendResetLink($request->email);
        return back()->with('status', 'Password reset link generated successfully. (Mock Send: Token = ' . $token . ')');
    } catch (\Exception $e) {
        return back()->withErrors(['email' => $e->getMessage()]);
    }
})->name('password.email');

Route::get('password/reset/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');

Route::post('password/update', function (Request $request, AuthService $authService) {
    $request->validate([
        'email' => 'required|email',
        'token' => 'required|string',
        'password' => 'required|string|min:8|confirmed',
    ]);
    try {
        $authService->resetPassword($request->email, $request->token, $request->password);
        return response()->json(['success' => true, 'message' => 'Your password has been reset successfully.']);
    } catch (\Exception $e) {
        return back()->withErrors(['email' => $e->getMessage()]);
    }
})->name('password.update');

// Employee Self Service (ESS) Portal Web Routes
use App\Http\Controllers\Web\EssPortalController;

Route::get('ess/login', [EssPortalController::class, 'showLogin'])->name('login');
Route::post('ess/login', [EssPortalController::class, 'login'])->name('ess.login.post');
Route::post('ess/logout', [EssPortalController::class, 'logout'])->name('ess.logout');

Route::middleware(['auth'])->group(function () {
    Route::get('ess/dashboard', [EssPortalController::class, 'dashboard'])->name('ess.dashboard');
    Route::get('ess/attendance', [EssPortalController::class, 'attendance'])->name('ess.attendance');
    Route::post('ess/attendance/clock-in', [EssPortalController::class, 'clockIn'])->name('ess.attendance.clock-in');
    Route::post('ess/attendance/clock-out', [EssPortalController::class, 'clockOut'])->name('ess.attendance.clock-out');
    Route::get('ess/leave', [EssPortalController::class, 'leave'])->name('ess.leave');
    Route::post('ess/leave/apply', [EssPortalController::class, 'applyLeave'])->name('ess.leave.apply');
    Route::get('ess/documents', [EssPortalController::class, 'documents'])->name('ess.documents');
    Route::get('ess/profile', [EssPortalController::class, 'profile'])->name('ess.profile');
    Route::post('ess/profile/update', [EssPortalController::class, 'updateProfile'])->name('ess.profile.update');
    Route::get('ess/payslips/{payslip_id}/download', [EssPortalController::class, 'downloadPayslip'])->name('ess.payslips.download');
});
