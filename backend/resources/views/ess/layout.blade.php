<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HumaNode - Employee Self Service</title>
    
    <!-- Premium Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #020617 100%);
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.15);
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            
            --glass-bg: rgba(30, 41, 59, 0.45);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-blur: blur(16px);
            --card-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.3);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.5);
        }

        /* Glassmorphic Navigation Sidebar */
        .sidebar {
            width: 280px;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-right: 1px solid var(--glass-border);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-dot {
            width: 8px;
            height: 8px;
            background: #a855f7;
            border-radius: 50%;
            display: inline-block;
        }

        .nav-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex-grow: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.85rem 1rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.04);
            border-color: var(--glass-border);
            transform: translateX(4px);
        }

        .nav-item.active {
            background: var(--primary-gradient);
            box-shadow: 0 4px 20px var(--primary-glow);
            border: none;
        }

        .user-summary {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: auto;
        }

        .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .user-details {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .logout-btn {
            background: transparent;
            border: none;
            color: #ef4444;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            margin-left: auto;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        /* Main Content Panel */
        .main-wrapper {
            margin-left: 280px;
            flex-grow: 1;
            padding: 2.5rem;
            min-height: 100vh;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .welcome-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 600;
        }

        .welcome-sub {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 0.25rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        /* Glass Cards */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        /* Form Controls */
        .form-control {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .form-input {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            color: var(--text-main);
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Alert notifications */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .btn {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            box-shadow: none;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 2rem 0.5rem;
                align-items: center;
            }
            .logo span:not(.logo-dot), .nav-item span, .user-details {
                display: none;
            }
            .logo {
                justify-content: center;
            }
            .nav-item {
                justify-content: center;
                padding: 1rem;
            }
            .main-wrapper {
                margin-left: 80px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Portal Sidebar -->
    <div class="sidebar">
        <div class="logo">
            HumaNode<span class="logo-dot"></span>
        </div>
        
        <div class="nav-group">
            <a href="{{ route('ess.dashboard') }}" class="nav-item {{ Route::is('ess.dashboard') ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('ess.attendance') }}" class="nav-item {{ Route::is('ess.attendance') ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Attendance</span>
            </a>
            <a href="{{ route('ess.leave') }}" class="nav-item {{ Route::is('ess.leave') ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Leaves</span>
            </a>
            <a href="{{ route('ess.documents') }}" class="nav-item {{ Route::is('ess.documents') ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Documents</span>
            </a>
            <a href="{{ route('ess.profile') }}" class="nav-item {{ Route::is('ess.profile') ? 'active' : '' }}">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Profile</span>
            </a>
        </div>

        <div class="user-summary">
            <div class="avatar">
                {{ substr(Auth::user()->name, 0, 2) }}
            </div>
            <div class="user-details">
                <span class="user-name">{{ Auth::user()->name }}</span>
                <span class="user-role">{{ Auth::user()->employee->designation->title ?? 'Employee' }}</span>
            </div>
            <form action="{{ route('ess.logout') }}" method="POST" id="logout-form">
                @csrf
                <button type="submit" class="logout-btn" title="Logout">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <div class="header">
            <div>
                <h1 class="welcome-title">@yield('page_title', 'Employee Self Service')</h1>
                <p class="welcome-sub">@yield('page_subtitle')</p>
            </div>
            <div class="header-actions">
                @yield('header_actions')
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>
