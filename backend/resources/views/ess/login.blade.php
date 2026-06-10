<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HumaNode - ESS Portal Login</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #090d16 0%, #111424 50%, #030409 100%);
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.25);
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --glass-bg: rgba(22, 28, 45, 0.45);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-blur: blur(20px);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
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
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Gradient Decorative Blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            z-index: 1;
            opacity: 0.4;
            animation: float 10s ease-in-out infinite alternate;
        }

        .blob-1 {
            width: 350px;
            height: 350px;
            background: #6366f1;
            left: 15%;
            top: 10%;
        }

        .blob-2 {
            width: 400px;
            height: 400px;
            background: #a855f7;
            right: 15%;
            bottom: 10%;
            animation-delay: -3s;
        }

        @keyframes float {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(30px) scale(1.1); }
        }

        /* Glassmorphic Login Container */
        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            z-index: 10;
            position: relative;
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .logo-dot {
            width: 8px;
            height: 8px;
            background: #a855f7;
            border-radius: 50%;
            display: inline-block;
        }

        .sub-title {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 2.5rem;
        }

        .form-control {
            margin-bottom: 1.5rem;
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
            background: rgba(10, 15, 30, 0.7);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 0.85rem 1.1rem;
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
        }

        .btn-submit {
            width: 100%;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.95rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
            box-shadow: 0 4px 20px var(--primary-glow);
        }

        .btn-submit:hover {
            opacity: 0.95;
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(99, 102, 241, 0.4);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bottom-links {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.85rem;
        }

        .bottom-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .bottom-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Blurry Background Elements -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <!-- Login Form Container -->
    <div class="login-card">
        <h1 class="logo">HumaNode<span class="logo-dot"></span></h1>
        <p class="sub-title">Sign in to your ESS Portal</p>

        @if ($errors->any())
            <div class="alert-error">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('ess.login.post') }}" method="POST">
            @csrf
            <div class="form-control">
                <label for="email" class="form-label">Work Email</label>
                <input type="email" name="email" id="email" class="form-input" placeholder="you@company.com" required autofocus value="{{ old('email') }}">
            </div>

            <div class="form-control">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Sign In</button>
        </form>

        <div class="bottom-links">
            <a href="{{ route('password.request') }}">Forgot your password?</a>
        </div>
    </div>

</body>
</html>
