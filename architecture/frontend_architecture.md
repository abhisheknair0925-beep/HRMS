# HumaNode HRMS - Frontend Architecture Specification

This document details the production-ready frontend architecture for the **HumaNode HRMS Admin Portal**. It uses **Laravel Blade**, **Tailwind CSS**, and **Alpine.js** for reactive UI interactions (modals, dropdowns, sidebars, multi-step forms).

---

## 🎨 Design System & Tailwind Preset (`tailwind.config.js`)

Our design standard uses **Glassmorphism** for visual overlays, rounded card contours, and high-fidelity HSL colors supporting dark and light themes.

```javascript
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: 'class', // Enables theme switching via class="dark" on <html>
    theme: {
        extend: {
            colors: {
                // Harmonic Dark/Light Accent Palette
                slate: {
                    950: '#030712',
                    900: '#0f172a',
                    800: '#1e293b',
                    700: '#334155',
                    100: '#f1f5f9',
                    50: '#f8fafc',
                },
                primary: {
                    50: '#e0f7fa',
                    500: '#00e5ff', // Cyber Cyan Accent
                    600: '#00b8d4',
                    700: '#00838f',
                },
                secondary: {
                    500: '#8a2be2', // Neon Indigo Accent
                    600: '#731bc9',
                },
                success: '#10b981',
                warning: '#f59e0b',
                danger: '#ef4444',
                info: '#3b82f6',
            },
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            backdropBlur: {
                xs: '2px',
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
```

### Shared CSS Utility Classes (`resources/css/app.css`)
```css
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';

@layer components {
    /* Glassmorphic Panel utility */
    .glass-panel {
        @apply bg-white/40 dark:bg-slate-900/40 backdrop-blur-md border border-white/10 dark:border-slate-800/50 rounded-2xl shadow-xl transition-all duration-300;
    }

    /* Glass Input fields */
    .glass-input {
        @apply w-full px-4 py-2.5 bg-white/10 dark:bg-slate-950/20 border border-white/20 dark:border-slate-800/80 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 backdrop-blur-sm transition-all;
    }

    /* Primary glowing button */
    .btn-glow {
        @apply relative overflow-hidden transition-all duration-300 shadow-[0_0_15px_rgba(0,229,255,0.15)] hover:shadow-[0_0_25px_rgba(0,229,255,0.35)] active:scale-95;
    }
}
```

---

## 📂 Frontend Folder Architecture

Maintain file organization inside the `/resources` folder:

```text
resources/
├── css/
│   └── app.css                    # Tailwind configurations & custom presets
├── js/
│   ├── app.js                     # Loaders for Alpine, Flatpickr, TomSelect, ApexCharts
│   └── charts.js                  # Standardized configuration hooks for dashboards
├── views/
│   ├── layouts/
│   │   └── app.blade.php          # Main Sidebar/Header Layout wrapper
│   ├── components/
│   │   ├── ui/                    # Reusable elements
│   │   │   ├── button.blade.php
│   │   │   ├── card.blade.php
│   │   │   ├── modal.blade.php
│   │   │   └── table.blade.php
│   │   └── layout/
│   │       ├── sidebar.blade.php  # Sidebar with role checking
│   │       └── header.blade.php   # Header with user controls & search
│   └── modules/                   # Module specific CRUD directories
│       ├── companies/             # Company profiles & multi-tenant status
│       ├── employees/             # Employee profile tab panels
│       ├── attendance/            # Clock-in widgets, maps, shift configurations
│       ├── leaves/                # Calendars, approval timelines, requests
│       ├── payroll/               # Base pay configurations, payslip print layouts
│       ├── documents/             # PDF letter generator and signer layouts
│       ├── ats/                   # Candidates Kanban board view
│       └── assets/                # Equipment inventories
```

---

## 🔗 Reusable Blade Components

### 1. The Custom Card (`components/ui/card.blade.php`)
```html
@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'glass-panel p-6']) }}>
    @if($title || $subtitle)
        <div class="mb-4">
            @if($title)
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div>
        {{ $slot }}
    </div>
</div>
```

### 2. Standardized Glassmorphic Modal (`components/ui/modal.blade.php`)
```html
@props(['name', 'title'])

<div x-data="{ show: false }"
     x-show="show"
     x-on:open-modal.window="if ($event.detail.name === '{{ $name }}') show = true"
     x-on:close-modal.window="show = false"
     x-on:keydown.escape.window="show = false"
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
     
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" x-on:click="show = false"></div>

    <!-- Modal Content wrapper -->
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 class="text-lg font-bold text-slate-100" id="modal-title">{{ $title }}</h3>
                <button type="button" x-on:click="show = false" class="text-slate-400 hover:text-slate-200">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mt-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
```

---

