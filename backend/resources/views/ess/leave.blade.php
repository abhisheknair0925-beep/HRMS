@extends('ess.layout')

@section('page_title', 'Leave Management')
@section('page_subtitle', 'Check your leave balance, apply for leaves, and track approval status.')

@section('header_actions')
    <button class="btn" onclick="openModal()">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Apply For Leave
    </button>
@endsection

@section('styles')
<style>
    /* Balance Cards */
    .balances-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .balance-card {
        text-align: center;
        padding: 2rem 1.5rem;
    }

    .balance-policy {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .balance-days {
        font-size: 2.25rem;
        font-weight: 700;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0.75rem 0;
    }

    .balance-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
        display: flex;
        justify-content: space-around;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        padding-top: 0.75rem;
        margin-top: 0.75rem;
    }

    /* Modal Styling */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(8px);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal-backdrop.active {
        display: flex;
        opacity: 1;
    }

    .modal-card {
        width: 100%;
        max-width: 500px;
        background: var(--glass-bg);
        backdrop-filter: var(--glass-blur);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 2.5rem;
        box-shadow: var(--card-shadow);
        transform: translateY(-20px);
        transition: transform 0.3s ease;
    }

    .modal-backdrop.active .modal-card {
        transform: translateY(0);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .modal-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .close-btn {
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 1.5rem;
        line-height: 1;
        padding: 0.25rem;
    }

    .close-btn:hover {
        color: var(--text-main);
    }

    /* Table Styles */
    .requests-table-wrapper {
        overflow-x: auto;
    }

    .requests-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.9rem;
    }

    .requests-table th {
        color: var(--text-muted);
        font-weight: 600;
        padding: 1rem;
        border-bottom: 1px solid var(--glass-border);
    }

    .requests-table td {
        padding: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }

    .requests-table tr:hover {
        background: rgba(255, 255, 255, 0.01);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-approved { background: rgba(16, 185, 129, 0.15); color: #34d399; }
    .badge-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
    .badge-rejected { background: rgba(239, 68, 68, 0.15); color: #fca5a5; }
</style>
@endsection

@section('content')
    <!-- Balances Section -->
    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 600; margin-bottom: 1.25rem;">Leave Balances</h3>
    <div class="balances-grid">
        @if ($balances->isEmpty())
            <div class="glass-card" style="grid-column: 1 / -1; text-align: center; color: var(--text-muted);">
                No leave allocations configured for your profile. Please contact HR.
            </div>
        @else
            @foreach ($balances as $balance)
                @php
                    $remaining = $balance->allocated_days - $balance->used_days - $balance->encashed_days;
                @endphp
                <div class="glass-card balance-card">
                    <p class="balance-policy">{{ $balance->leavePolicy->name }}</p>
                    <p class="balance-days">{{ $remaining }}</p>
                    <p style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Days Remaining</p>
                    
                    <div class="balance-meta">
                        <div>
                            <p style="font-weight: 600; color: var(--text-main);">{{ $balance->allocated_days }}</p>
                            <p style="font-size: 0.7rem;">Allocated</p>
                        </div>
                        <div>
                            <p style="font-weight: 600; color: var(--text-main);">{{ $balance->used_days }}</p>
                            <p style="font-size: 0.7rem;">Used</p>
                        </div>
                        @if ($balance->encashed_days > 0)
                            <div>
                                <p style="font-weight: 600; color: var(--text-main);">{{ $balance->encashed_days }}</p>
                                <p style="font-size: 0.7rem;">Encashed</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- History Requests List -->
    <div class="glass-card">
        <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Leave Request History</h3>

        @if ($requests->isEmpty())
            <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">No leave applications found.</p>
        @else
            <div class="requests-table-wrapper">
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Policy Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $req)
                            <tr>
                                <td style="font-weight: 500;">{{ $req->leavePolicy ? $req->leavePolicy->name : 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($req->start_date)->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($req->end_date)->format('M d, Y') }}</td>
                                <td>{{ $req->total_days }} {{ Str::plural('day', (float)$req->total_days) }}</td>
                                <td style="max-width: 200px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="{{ $req->reason }}">
                                    {{ $req->reason }}
                                </td>
                                <td>
                                    <span class="status-badge 
                                        @if ($req->status === 'Approved') badge-approved
                                        @elseif ($req->status === 'Pending') badge-pending
                                        @else badge-rejected
                                        @endif">
                                        {{ $req->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Apply Leave Modal -->
    <div class="modal-backdrop" id="leave-modal">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title">Apply for Leave</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <form action="{{ route('ess.leave.apply') }}" method="POST">
                @csrf
                
                <div class="form-control">
                    <label for="leave_policy_id" class="form-label">Leave Type</label>
                    <select name="leave_policy_id" id="leave_policy_id" class="form-input" style="appearance: none;" required>
                        <option value="" disabled selected>Select Leave Type</option>
                        @foreach ($policies as $policy)
                            <option value="{{ $policy->id }}">{{ $policy->name }} (Total {{ $policy->total_days }} Days)</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-control">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-input" required min="{{ now()->toDateString() }}">
                    </div>
                    <div class="form-control">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-input" required min="{{ now()->toDateString() }}">
                    </div>
                </div>

                <div class="form-control" style="display: flex; align-items: center; gap: 0.5rem; margin: 1rem 0;">
                    <input type="checkbox" name="half_day" id="half_day" value="1" style="width: auto; cursor: pointer;">
                    <label for="half_day" style="font-size: 0.85rem; font-weight: 500; cursor: pointer; user-select: none;">Apply as Half Day</label>
                </div>

                <div class="form-control">
                    <label for="reason" class="form-label">Reason for Leave</label>
                    <textarea name="reason" id="reason" class="form-input" rows="4" placeholder="Brief explanation of your leave request..." style="resize: none;" required></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" style="flex: 1;" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn" style="flex: 1;">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const modal = document.getElementById('leave-modal');
    
    function openModal() {
        modal.classList.add('active');
    }
    
    function closeModal() {
        modal.classList.remove('active');
    }

    // Auto update min End Date based on Start Date selection
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    startDateInput.addEventListener('change', () => {
        endDateInput.min = startDateInput.value;
        if (endDateInput.value && endDateInput.value < startDateInput.value) {
            endDateInput.value = startDateInput.value;
        }
    });
</script>
@endsection
