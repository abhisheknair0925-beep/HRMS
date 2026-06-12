<aside x-data="{ collapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" 
       x-init="$watch('collapsed', val => localStorage.setItem('sidebarCollapsed', val))"
       :class="collapsed ? 'w-20' : 'w-64'" 
       class="bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 min-h-screen flex flex-col transition-all duration-300 ease-in-out shrink-0 select-none z-30">
    
    <!-- Header/Logo Area -->
    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-slate-800">
        <span x-show="!collapsed" 
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 scale-95"
              x-transition:enter-end="opacity-100 scale-100"
              class="text-xl font-bold bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent tracking-wide">
            HumaNode
        </span>
        <span x-show="collapsed" class="text-xl font-bold text-primary-500 mx-auto">H</span>
        
        <button @click="collapsed = !collapsed" 
                class="p-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 dark:text-slate-300 transition-colors">
            <svg class="w-5 h-5 transform transition-transform duration-300" :class="collapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <!-- Navigation Nodes -->
    <div class="flex-grow overflow-y-auto px-4 py-6 space-y-6">
        
        <!-- Category: ESS (All Employees) -->
        <div>
            <p x-show="!collapsed" class="px-3 text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase mb-2">Employee Portal</p>
            <nav class="space-y-1">
                <a href="{{ route('ess.dashboard') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('ess.dashboard') ? 'bg-primary-500/10 text-primary-500 border border-primary-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <span class="text-lg">🏠</span>
                    <span x-show="!collapsed">Dashboard</span>
                </a>
                
                <a href="{{ route('ess.attendance') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('ess.attendance') ? 'bg-primary-500/10 text-primary-500 border border-primary-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <span class="text-lg">⏱️</span>
                    <span x-show="!collapsed">My Attendance</span>
                </a>

                <a href="{{ route('ess.leave') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('ess.leave') ? 'bg-primary-500/10 text-primary-500 border border-primary-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <span class="text-lg">📅</span>
                    <span x-show="!collapsed">My Leaves</span>
                </a>

                <a href="{{ route('ess.documents') }}" 
                   class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('ess.documents') ? 'bg-primary-500/10 text-primary-500 border border-primary-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200' }}">
                    <span class="text-lg">📂</span>
                    <span x-show="!collapsed">My Documents</span>
                </a>
            </nav>
        </div>

        <!-- Category: MSS (Managers & above) -->
        @canany(['attendance.approve', 'leaves.approve'])
        <div>
            <p x-show="!collapsed" class="px-3 text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase mb-2">Manager Self Service</p>
            <nav class="space-y-1">
                @can('attendance.approve')
                <a href="#" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200 transition-all duration-200">
                    <span class="text-lg">👥</span>
                    <span x-show="!collapsed">Team Attendance</span>
                </a>
                @endcan

                @can('leaves.approve')
                <a href="#" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200 transition-all duration-200">
                    <span class="text-lg">📝</span>
                    <span x-show="!collapsed">Leave Approvals</span>
                </a>
                @endcan
            </nav>
        </div>
        @endcanany

        <!-- Category: HR Operations & Systems -->
        @canany(['employee.create', 'payroll.run', 'tenant.settings'])
        <div>
            <p x-show="!collapsed" class="px-3 text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase mb-2">HR Operations</p>
            <nav class="space-y-1">
                @can('employee.create')
                <a href="#" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200 transition-all duration-200">
                    <span class="text-lg">📇</span>
                    <span x-show="!collapsed">Employee Registry</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200 transition-all duration-200">
                    <span class="text-lg">🏗️</span>
                    <span x-show="!collapsed">Departments</span>
                </a>
                @endcan

                @can('payroll.run')
                <a href="#" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200 transition-all duration-200">
                    <span class="text-lg">💰</span>
                    <span x-show="!collapsed">Payroll Run</span>
                </a>
                @endcan

                @can('tenant.settings')
                <a href="#" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200 transition-all duration-200">
                    <span class="text-lg">⚙️</span>
                    <span x-show="!collapsed">Settings</span>
                </a>
                @endcan
            </nav>
        </div>
        @endcanany

    </div>

    <!-- Quick Profile Card at Bottom -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-primary-500 to-secondary-500 flex items-center justify-center font-bold text-slate-950 text-sm">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
            <div x-show="!collapsed" class="min-w-0 flex-1">
                <p class="text-xs font-bold truncate text-slate-800 dark:text-slate-200">{{ auth()->user()->name ?? 'Guest User' }}</p>
                <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->role ?? 'Employee' }}</p>
            </div>
        </div>
    </div>
</aside>