## 🏛️ Master Layout Architecture (`layouts/app.blade.php`)

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" x-data="{ darkMode: true }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HumaNode HRMS</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles & Scripts (Vite compilation) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen flex">

    <!-- Sidebar Navigation -->
    <x-layout.sidebar />

    <!-- Main Container -->
    <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
        
        <!-- Header -->
        <x-layout.header />

        <!-- Page Content -->
        <main class="flex-grow p-6 lg:p-8">
            <!-- Alert systems -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-success/20 border border-success/30 text-success flex items-center">
                    {{ session('success') }}
                </div>
            @endif
            
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="p-6 border-t border-slate-200 dark:border-slate-800 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} HumaNode HRMS SaaS. All rights reserved.
        </footer>
    </div>
</body>
</html>
```

---

## 🧭 Permission-Based Navigation (`components/layout/sidebar.blade.php`)

```html
<aside x-data="{ collapsed: false }" 
       :class="collapsed ? 'w-20' : 'w-64'" 
       class="bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 min-h-screen flex flex-col transition-all duration-300">
    
    <!-- Logo Header -->
    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-slate-800">
        <span x-show="!collapsed" class="text-xl font-bold bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">HumaNode</span>
        <button @click="collapsed = !collapsed" class="p-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-400">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Menus Group -->
    <nav class="flex-1 px-4 py-6 space-y-1">
        <a href="{{ route('ess.dashboard') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-slate-100">
            <span>🏠</span>
            <span x-show="!collapsed">Dashboard</span>
        </a>

        <!-- HR Admin specific items -->
        @can('employee.view')
            <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-slate-100">
                <span>👥</span>
                <span x-show="!collapsed">Employee Directory</span>
            </a>
        @endcan

        <!-- Manager specific items -->
        @can('leaves.approve')
            <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-slate-100">
                <span>📝</span>
                <span x-show="!collapsed">Team Leave Requests</span>
            </a>
        @endcan

        <!-- Payroll specific items -->
        @can('payroll.run')
            <a href="#" class="flex items-center space-x-3 px-3 py-2 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-slate-100">
                <span>💰</span>
                <span x-show="!collapsed">Payroll Processing</span>
            </a>
        @endcan
    </nav>
</aside>
```

---

## 📊 High-Fidelity Modules Layout Examples

### 1. Dashboard View (`views/dashboard.blade.php`)
```html
<x-app-layout>
    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-ui.card class="flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500">Total Employees</p>
                <h2 class="text-2xl font-bold mt-1 text-slate-100">128</h2>
            </div>
            <div class="p-3 bg-primary-500/10 text-primary-500 rounded-xl">👥</div>
        </x-ui.card>
        
        <x-ui.card class="flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500">Present Today</p>
                <h2 class="text-2xl font-bold mt-1 text-slate-100">114 <span class="text-sm font-normal text-success">(89%)</span></h2>
            </div>
            <div class="p-3 bg-success/10 text-success rounded-xl">✅</div>
        </x-ui.card>

        <x-ui.card class="flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500">Pending Leaves</p>
                <h2 class="text-2xl font-bold mt-1 text-slate-100">4</h2>
            </div>
            <div class="p-3 bg-warning/10 text-warning rounded-xl">⏳</div>
        </x-ui.card>

        <x-ui.card class="flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500">Active Openings</p>
                <h2 class="text-2xl font-bold mt-1 text-slate-100">12</h2>
            </div>
            <div class="p-3 bg-secondary-500/10 text-secondary-500 rounded-xl">💼</div>
        </x-ui.card>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-ui.card class="col-span-2" title="Attendance Trend" subtitle="Monthly presence rate vs late flags">
            <div id="attendanceChart" class="h-80"></div>
        </x-ui.card>
        
        <x-ui.card title="Pending Actions" subtitle="Tasks requiring immediate signature or approval">
            <ul class="space-y-3 mt-4">
                <li class="p-3 bg-slate-800/40 rounded-xl flex justify-between items-center">
                    <div>
                        <p class="text-sm font-semibold">Abhishek Nair</p>
                        <p class="text-xs text-slate-400">Casual Leave (2 days)</p>
                    </div>
                    <button class="px-3 py-1 bg-primary-500 text-slate-950 font-bold rounded-lg text-xs hover:bg-primary-400">Review</button>
                </li>
            </ul>
        </x-ui.card>
    </div>

    <!-- Load ApexCharts Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var options = {
                chart: { type: 'area', height: 320, toolbar: { show: false } },
                colors: ['#00e5ff', '#8a2be2'],
                series: [
                    { name: 'Present', data: [94, 95, 93, 97, 96, 95, 98] },
                    { name: 'Late', data: [5, 4, 8, 3, 2, 5, 1] }
                ],
                xaxis: { categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] }
            };
            var chart = new ApexCharts(document.querySelector("#attendanceChart"), options);
            chart.render();
        });
    </script>
