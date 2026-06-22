<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): JsonResponse
    {
        $employees = Employee::all();
        $payrollList = [];

        foreach ($employees as $emp) {
            // Guarantee a payroll record exists for each employee
            $payroll = Payroll::firstOrCreate(
                ['employee_id' => $emp->id],
                [
                    'company_id' => $emp->company_id,
                    'base_pay' => 4000.00,
                    'hra' => 800.00,
                    'allowance' => 400.00,
                    'pf' => 320.00,
                    'tax' => 380.00,
                    'revisions' => [
                        [
                            'id' => 'initial',
                            'reviewer' => 'HR Director',
                            'date' => now()->toDateString(),
                            'previous_base' => 0.00,
                            'new_base' => 4000.00
                        ]
                    ]
                ]
            );

            // Fetch payslips for this employee
            $payslips = Payslip::where('employee_id', $emp->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($slip) {
                    return [
                        'id' => $slip->id,
                        'month' => $slip->month,
                        'net' => $slip->net,
                        'status' => $slip->status,
                    ];
                });

            $payrollList[] = [
                'id' => $payroll->id,
                'employee_id' => $emp->employee_id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'base_pay' => $payroll->base_pay,
                'hra' => $payroll->hra,
                'allowance' => $payroll->allowance,
                'pf' => $payroll->pf,
                'tax' => $payroll->tax,
                'revisions' => $payroll->revisions ?? [],
                'payslips' => $payslips,
            ];
        }

        return $this->successResponse($payrollList, 'Payroll entries list retrieved.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $payroll = Payroll::find($id);
        if (!$payroll) {
            return $this->errorResponse('Payroll structure not found.', 404);
        }

        $validated = $request->validate([
            'base_pay' => 'required|numeric|min:0',
        ]);

        $newBase = (float) $validated['base_pay'];
        $oldBase = (float) $payroll->base_pay;

        // Auto calculate standard structures: HRA (20%), PF (8%), Tax (10%), Allowance (10%)
        $hra = round($newBase * 0.20, 2);
        $allowance = round($newBase * 0.10, 2);
        $pf = round($newBase * 0.08, 2);
        $tax = round($newBase * 0.10, 2);

        $newRevision = [
            'id' => uniqid('rev_'),
            'reviewer' => $request->user()?->name ?? 'Compensation Committee',
            'date' => now()->toDateString(),
            'previous_base' => $oldBase,
            'new_base' => $newBase
        ];

        $revisions = $payroll->revisions ?? [];
        array_unshift($revisions, $newRevision);

        $payroll->update([
            'base_pay' => $newBase,
            'hra' => $hra,
            'allowance' => $allowance,
            'pf' => $pf,
            'tax' => $tax,
            'revisions' => $revisions,
        ]);

        return $this->successResponse($payroll, 'Payroll structure revised successfully.');
    }

    public function generatePayslips(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => 'required|string', // e.g. "June 2026"
        ]);

        $month = $validated['month'];
        $payrolls = Payroll::all();
        $generatedCount = 0;

        foreach ($payrolls as $payroll) {
            // Avoid duplicate payslips for the same month and employee
            $exists = Payslip::where('employee_id', $payroll->employee_id)
                ->where('month', $month)
                ->exists();

            if (!$exists) {
                $gross = $payroll->base_pay + $payroll->hra + $payroll->allowance;
                $deductions = $payroll->pf + $payroll->tax;
                $net = $gross - $deductions;

                Payslip::create([
                    'company_id' => $payroll->company_id,
                    'employee_id' => $payroll->employee_id,
                    'month' => $month,
                    'net' => $net,
                    'status' => 'Released',
                ]);
                $generatedCount++;
            }
        }

        return $this->successResponse(
            ['generated_count' => $generatedCount],
            "Payslips for {$month} generated successfully."
        );
    }
}
