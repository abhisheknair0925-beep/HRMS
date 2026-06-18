<x-layouts.app>
    <!-- Welcome Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
                Welcome, <span class="bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">{{ $employee->first_name }}!</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Here is your dashboard overview for today, <span class="font-semibold text-slate-700 dark:text-slate-300">{{ now()->format('l, F j, Y') }}</span>.
            </p>
        </div>
    </div>

    <!-- Quick Stats Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- Leave Card -->
        <div class="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform">
            <div class="w-14 h-14 rounded-2xl bg-primary-500/10 border border-primary-500/20 flex items-center justify-center text-primary-500 text-2xl">
                📅
            </div>
            <div>
                <p class="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Leaves Left</p>
                <p class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                    @php
                        $balance = $leave_balances->first();
                        $left = $balance ? ($balance->allocated_days - $balance->used_days - $balance->encashed_days) : 0;
                    @endphp
                    <span class="text-primary-500">{{ $left }}</span> / {{ $balance->allocated_days ?? 0 }} Days
                </p>
            </div>
        </div>

        <!-- Attendance Card -->
        <div class="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform">
            @if ($today_attendance)
                <div class="w-14 h-14 rounded-2xl bg-success/10 border border-success/20 flex items-center justify-center text-success text-2xl">
                    ✅
                </div>
                <div>
                    <p class="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Today's Status</p>
                    <p class="text-xl font-bold text-success mt-1">
                        {{ $today_attendance->status }} 
                        <span class="text-xs font-normal text-slate-400">
                            ({{ \Carbon\Carbon::parse($today_attendance->clock_in)->format('H:i') }})
                        </span>
                    </p>
                </div>
            @else
                <div class="w-14 h-14 rounded-2xl bg-danger/10 border border-danger/20 flex items-center justify-center text-danger text-2xl">
                    ❌
                </div>
                <div>
                    <p class="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Today's Status</p>
                    <p class="text-xl font-bold text-danger mt-1">Absent / Clock-Out</p>
                </div>
            @endif
        </div>

        <!-- Direct Manager Card -->
        <div class="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform">
            <div class="w-14 h-14 rounded-2xl bg-warning/10 border border-warning/20 flex items-center justify-center text-warning text-2xl">
                💼
            </div>
            <div>
                <p class="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Direct Manager</p>
                <p class="text-base font-bold text-slate-800 dark:text-slate-100 truncate mt-1 max-w-[170px]" title="{{ $employee->manager ? $employee->manager->name : 'No Reporting Manager' }}">
                    {{ $employee->manager ? $employee->manager->name : 'No Reporting Manager' }}
                </p>
            </div>
        </div>

    </div>

    <!-- Main Content Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Columns: Announcements Feed -->
        <div class="lg:col-span-2 glass-panel p-6">
            <div class="flex items-center gap-2 mb-6 border-b border-slate-200 dark:border-slate-800/80 pb-4">
                <span class="text-xl">📢</span>
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Company Announcements</h2>
            </div>

            @if ($announcements->isEmpty())
                <div class="text-center py-12 text-slate-400">
                    <span class="text-4xl block mb-2">📭</span>
                    <p class="text-sm">No active announcements at this moment.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($announcements as $announcement)
                        <div class="p-5 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/60 rounded-2xl hover:border-primary-500/30 transition-all duration-300">
                            <div class="flex items-center justify-between gap-4">
                                <h4 class="font-bold text-sm md:text-base text-slate-900 dark:text-slate-100">
                                    {{ $announcement->title }}
                                </h4>
                                @if ($announcement->created_at->gt(now()->subDays(2)))
                                    <span class="px-2 py-0.5 bg-gradient-to-r from-primary-500 to-secondary-500 text-[9px] font-black text-slate-950 rounded-full">
                                        NEW
                                    </span>
                                @endif
                            </div>
                            
                            <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-1.5 font-medium">
                                <span>📅 {{ $announcement->created_at->diffForHumans() }}</span>
                                <span>•</span>
                                <span>👤 {{ $announcement->creator ? $announcement->creator->name : 'HR Admin' }}</span>
                            </div>

                            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-300 mt-4 leading-relaxed whitespace-pre-line">
                                {!! nl2br(e($announcement->content)) !!}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right 1 Column: Quick Actions & Celebrations -->
        <div class="space-y-8">
            
            <!-- Quick Actions Card -->
            <div class="glass-panel p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4 border-b border-slate-200 dark:border-slate-800/80 pb-2">Quick Actions</h3>
                <div class="space-y-3">
                    
                    <a href="{{ route('ess.attendance') }}" class="flex items-center justify-between p-3.5 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-xl hover:border-primary-500 hover:scale-[1.01] transition-all duration-300 group">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-500 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-slate-950 transition-colors">⏱️</span>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Clock In / Out</span>
                        </div>
                        <span class="text-xs text-slate-400 group-hover:translate-x-1 transition-transform">➡️</span>
                    </a>

                    <a href="{{ route('ess.leave') }}" class="flex items-center justify-between p-3.5 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-xl hover:border-primary-500 hover:scale-[1.01] transition-all duration-300 group">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-500 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-slate-950 transition-colors">📅</span>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Apply for Leave</span>
                        </div>
                        <span class="text-xs text-slate-400 group-hover:translate-x-1 transition-transform">➡️</span>
                    </a>

                    <a href="{{ route('ess.documents') }}" class="flex items-center justify-between p-3.5 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-xl hover:border-primary-500 hover:scale-[1.01] transition-all duration-300 group">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-500 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-slate-950 transition-colors">📂</span>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">My Payslips</span>
                        </div>
                        <span class="text-xs text-slate-400 group-hover:translate-x-1 transition-transform">➡️</span>
                    </a>

                </div>
            </div>

            <!-- Team Celebrations Card -->
            <div class="glass-panel p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-4 border-b border-slate-200 dark:border-slate-800/80 pb-2">Upcoming Birthdays</h3>
                <div class="space-y-4">
                    
                    <div class="flex items-center gap-3">
                        <span class="text-2xl animate-bounce">🎈</span>
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Sarah HR</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">HR Director • June 15</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🎂</span>
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Alex Dev</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Lead Architect • June 22</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</x-layouts.app>