</x-app-layout>
```

### 2. Recruitment Kanban Pipeline (`views/ats/pipeline.blade.php`)
```html
<x-app-layout>
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold">Candidate Pipeline</h2>
            <p class="text-sm text-slate-400">Manage applicants across interview stages</p>
        </div>
        <button class="px-4 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl btn-glow">
            + Post Job Opening
        </button>
    </div>

    <!-- Kanban Wrapper -->
    <div class="flex gap-6 overflow-x-auto pb-4">
        
        <!-- Stage: Applied -->
        <div class="flex-shrink-0 w-80 bg-slate-900/40 border border-slate-800/80 p-4 rounded-2xl flex flex-col h-[70vh]">
            <h3 class="font-bold text-sm text-slate-200 mb-3 flex items-center justify-between">
                <span>Applied</span>
                <span class="px-2 py-0.5 bg-slate-800 rounded text-xs">3</span>
            </h3>
            
            <div class="space-y-3 flex-1 overflow-y-auto">
                <div class="p-4 bg-slate-800/80 border border-slate-700/50 rounded-xl cursor-grab active:cursor-grabbing">
                    <p class="font-bold text-sm">John Doe</p>
                    <p class="text-xs text-slate-400 mt-1">Laravel Developer</p>
                    <div class="mt-3 flex justify-between items-center">
                        <span class="text-xs px-2 py-0.5 bg-primary-500/10 text-primary-500 rounded">Match: 92%</span>
                        <span class="text-xs text-slate-500">2d ago</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stage: Interviewing -->
        <div class="flex-shrink-0 w-80 bg-slate-900/40 border border-slate-800/80 p-4 rounded-2xl flex flex-col h-[70vh]">
            <h3 class="font-bold text-sm text-slate-200 mb-3 flex items-center justify-between">
                <span>Interviewing</span>
                <span class="px-2 py-0.5 bg-slate-800 rounded text-xs">1</span>
            </h3>
            
            <div class="space-y-3 flex-1 overflow-y-auto">
                <div class="p-4 bg-slate-800/80 border border-slate-700/50 rounded-xl cursor-grab">
                    <p class="font-bold text-sm">Jane Smith</p>
                    <p class="text-xs text-slate-400 mt-1">Product Designer</p>
                    <div class="mt-3 flex justify-between items-center">
                        <span class="text-xs px-2 py-0.5 bg-secondary-500/10 text-secondary-500 rounded">Match: 88%</span>
                        <span class="text-xs text-slate-500">1d ago</span>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</x-app-layout>
```

### 3. Geofenced Clock-In View (`views/attendance/geo.blade.php`)
```html
<x-app-layout>
    <x-ui.card class="max-w-md mx-auto text-center" title="Clock-In Center" subtitle="Enter coordinates verification zone">
        <div x-data="gpsResolver()" x-init="checkSupport()" class="space-y-6 mt-6">
            
            <!-- Dial Pulse Indicator -->
            <div class="relative w-40 h-40 mx-auto flex items-center justify-center">
                <div :class="insideGeofence ? 'bg-success/20 animate-ping' : 'bg-danger/20'" class="absolute inset-0 rounded-full"></div>
                <div :class="insideGeofence ? 'bg-success/90 text-white' : 'bg-slate-800 text-slate-400'" 
                     class="relative w-32 h-32 rounded-full border border-white/10 flex flex-col items-center justify-center font-bold text-sm cursor-pointer shadow-lg">
                    <span x-text="insideGeofence ? 'CLOCK IN' : 'BLOCKED'"></span>
                    <span class="text-[10px] opacity-75 mt-1" x-text="distanceMsg"></span>
                </div>
            </div>

            <!-- GPS coordinate details -->
            <div class="p-3 bg-slate-950/40 rounded-xl text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-400">Your Lat:</span>
                    <span class="font-mono text-slate-200" x-text="lat">--</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Your Lng:</span>
                    <span class="font-mono text-slate-200" x-text="lng">--</span>
                </div>
            </div>

            <button @click="getLocation()" class="w-full py-2 bg-slate-800 text-slate-200 text-xs rounded-xl hover:bg-slate-700">
                🔄 Update Location
            </button>
        </div>
    </x-ui.card>

    <script>
        function gpsResolver() {
            return {
                lat: '--',
                lng: '--',
                insideGeofence: false,
                distanceMsg: 'Locating...',
                officeLat: 25.2048,
                officeLng: 55.2708,
                checkSupport() {
                    this.getLocation();
                },
                getLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.lat = pos.coords.latitude;
                                this.lng = pos.coords.longitude;
                                this.calculateDistance();
                            },
                            () => {
                                this.distanceMsg = 'GPS Access Denied';
                            }
                        );
                    }
                },
                calculateDistance() {
                    // Haversine formula calculation
                    const R = 6371e3; // metres
                    const phi1 = this.lat * Math.PI/180;
                    const phi2 = this.officeLat * Math.PI/180;
                    const deltaPhi = (this.officeLat-this.lat) * Math.PI/180;
                    const deltaLambda = (this.officeLng-this.lng) * Math.PI/180;

                    const a = Math.sin(deltaPhi/2) * Math.sin(deltaPhi/2) +
                              Math.cos(phi1) * Math.cos(phi2) *
                              Math.sin(deltaLambda/2) * Math.sin(deltaLambda/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    const distance = R * c; // in metres

                    this.insideGeofence = distance <= 100; // 100 meters boundary
                    this.distanceMsg = this.insideGeofence ? 'Within Range' : `${Math.round(distance)}m Away`;
                }
            }
        }
    </script>
</x-app-layout>
```
