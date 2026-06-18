<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HumaNode - ESS Portal Login</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite CSS/JS compiler -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center overflow-hidden relative select-none antialiased">

    <!-- Decorative Gradient Blobs -->
    <div class="absolute w-[350px] h-[350px] rounded-full bg-primary-500/30 blur-[100px] left-[15%] top-[10%] animate-pulse duration-10000"></div>
    <div class="absolute w-[400px] h-[400px] rounded-full bg-secondary-500/20 blur-[100px] right-[15%] bottom-[10%] animate-pulse duration-10000 delay-3000"></div>

    <!-- Glassmorphic Login Card -->
    <div class="glass-panel w-full max-w-[440px] px-8 py-10 shadow-2xl relative z-10 border border-white/10 mx-4">
        
        <!-- Corporate Brand Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent tracking-wide">
                HumaNode
            </h1>
            <p class="text-xs text-slate-400 font-semibold tracking-widest uppercase mt-2">
                Employee Self Service
            </p>
        </div>

        <!-- Alert Notification Panel -->
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-danger/20 border border-danger/30 text-danger flex items-start gap-2.5 text-xs font-semibold shadow-lg backdrop-blur-sm">
                <span class="text-sm">❌</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Form submissions -->
        <form action="{{ route('ess.login.post') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="space-y-1">
                <label for="email" class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Work Email</label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       class="glass-input text-xs" 
                       placeholder="you@company.com" 
                       required 
                       autofocus 
                       value="{{ old('email') }}">
            </div>

            <div class="space-y-1">
                <label for="password" class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Password</label>
                <input type="password" 
                       name="password" 
                       id="password" 
                       class="glass-input text-xs" 
                       placeholder="••••••••" 
                       required>
            </div>

            <button type="submit" 
                    class="w-full py-3 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-300 btn-glow">
                Sign In
            </button>
        </form>

        <!-- Recovery Utilities -->
        <div class="mt-6 text-center">
            <a href="{{ route('password.request') }}" class="text-xs text-primary-500 hover:text-primary-600 hover:underline font-semibold">
                Forgot your password?
            </a>
        </div>

    </div>

</body>
</html>
