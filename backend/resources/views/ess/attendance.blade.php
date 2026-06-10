@extends('ess.layout')

@section('page_title', 'Attendance Tracking')
@section('page_subtitle', 'Clock in and out, track geofenced coordinate checkpoints, and view your attendance records.')

@section('styles')
<style>
    .attendance-split {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
    }

    @media (max-width: 992px) {
        .attendance-split {
            grid-template-columns: 1fr;
        }
    }

    /* Clock Widget */
    .clock-widget {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 2.5rem 2rem;
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.8) 100%);
    }

    .digital-clock {
        font-family: 'Outfit', sans-serif;
        font-size: 2.75rem;
        font-weight: 700;
        letter-spacing: 1px;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
    }

    .date-display {
        font-size: 0.95rem;
        color: var(--text-muted);
        margin-bottom: 2rem;
        font-weight: 500;
    }

    .geoloc-status {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 0.8rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 2rem;
        width: 100%;
        justify-content: center;
    }

    .geoloc-dot {
        width: 8px;
        height: 8px;
        background: var(--warning);
        border-radius: 50%;
        display: inline-block;
    }

    .geoloc-dot.active {
        background: var(--success);
        box-shadow: 0 0 8px var(--success);
    }

    .geoloc-dot.error {
        background: var(--danger);
        box-shadow: 0 0 8px var(--danger);
    }

    /* Table Styles */
    .logs-table-wrapper {
        overflow-x: auto;
    }

    .logs-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 0.9rem;
    }

    .logs-table th {
        color: var(--text-muted);
        font-weight: 600;
        padding: 1rem;
        border-bottom: 1px solid var(--glass-border);
    }

    .logs-table td {
        padding: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }

    .logs-table tr:hover {
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

    .badge-present { background: rgba(16, 185, 129, 0.15); color: #34d399; }
    .badge-late { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
    .badge-absent { background: rgba(239, 68, 68, 0.15); color: #fca5a5; }
    .badge-halfday { background: rgba(99, 102, 241, 0.15); color: #818cf8; }
</style>
@endsection

@section('content')
<div class="attendance-split">

    <!-- Left Panel: Clock widget -->
    <div>
        <div class="glass-card clock-widget">
            <div class="digital-clock" id="clock">00:00:00</div>
            <div class="date-display" id="date">Loading current date...</div>

            <!-- Geolocation Status -->
            <div class="geoloc-status">
                <span class="geoloc-dot" id="geoloc-dot"></span>
                <span id="geoloc-text">Checking location services...</span>
            </div>

            <!-- Forms -->
            @if (!$today_log)
                <!-- Clock In Form -->
                <form action="{{ route('ess.attendance.clock-in') }}" method="POST" id="attendance-form" style="width: 100%;">
                    @csrf
                    <input type="hidden" name="latitude" id="lat-input">
                    <input type="hidden" name="longitude" id="lng-input">
                    <button type="submit" class="btn" id="submit-btn" style="width: 100%; padding: 1rem;" disabled>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Clock In
                    </button>
                </form>
            @elseif (!$today_log->clock_out)
                <!-- Clock Out Form -->
                <form action="{{ route('ess.attendance.clock-out') }}" method="POST" id="attendance-form" style="width: 100%;">
                    @csrf
                    <input type="hidden" name="latitude" id="lat-input">
                    <input type="hidden" name="longitude" id="lng-input">
                    <button type="submit" class="btn" id="submit-btn" style="width: 100%; padding: 1rem; background: linear-gradient(135deg, #ef4444 0%, #f43f5e 100%);" disabled>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Clock Out
                    </button>
                </form>
            @else
                <!-- Completed Card -->
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 12px; padding: 1rem; color: #34d399; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Daily Attendance Completed
                </div>
            @endif

            <!-- Today's Stats -->
            @if ($today_log)
                <div style="width: 100%; text-align: left; margin-top: 2rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    <h4 style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">TODAY'S SUMMARY</h4>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                        <span style="font-size: 0.9rem; color: var(--text-muted);">Clock In</span>
                        <span style="font-weight: 500;">{{ \Carbon\Carbon::parse($today_log->clock_in)->format('H:i:s') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.5rem;">
                        <span style="font-size: 0.9rem; color: var(--text-muted);">Clock Out</span>
                        <span style="font-weight: 500;">{{ $today_log->clock_out ? \Carbon\Carbon::parse($today_log->clock_out)->format('H:i:s') : '--:--:--' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-size: 0.9rem; color: var(--text-muted);">Total Hours</span>
                        <span style="font-weight: 500;">{{ number_format($today_log->working_minutes / 60, 2) }} hrs</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Panel: Logs list -->
    <div>
        <div class="glass-card">
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Recent Attendance Logs</h3>

            @if ($logs->isEmpty())
                <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">No attendance records found.</p>
            @else
                <div class="logs-table-wrapper">
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Hours</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td style="font-weight: 500;">{{ \Carbon\Carbon::parse($log->log_date)->format('M d, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($log->clock_in)->format('H:i:s') }}</td>
                                    <td>{{ $log->clock_out ? \Carbon\Carbon::parse($log->clock_out)->format('H:i:s') : '--:--:--' }}</td>
                                    <td>{{ number_format($log->working_minutes / 60, 2) }} hrs</td>
                                    <td>
                                        <span class="status-badge 
                                            @if ($log->status === 'Present') badge-present
                                            @elseif ($log->status === 'Late') badge-late
                                            @elseif ($log->status === 'Half-Day') badge-halfday
                                            @else badge-absent
                                            @endif">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    // Live Clock Logic
    function updateClock() {
        const now = new Date();
        const hrs = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        const secs = String(now.getSeconds()).padStart(2, '0');
        
        document.getElementById('clock').textContent = `${hrs}:${mins}:${secs}`;
        
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('date').textContent = now.toLocaleDateString('en-US', options);
    }
    
    updateClock();
    setInterval(updateClock, 1000);

    // Geolocation Retrieval Logic
    const dot = document.getElementById('geoloc-dot');
    const text = document.getElementById('geoloc-text');
    const submitBtn = document.getElementById('submit-btn');
    const latInput = document.getElementById('lat-input');
    const lngInput = document.getElementById('lng-input');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                latInput.value = lat;
                lngInput.value = lng;

                dot.classList.add('active');
                text.textContent = `Location secured (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
                if (submitBtn) submitBtn.disabled = false;
            },
            (error) => {
                dot.classList.add('error');
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        text.textContent = "Location permission denied by browser.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        text.textContent = "Location information unavailable.";
                        break;
                    case error.TIMEOUT:
                        text.textContent = "Request to get location timed out.";
                        break;
                    default:
                        text.textContent = "An unknown location error occurred.";
                }
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else {
        dot.classList.add('error');
        text.textContent = "Geolocation is not supported by this browser.";
    }
</script>
@endsection
