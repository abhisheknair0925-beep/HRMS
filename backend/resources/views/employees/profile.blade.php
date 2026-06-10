<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HumaNode HRMS - Employee Profile ({{ $employee->first_name }} {{ $employee->last_name }})</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #1e293b;
            padding: 40px 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%);
            padding: 40px;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .avatar-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            background-color: #334155;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-avatar {
            font-size: 48px;
            font-weight: 700;
            color: #94a3b8;
        }

        .header-info h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .header-info p {
            font-size: 16px;
            color: #cbd5e1;
            margin-bottom: 8px;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-active {
            background-color: #10b981;
            color: #fff;
        }

        .badge-probation {
            background-color: #f59e0b;
            color: #fff;
        }

        .content {
            padding: 40px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e1b4b;
            margin-bottom: 20px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-bottom: 40px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .detail-label {
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 500;
            color: #0f172a;
        }

        .sub-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 30px;
        }

        .sub-table th, .sub-table td {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .sub-table th {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            background-color: #f8fafc;
        }

        .sub-table td {
            font-size: 14px;
            color: #334155;
        }

        /* Print styles */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .container {
                box-shadow: none;
                border: none;
            }
            .header {
                background: #fff !important;
                color: #000 !important;
                border-bottom: 2px solid #000;
                padding: 20px 0;
            }
            .header-info p {
                color: #333 !important;
            }
            .avatar-container {
                border-color: #000;
            }
            .content {
                padding: 20px 0;
            }
            .section-title {
                border-bottom-color: #000;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Profile Header -->
        <div class="header">
            <div class="avatar-container">
                @if($employee->profile_picture_url)
                    <img src="{{ $employee->profile_picture_url }}" alt="Profile Photo" class="avatar">
                @else
                    <div class="no-avatar">
                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="header-info">
                <h1>{{ $employee->first_name }} {{ $employee->last_name }}</h1>
                <p>Employee ID: {{ $employee->employee_id }} | Joining Date: {{ $employee->joining_date->format('d M Y') }}</p>
                <span class="badge badge-{{ strtolower($employee->status) }}">{{ $employee->status }}</span>
            </div>
        </div>

        <div class="content">
            <!-- Employment & Personal Information -->
            <h2 class="section-title">Personal & Employment Details</h2>
            <div class="grid">
                <div class="detail-item">
                    <span class="detail-label">Email Address</span>
                    <span class="detail-value">{{ $employee->email ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Phone Number</span>
                    <span class="detail-value">{{ $employee->phone ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Gender</span>
                    <span class="detail-value">{{ $employee->gender ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date of Birth</span>
                    <span class="detail-value">{{ $employee->dob ? $employee->dob->format('d M Y') : 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Passport / ID Number</span>
                    <span class="detail-value">{{ $employee->personal_info['passport_number'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">National ID (SSN)</span>
                    <span class="detail-value">{{ $employee->personal_info['national_id'] ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Bank Details -->
            <h2 class="section-title">Bank Information</h2>
            <div class="grid">
                <div class="detail-item">
                    <span class="detail-label">Bank Name</span>
                    <span class="detail-value">{{ $employee->bank_details['bank_name'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Account Number</span>
                    <span class="detail-value">{{ $employee->bank_details['account_number'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Branch Code / Routing</span>
                    <span class="detail-value">{{ $employee->bank_details['branch_code'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">SWIFT / BIC Code</span>
                    <span class="detail-value">{{ $employee->bank_details['swift_code'] ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Emergency Contacts -->
            <h2 class="section-title">Emergency Contacts</h2>
            @if(!empty($employee->emergency_contacts))
                <table class="sub-table">
                    <thead>
                        <tr>
                            <th>Contact Name</th>
                            <th>Relationship</th>
                            <th>Phone Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee->emergency_contacts as $contact)
                            <tr>
                                <td>{{ $contact['name'] }}</td>
                                <td>{{ $contact['relationship'] }}</td>
                                <td>{{ $contact['phone'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #64748b; font-size: 14px; margin-bottom: 30px;">No emergency contacts registered.</p>
            @endif

            <!-- Family Details -->
            <h2 class="section-title">Family Information</h2>
            @if(!empty($employee->family_info))
                <table class="sub-table">
                    <thead>
                        <tr>
                            <th>Member Name</th>
                            <th>Relationship</th>
                            <th>Date of Birth</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employee->family_info as $member)
                            <tr>
                                <td>{{ $member['name'] }}</td>
                                <td>{{ $member['relationship'] }}</td>
                                <td>{{ isset($member['dob']) ? now()->parse($member['dob'])->format('d M Y') : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #64748b; font-size: 14px; margin-bottom: 30px;">No family details registered.</p>
            @endif
        </div>
    </div>
</body>
</html>
