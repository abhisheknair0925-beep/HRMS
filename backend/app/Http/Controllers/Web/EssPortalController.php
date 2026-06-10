<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\EssService;
use App\Services\LeaveService;
use App\Services\AttendanceService;
use App\Models\AttendanceLog;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\EmployeeDocument;
use App\Exceptions\BusinessException;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EssPortalController extends Controller
{
    public function __construct(
        protected EssService $essService,
        protected LeaveService $leaveService,
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Show portal login form.
     */
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->employee) {
            return redirect()->route('ess.dashboard');
        }
        return view('ess.login');
    }

    /**
     * Process portal login credentials.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is currently suspended.']);
            }

            if (!$user->employee) {
                Auth::logout();
                return back()->withErrors(['email' => 'No employee profile linked to this user account.']);
            }

            // Bind tenant context session-wise
            $tenantContext = app(TenantContext::class);
            $tenantContext->setCompany($user->company);

            return redirect()->route('ess.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid email address or password credentials.']);
    }

    /**
     * Terminate portal session.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Display the employee portal dashboard.
     */
    public function dashboard()
    {
        $employee = Auth::user()->employee;
        $data = $this->essService->getDashboardData($employee->id);
        return view('ess.dashboard', $data);
    }

    /**
     * Display attendance history and clock-in form.
     */
    public function attendance()
    {
        $employee = Auth::user()->employee;
        
        $today = now()->toDateString();
        $todayLog = AttendanceLog::where('employee_id', $employee->id)
            ->where('log_date', $today)
            ->first();

        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->orderBy('log_date', 'desc')
            ->limit(30)
            ->get();

        return view('ess.attendance', [
            'employee' => $employee,
            'today_log' => $todayLog,
            'logs' => $logs,
        ]);
    }

    /**
     * Process clock in request from portal.
     */
    public function clockIn(Request $request)
    {
        $employee = Auth::user()->employee;
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            $this->attendanceService->clockIn(
                $employee->id,
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $request->ip()
            );
            return back()->with('status', 'Clock-in recorded successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['attendance' => $e->getMessage()]);
        }
    }

    /**
     * Process clock out request from portal.
     */
    public function clockOut(Request $request)
    {
        $employee = Auth::user()->employee;
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            $this->attendanceService->clockOut(
                $employee->id,
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $request->ip()
            );
            return back()->with('status', 'Clock-out recorded successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['attendance' => $e->getMessage()]);
        }
    }

    /**
     * Display leaves balance and requests.
     */
    public function leave()
    {
        $employee = Auth::user()->employee;

        $balances = LeaveBalance::where('employee_id', $employee->id)
            ->with('leavePolicy')
            ->get();

        $requests = LeaveRequest::where('employee_id', $employee->id)
            ->with('leavePolicy')
            ->orderBy('start_date', 'desc')
            ->get();

        $policies = \App\Models\LeavePolicy::all(); // available types to apply for

        return view('ess.leave', [
            'employee' => $employee,
            'balances' => $balances,
            'requests' => $requests,
            'policies' => $policies,
        ]);
    }

    /**
     * Process leave request submission.
     */
    public function applyLeave(Request $request)
    {
        $employee = Auth::user()->employee;
        $validated = $request->validate([
            'leave_policy_id' => 'required|uuid|exists:leave_policies,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'half_day' => 'sometimes|boolean',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->leaveService->applyForLeave(
                $employee->id,
                $validated['leave_policy_id'],
                $validated['start_date'],
                $validated['end_date'],
                (bool) ($validated['half_day'] ?? false),
                $validated['reason']
            );
            return back()->with('status', 'Leave application submitted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['leave' => $e->getMessage()]);
        }
    }

    /**
     * Display documents and payslip downloads.
     */
    public function documents()
    {
        $employee = Auth::user()->employee;
        
        $documents = EmployeeDocument::where('employee_id', $employee->id)->get();
        $payslips = $this->essService->getPayslipsList($employee->id);

        return view('ess.documents', [
            'employee' => $employee,
            'documents' => $documents,
            'payslips' => $payslips,
        ]);
    }

    /**
     * Display profile details.
     */
    public function profile()
    {
        $employee = Auth::user()->employee;
        return view('ess.profile', [
            'employee' => $employee,
        ]);
    }

    /**
     * Process profile settings update.
     */
    public function updateProfile(Request $request)
    {
        $employee = Auth::user()->employee;
        
        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'ifsc_code' => 'required|string|max:20',
            'emergency_name' => 'required|string|max:100',
            'emergency_relationship' => 'required|string|max:50',
            'emergency_phone' => 'required|string|max:20',
        ]);

        $profileData = [
            'phone' => $validated['phone'],
            'bank_details' => [
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'ifsc_code' => $validated['ifsc_code'],
            ],
            'emergency_contacts' => [
                [
                    'name' => $validated['emergency_name'],
                    'relationship' => $validated['emergency_relationship'],
                    'phone' => $validated['emergency_phone'],
                ]
            ]
        ];

        try {
            $this->essService->updateProfile($employee->id, $profileData);
            return back()->with('status', 'Your profile details have been updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['profile' => $e->getMessage()]);
        }
    }

    /**
     * Render a printable payslip layout.
     */
    public function downloadPayslip(string $payslipId)
    {
        $employee = Auth::user()->employee;
        $payslips = $this->essService->getPayslipsList($employee->id);
        
        // Find matching payslip from mock array
        $payslip = collect($payslips)->firstWhere('id', $payslipId);
        
        if (!$payslip) {
            abort(404, 'Payslip not found.');
        }

        return view('ess.payslip_print', [
            'employee' => $employee,
            'payslip' => $payslip,
        ]);
    }
}
