<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" 
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'HumaNode HRMS') }}</title>

    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets Compilation -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen flex antialiased">

    <!-- Sidebar Component -->
    <x-layout.sidebar />

    <!-- Main Content Container -->
    <div class="flex-grow flex flex-col min-h-screen overflow-x-hidden min-w-0">
        
        <!-- Header Component -->
        <x-layout.header />

        <!-- Main Viewport -->
        <main class="flex-grow p-4 md:p-6 lg:p-8">
            <!-- Alert Display -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-success/20 border border-success/30 text-success flex items-center shadow-lg backdrop-blur-sm">
                    <span class="mr-2">✅</span>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-danger/20 border border-danger/30 text-danger flex items-center shadow-lg backdrop-blur-sm">
                    <span class="mr-2">❌</span>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif
            
            {{ $slot }}
        </main>

        <!-- Standard Footer -->
        <footer class="p-4 md:p-6 border-t border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 max-w-7xl mx-auto">
                <span>&copy; {{ date('Y') }} HumaNode HRMS SaaS. All rights reserved.</span>
                <span class="text-slate-500 font-medium">Enterprise SaaS Edition</span>
            </div>
        </footer>
    </div>
</body>
</html>
