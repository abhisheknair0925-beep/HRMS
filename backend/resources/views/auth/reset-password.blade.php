<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HumaNode HRMS - Update Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311042 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #f8fafc;
            padding: 20px;
        }

        .container {
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            width: 100%;
            max-width: 440px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -1px;
            margin-bottom: 30px;
            background: linear-gradient(90deg, #38bdf8 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #fff;
        }

        .subtitle {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.6);
            color: #fff;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.25);
            background: rgba(15, 23, 42, 0.8);
        }

        .btn {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(90deg, #38bdf8 0%, #8b5cf6 100%);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 16px rgba(139, 92, 246, 0.2);
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(139, 92, 246, 0.35);
        }

        .alert {
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: left;
            border: 1px solid transparent;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.25);
            color: #f87171;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">HumaNode</div>
        <h2 class="title">Create New Password</h2>
        <p class="subtitle">Set your new password to regain access to your account.</p>

        @if($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ request()->email ?? '' }}" required readonly>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">New Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autofocus>
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn">Update Password</button>
        </form>
    </div>
</body>
</html>
