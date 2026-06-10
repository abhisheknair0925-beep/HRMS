@extends('ess.layout')

@section('page_title', 'Profile & Settings')
@section('page_subtitle', 'Manage your contact information, direct deposit bank accounts, and emergency contact details.')

@section('styles')
<style>
    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
    }

    @media (max-width: 992px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Avatar Widget */
    .profile-widget {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 3rem 2rem;
    }

    .large-avatar {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.25rem;
        font-weight: 700;
        color: white;
        border: 4px solid rgba(255, 255, 255, 0.05);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        margin-bottom: 1.5rem;
    }

    .section-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: var(--primary);
    }
</style>
@endsection

@section('content')
<div class="profile-grid">

    <!-- Left Column: Avatar Widget -->
    <div>
        <div class="glass-card profile-widget">
            <div class="large-avatar">
                {{ substr(Auth::user()->name, 0, 2) }}
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 1.25rem;">{{ $employee->first_name }} {{ $employee->last_name }}</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">{{ $employee->employee_id }}</p>
            
            <div style="width: 100%; text-align: left; margin-top: 2rem; display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Department</span>
                    <span style="font-weight: 500;">{{ $employee->department ? $employee->department->name : 'N/A' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Designation</span>
                    <span style="font-weight: 500;">{{ $employee->designation ? $employee->designation->title : 'N/A' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Email</span>
                    <span style="font-weight: 500;">{{ $employee->email }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Joining Date</span>
                    <span style="font-weight: 500;">{{ \Carbon\Carbon::parse($employee->joining_date)->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Settings Form -->
    <div>
        <div class="glass-card">
            <form action="{{ route('ess.profile.update') }}" method="POST">
                @csrf
                
                <!-- Contact info -->
                <h4 class="section-title">Contact Settings</h4>
                <div class="form-control">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-input" value="{{ old('phone', $employee->phone) }}" placeholder="+1 555-0199">
                </div>

                <!-- Bank Info -->
                <h4 class="section-title" style="margin-top: 2.5rem;">Bank Deposit Details</h4>
                <div class="form-control">
                    <label for="bank_name" class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" id="bank_name" class="form-input" value="{{ old('bank_name', $employee->bank_details['bank_name'] ?? '') }}" placeholder="Federal Deposit Bank" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-control">
                        <label for="account_number" class="form-label">Account Number</label>
                        <input type="text" name="account_number" id="account_number" class="form-input" value="{{ old('account_number', $employee->bank_details['account_number'] ?? '') }}" placeholder="0123456789" required>
                    </div>
                    <div class="form-control">
                        <label for="ifsc_code" class="form-label">IFSC Code / Routing No</label>
                        <input type="text" name="ifsc_code" id="ifsc_code" class="form-input" value="{{ old('ifsc_code', $employee->bank_details['ifsc_code'] ?? '') }}" placeholder="FED0012345" required>
                    </div>
                </div>

                <!-- Emergency Contacts -->
                <h4 class="section-title" style="margin-top: 2.5rem;">Emergency Contact</h4>
                @php
                    $emergency = $employee->emergency_contacts[0] ?? null;
                @endphp
                <div class="form-control">
                    <label for="emergency_name" class="form-label">Contact Name</label>
                    <input type="text" name="emergency_name" id="emergency_name" class="form-input" value="{{ old('emergency_name', $emergency['name'] ?? '') }}" placeholder="Jane Doe" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-control">
                        <label for="emergency_relationship" class="form-label">Relationship</label>
                        <input type="text" name="emergency_relationship" id="emergency_relationship" class="form-input" value="{{ old('emergency_relationship', $emergency['relationship'] ?? '') }}" placeholder="Spouse" required>
                    </div>
                    <div class="form-control">
                        <label for="emergency_phone" class="form-label">Emergency Phone</label>
                        <input type="text" name="emergency_phone" id="emergency_phone" class="form-input" value="{{ old('emergency_phone', $emergency['phone'] ?? '') }}" placeholder="+1 555-0100" required>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 2.5rem;">
                    <button type="submit" class="btn">Save Profile Settings</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
