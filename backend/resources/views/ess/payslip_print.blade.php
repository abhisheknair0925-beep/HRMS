<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payslip['month_name'] }} - {{ $employee->first_name }} {{ $employee->last_name }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Top control bar (hidden on print) */
        .control-bar {
            width: 100%;
            max-width: 800px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .btn {
            background: #6366f1;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background: #4f46e5;
        }

        /* Printable Payslip Card */
        .payslip-container {
            width: 100%;
            max-width: 800px;
            background: white;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .payslip-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }

        .company-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4f46e5;
            letter-spacing: -0.5px;
        }

        .payslip-title {
            text-align: right;
        }

        .payslip-title h2 {
            font-size: 1.25rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #0f172a;
        }

        .payslip-title p {
            color: #64748b;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        /* Employee Details Grid */
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .detail-row {
            display: flex;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .detail-label {
            width: 140px;
            color: #64748b;
            font-weight: 500;
        }

        .detail-val {
            font-weight: 600;
            color: #0f172a;
        }

        /* Ledger Table */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2.5rem;
        }

        .ledger-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            padding: 0.75rem 1rem;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }

        .ledger-table td {
            padding: 1rem;
            font-size: 0.9rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        .amount-col {
            text-align: right;
            font-weight: 500;
        }

        /* Summary Total */
        .summary-block {
            display: flex;
            justify-content: flex-end;
            border-top: 2px solid #e2e8f0;
            padding-top: 1.5rem;
            margin-bottom: 3rem;
        }

        .summary-box {
            width: 300px;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }

        .summary-row.net-pay {
            font-size: 1.15rem;
            font-weight: 700;
            border-top: 1px solid #e2e8f0;
            padding-top: 0.75rem;
            color: #4f46e5;
        }

        .footer {
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 2rem;
            line-height: 1.5;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                color: black;
            }

            .control-bar {
                display: none;
            }

            .payslip-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Control Bar -->
    <div class="control-bar">
        <span style="font-weight: 600;">Payslip Preview</span>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('ess.documents') }}" class="btn" style="background: #64748b;">Back to Portal</a>
            <button class="btn" onclick="window.print()">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:0.25rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print PDF
            </button>
        </div>
    </div>

    <!-- Printable Payslip Wrapper -->
    <div class="payslip-container">
        
        <div class="payslip-header">
            <div>
                <div class="company-logo">{{ $employee->company->name }}</div>
                <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Enterprise Headquarters</p>
            </div>
            
            <div class="payslip-title">
                <h2>Payslip</h2>
                <p>For the Month of {{ $payslip['month_name'] }}</p>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="details-grid">
            <div>
                <div class="detail-row">
                    <span class="detail-label">Employee ID</span>
                    <span class="detail-val">{{ $employee->employee_id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Employee Name</span>
                    <span class="detail-val">{{ $employee->first_name }} {{ $employee->last_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Department</span>
                    <span class="detail-val">{{ $employee->department ? $employee->department->name : 'General' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Designation</span>
                    <span class="detail-val">{{ $employee->designation ? $employee->designation->title : 'Staff' }}</span>
                </div>
            </div>
            
            <div>
                <div class="detail-row">
                    <span class="detail-label">Bank Name</span>
                    <span class="detail-val">{{ $employee->bank_details['bank_name'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Account No.</span>
                    <span class="detail-val">{{ $employee->bank_details['account_number'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">IFSC Code</span>
                    <span class="detail-val">{{ $employee->bank_details['ifsc_code'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Joining Date</span>
                    <span class="detail-val">{{ \Carbon\Carbon::parse($employee->joining_date)->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Ledger Table -->
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Earnings Description</th>
                    <th class="amount-col" style="width: 50%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic Salary</td>
                    <td class="amount-col">${{ number_format($payslip['basic_salary'], 2) }}</td>
                </tr>
                <tr>
                    <td>House Rent Allowance (HRA)</td>
                    <td class="amount-col">${{ number_format($payslip['allowances'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Deductions Table -->
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Deductions Description</th>
                    <th class="amount-col" style="width: 50%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Provident Fund (PF)</td>
                    <td class="amount-col">${{ number_format($payslip['deductions'], 2) }}</td>
                </tr>
                <tr>
                    <td>Professional Tax & TDS</td>
                    <td class="amount-col">$0.00</td>
                </tr>
            </tbody>
        </table>

        <!-- Summary total box -->
        <div class="summary-block">
            <div class="summary-box">
                <div class="summary-row">
                    <span style="color: #64748b;">Gross Earnings</span>
                    <span style="font-weight: 600;">${{ number_format($payslip['basic_salary'] + $payslip['allowances'], 2) }}</span>
                </div>
                <div class="summary-row">
                    <span style="color: #64748b;">Total Deductions</span>
                    <span style="font-weight: 600;">${{ number_format($payslip['deductions'], 2) }}</span>
                </div>
                <div class="summary-row net-pay">
                    <span>Net Salary Payout</span>
                    <span>${{ number_format($payslip['net_pay'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated payslip invoice and does not require a signature.</p>
            <p style="margin-top: 0.25rem;">HumaNode HRMS • Secure Multi-Tenant System</p>
        </div>

    </div>

    <!-- Auto Print Trigger -->
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
            }, 800);
        });
    </script>

</body>
</html>
