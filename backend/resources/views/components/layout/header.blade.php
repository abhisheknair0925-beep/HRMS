<header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-20 select-none">
    
    <!-- Search Everywhere Bar -->
    <div class="flex-1 max-w-lg hidden md:block">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                🔍
            </span>
            <input type="text" 
                   placeholder="Search employees, documents, requests..." 
                   class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 transition-all" />
        </div>
    </div>

    <!-- Right Side Toolbar Controls -->
    <div class="flex items-center gap-4 ml-auto">
        
        <!-- Company & Branch Switcher -->
        @if(auth()->check() && auth()->user()->company)
        <div class="hidden sm:flex items-center bg-slate-50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-1.5 gap-2 text-xs">
            <span class="text-slate-400">🏢</span>
            <span class="font-bold text-slate-700 dark:text-slate-300 truncate max-w-[120px]">
                {{ auth()->user()->company->name ?? 'Default Tenant' }}
            </span>
        </div>
        @endif

        <!-- Dark/Light Theme Switcher -->
        <button @click="darkMode = !darkMode" 
                class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800/80 text-slate-500 dark:text-slate-400 transition-all">
            <!-- Light icon -->
            <span x-show="!darkMode" class="text-sm">🌙</span>
            <!-- Dark icon -->
            <span x-show="darkMode" class="text-sm">☀️</span>
        </button>

        <!-- Notification Center Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" 
                    class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800/80 text-slate-500 dark:text-slate-400 transition-all relative">
                <span class="text-sm">🔔</span>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-danger rounded-full ring-2 ring-white dark:ring-slate-900"></span>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 style="display: none;"
                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl py-2 z-50">
                <div class="px-4 py-2 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <span class="font-bold text-xs">Notifications</span>
                    <span class="text-[10px] text-primary-500 cursor-pointer">Mark all read</span>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800/50 max-h-64 overflow-y-auto">
                    <a href="#" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                        <p class="text-xs font-semibold">Leave Approved</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Your Casual Leave request was approved.</p>
                        <span class="text-[8px] text-slate-500">5m ago</span>
                    </a>
                    <a href="#" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                        <p class="text-xs font-semibold">Attendance Discrepancy</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Late marker added for June 10.</p>
                        <span class="text-[8px] text-slate-500">2h ago</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 outline-none">
                <div class="w-8.5 h-8.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-800 flex items-center justify-center font-bold text-xs">
                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 style="display: none;"
                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl py-2 z-50">
                
                <div class="px-4 py-2 border-b border-slate-200 dark:border-slate-800">
                    <p class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">{{ auth()->user()->name ?? 'Guest User' }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email ?? 'guest@humanode.net' }}</p>
                </div>

                <a href="{{ route('ess.profile') }}" class="block px-4 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60">My Profile</a>
                <a href="{{ route('ess.leave') }}" class="block px-4 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60">Leave Balances</a>
                
                <!-- Logout Form -->
                <form action="{{ route('ess.logout') }}" method="POST" class="border-t border-slate-200 dark:border-slate-800 mt-1">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-xs text-danger hover:bg-danger/10">
                        Logout
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>
