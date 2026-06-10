@extends('ess.layout')

@section('page_title')
    Welcome, {{ $employee->first_name }}!
@endsection

@section('page_subtitle')
    Here is your dashboard overview for today, {{ now()->format('l, F j, Y') }}.
@endsection

@section('styles')
<style>
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: rgba(99, 102, 241, 0.1);
        border: 1px solid rgba(99, 102, 241, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
    }

    .stat-icon.success {
        background: rgba(16, 185, 129, 0.1);
        border-color: rgba(16, 185, 129, 0.2);
        color: var(--success);
    }

    .stat-icon.warning {
        background: rgba(245, 158, 11, 0.1);
        border-color: rgba(245, 158, 11, 0.2);
        color: var(--warning);
    }

    .stat-value {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        font-weight: 600;
        margin-top: 0.1rem;
    }

    .stat-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Content Layout Split */
    .dashboard-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    @media (max-width: 992px) {
        .dashboard-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Announcements CSS */
    .announcement-item {
        padding: 1.25rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        transition: background 0.2s;
        border-radius: 12px;
    }

    .announcement-item:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .announcement-item:last-child {
        border-bottom: none;
    }

    .announcement-title {
        font-size: 1.05rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .badge-new {
        background: var(--primary-gradient);
        font-size: 0.65rem;
        padding: 0.15rem 0.5rem;
        border-radius: 20px;
        font-weight: bold;
    }

    .announcement-meta {
        font-size: 0.75rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .announcement-body {
        font-size: 0.9rem;
        color: #cbd5e1;
        line-height: 1.5;
    }

    /* Quick Actions */
    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .action-link {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--glass-border);
        border-radius: 14px;
        color: var(--text-main);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s;
    }

    .action-link:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--primary);
        transform: translateY(-2px);
    }

    .action-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(99, 102, 241, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        transition: all 0.3s;
    }

    .action-link:hover .action-icon {
        background: var(--primary-gradient);
        color: white;
    }

    /* Celebrations */
    .celeb-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .celeb-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
    }

    .celeb-icon {
        color: #ec4899;
    }
</style>
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="stats-grid">
        <!-- Leave Card -->
        <div class="glass-card stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="stat-label">Leaves Left</p>
                <p class="stat-value">
                    @php
                        $balance = $leave_balances->first();
                        $left = $balance ? ($balance->allocated_days - $balance->used_days - $balance->encashed_days) : 0;
                    @endphp
                    {{ $left }} / {{ $balance->allocated_days ?? 0 }} Days
                </p>
            </div>
        </div>

        <!-- Attendance Card -->
        <div class="glass-card stat-card">
            <div class="stat-icon success">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="stat-label">Today's Status</p>
                <p class="stat-value">
                    @if ($today_attendance)
                        {{ $today_attendance->status }} 
                        <span style="font-size: 0.8rem; font-weight: normal; color: var(--text-muted);">
                            ({{ \Carbon\Carbon::parse($today_attendance->clock_in)->format('H:i') }})
                        </span>
                    @else
                        Absent
                    @endif
                </p>
            </div>
        </div>

        <!-- Direct Manager Card -->
        <div class="glass-card stat-card">
            <div class="stat-icon warning">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="stat-label">Direct Manager</p>
                <p class="stat-value" style="font-size: 1.15rem;">
                    {{ $employee->manager ? $employee->manager->name : 'No Reporting Manager' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Dashboard Layout Grid -->
    <div class="dashboard-layout">
        
        <!-- Left Panel: Announcements -->
        <div class="glass-card">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Company Announcements
            </h3>

            @if ($announcements->isEmpty())
                <p style="color: var(--text-muted); font-size: 0.95rem; text-align: center; padding: 2rem 0;">No active announcements at this moment.</p>
            @else
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    @foreach ($announcements as $announcement)
                        <div class="announcement-item">
                            <div class="announcement-title">
                                {{ $announcement->title }}
                                @if ($announcement->created_at->gt(now()->subDays(2)))
                                    <span class="badge-new">NEW</span>
                                @endif
                            </div>
                            <div class="announcement-meta">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Published {{ $announcement->created_at->diffForHumans() }}</span>
                                <span>•</span>
                                <span>By {{ $announcement->creator ? $announcement->creator->name : 'HR Administration' }}</span>
                            </div>
                            <div class="announcement-body">
                                {!! nl2br(e($announcement->content)) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right Panel: Quick Actions & Celebrations -->
        <div>
            <!-- Quick Actions -->
            <div class="glass-card">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 600; margin-bottom: 1.25rem;">Quick Actions</h3>
                <div class="quick-actions">
                    <a href="{{ route('ess.attendance') }}" class="action-link">
                        <div class="action-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span>Check In / Out</span>
                    </a>
                    <a href="{{ route('ess.leave') }}" class="action-link">
                        <div class="action-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span>Apply For Leave</span>
                    </a>
                    <a href="{{ route('ess.documents') }}" class="action-link">
                        <div class="action-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span>Download Payslips</span>
                    </a>
                </div>
            </div>

            <!-- Team Celebrations -->
            <div class="glass-card">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 600; margin-bottom: 1.25rem;">Upcoming Birthdays</h3>
                <div class="celeb-list">
                    <div class="celeb-item">
                        <span class="celeb-icon">🎈</span>
                        <div>
                            <p style="font-weight: 600;">Sarah HR</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted);">HR Director • June 15</p>
                        </div>
                    </div>
                    <div class="celeb-item">
                        <span class="celeb-icon">🎂</span>
                        <div>
                            <p style="font-weight: 600;">Alex Dev</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted);">Lead Architect • June 22</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
