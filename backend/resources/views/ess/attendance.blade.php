<x-layouts.app>
    <!-- Header Summary -->
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Attendance Tracking</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Clock in and out, track geofenced checkpoints, and view your historical check-in records.
        </p>
    </div>

    <!-- Attendance Grid split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Left Side: Live Clock & Action Panel -->
        <div class="glass-panel p-6 flex flex-col items-center text-center">
            
            <!-- Live Clock and Date using Alpine -->
            <div x-data="{ time: '', date: '' }" 
                 x-init="
                    const updateTime = () => {
                        const now = new Date();
                        time = now.toTimeString().split(' ')[0];
                        date = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                    };
                    updateTime();
                    setInterval(updateTime, 1000);
                 }">
                <h2 x-text="time" class="text-4xl md:text-5xl font-extrabold tracking-wide font-sans bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">
                    00:00:00
                </h2>
                <p x-text="date" class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-2">
                    Loading current date...
                </p>
            </div>

            <!-- Geolocation Status -->
            <div x-data="geoHandler()" 
                 x-init="initGeo()"
                 class="w-full mt-6 space-y-4">
                
                <div class="flex items-center justify-center gap-2 p-3 bg-slate-100 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold">
                    <span :class="{
                        'bg-success animate-ping': isSecured,
                        'bg-danger': isError,
                        'bg-warning': !isSecured && !isError
                    }" class="w-2.5 h-2.5 rounded-full inline-block"></span>
                    <span x-text="statusText" class="text-slate-600 dark:text-slate-400">Loading location services...</span>
                </div>

                <!-- Clock In/Out Actions -->
                @if (!$today_log)
                    <!-- Clock In Form -->
                    <form action="{{ route('ess.attendance.clock-in') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="latitude" :value="lat">
                        <input type="hidden" name="longitude" :value="lng">
                        <button type="submit" 
                                :disabled="!isSecured"
                                :class="isSecured ? 'bg-primary-500 text-slate-950 font-bold hover:bg-primary-400 shadow-[0_0_15px_rgba(0,229,255,0.2)]' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed'"
                                class="w-full py-3.5 rounded-xl text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-all duration-300">
                            ⏱️ Clock In
                        </button>
                    </form>
                @elseif (!$today_log->clock_out)
                    <!-- Clock Out Form -->
                    <form action="{{ route('ess.attendance.clock-out') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="latitude" :value="lat">
                        <input type="hidden" name="longitude" :value="lng">
                        <button type="submit" 
                                :disabled="!isSecured"
                                :class="isSecured ? 'bg-danger text-white font-bold hover:bg-danger/80 shadow-[0_0_15px_rgba(239,68,68,0.2)]' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed'"
                                class="w-full py-3.5 rounded-xl text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-all duration-300">
                            ⏱️ Clock Out
                        </button>
                    </form>
                @else
                    <!-- Shift Completed Info -->
                    <div class="w-full py-3.5 bg-success/15 border border-success/30 rounded-xl text-xs font-bold text-success flex items-center justify-center gap-2">
                        ✅ Daily Attendance Completed
                    </div>
                @endif
            </div>

            <!-- Today's Attendance Details -->
            @if ($today_log)
                <div class="w-full text-left mt-8 border-t border-slate-200 dark:border-slate-800/80 pt-6 space-y-3">
                    <h4 class="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">TODAY'S SUMMARY</h4>
                    
                    <div class="flex justify-between items-center text-xs pb-2 border-b border-slate-100 dark:border-slate-800/50">
                        <span class="text-slate-500">Clock In:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">
                            {{ \Carbon\Carbon::parse($today_log->clock_in)->format('H:i:s') }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center text-xs pb-2 border-b border-slate-100 dark:border-slate-800/50">
                        <span class="text-slate-500">Clock Out:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">
                            {{ $today_log->clock_out ? \Carbon\Carbon::parse($today_log->clock_out)->format('H:i:s') : '--:--:--' }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500">Total Hours:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">
                            {{ number_format($today_log->working_minutes / 60, 2) }} hrs
                        </span>
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Side: Recent Logs Table -->
        <div class="lg:col-span-2 glass-panel p-6">
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-6 border-b border-slate-200 dark:border-slate-800/80 pb-4">
                Recent Attendance Logs
            </h3>

            @if ($logs->isEmpty())
                <div class="text-center py-12 text-slate-400">
                    <span class="text-4xl block mb-2">⏱️</span>
                    <p class="text-sm">No attendance records found.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800/80 text-slate-400 uppercase tracking-widest font-bold">
                                <th class="pb-3 pr-4">Date</th>
                                <th class="pb-3 px-4">Clock In</th>
                                <th class="pb-3 px-4">Clock Out</th>
                                <th class="pb-3 px-4">Hours</th>
                                <th class="pb-3 pl-4 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                            @foreach ($logs as $log)
                                <tr class="hover:bg-slate-100/50 dark:hover:bg-slate-800/20 transition-colors">
                                    <td class="py-3.5 pr-4 font-bold text-slate-700 dark:text-slate-300">
                                        {{ \Carbon\Carbon::parse($log->log_date)->format('M d, Y') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">
                                        {{ \Carbon\Carbon::parse($log->clock_in)->format('H:i:s') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">
                                        {{ $log->clock_out ? \Carbon\Carbon::parse($log->clock_out)->format('H:i:s') : '--:--:--' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-semibold">
                                        {{ number_format($log->working_minutes / 60, 2) }} hrs
                                    </td>
                                    <td class="py-3.5 pl-4 text-right">
                                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase
                                            @if ($log->status === 'Present') bg-success/10 text-success
                                            @elseif ($log->status === 'Late') bg-warning/10 text-warning
                                            @elseif ($log->status === 'Half-Day') bg-secondary-500/10 text-secondary-500
                                            @else bg-danger/10 text-danger
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

    <!-- Alpine.js Geolocation Helper Logic -->
    <script>
        function geoHandler() {
            return {
                lat: null,
                lng: null,
                isSecured: false,
                isError: false,
                statusText: 'Checking location...',
                initGeo() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                this.lat = position.coords.latitude;
                                this.lng = position.coords.longitude;
                                this.isSecured = true;
                                this.statusText = `Location Secured (${this.lat.toFixed(4)}, ${this.lng.toFixed(4)})`;
                            },
                            (error) => {
                                this.isError = true;
                                switch(error.code) {
                                    case error.PERMISSION_DENIED:
                                        this.statusText = "Location denied by browser.";
                                        break;
                                    default:
                                        this.statusText = "Location accuracy issue.";
                                }
                            },
                            { enableHighAccuracy: true, timeout: 10000 }
                        );
                    } else {
                        this.isError = true;
                        this.statusText = "Geolocation not supported.";
                    }
                }
            }
        }
    </script>
</x-layouts.app>
