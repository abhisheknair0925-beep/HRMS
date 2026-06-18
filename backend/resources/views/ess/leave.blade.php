<x-layouts.app>
    <!-- Header with Action Trigger -->
    <div class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Leave Management</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Check your remaining leave balances, apply for time-off, and track approvals.
            </p>
        </div>
        <button @click="$dispatch('open-modal', { name: 'apply-leave' })"
                class="px-4 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl btn-glow text-xs flex items-center gap-2">
            <span>➕</span> Apply For Leave
        </button>
    </div>

    <!-- Grid: Leave Balances -->
    <div class="mb-8">
        <h3 class="text-sm font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase mb-4">Leave Balances</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @if ($balances->isEmpty())
                <div class="glass-panel p-6 col-span-3 text-center text-slate-400 text-sm">
                    No leave allocations configured for your profile. Please contact HR.
                </div>
            @else
                @foreach ($balances as $balance)
                    @php
                        $remaining = $balance->allocated_days - $balance->used_days - $balance->encashed_days;
                    @endphp
                    <div class="glass-panel p-6 text-center flex flex-col justify-between hover:scale-[1.01] transition-transform">
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $balance->leavePolicy->name }}</p>
                            <h2 class="text-4xl font-extrabold bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent my-4">
                                {{ $remaining }}
                            </h2>
                            <p class="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Days Remaining</p>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-2 border-t border-slate-200 dark:border-slate-800/80 pt-4 mt-6 text-[10px] font-semibold">
                            <div>
                                <p class="text-slate-700 dark:text-slate-300 font-bold text-xs">{{ $balance->allocated_days }}</p>
                                <p class="text-slate-400 mt-0.5">Allocated</p>
                            </div>
                            <div>
                                <p class="text-slate-700 dark:text-slate-300 font-bold text-xs">{{ $balance->used_days }}</p>
                                <p class="text-slate-400 mt-0.5">Used</p>
                            </div>
                            <div>
                                <p class="text-slate-700 dark:text-slate-300 font-bold text-xs">{{ $balance->encashed_days }}</p>
                                <p class="text-slate-400 mt-0.5">Encashed</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Request History List -->
    <div class="glass-panel p-6">
        <h3 class="text-sm font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase mb-4">Leave Request History</h3>
        
        @if ($requests->isEmpty())
            <div class="text-center py-12 text-slate-400">
                <span class="text-4xl block mb-2">📬</span>
                <p class="text-sm">No leave applications found.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800/80 text-slate-400 uppercase tracking-widest font-bold">
                            <th class="pb-3 pr-4">Policy Type</th>
                            <th class="pb-3 px-4">Start Date</th>
                            <th class="pb-3 px-4">End Date</th>
                            <th class="pb-3 px-4">Days</th>
                            <th class="pb-3 px-4">Reason</th>
                            <th class="pb-3 pl-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                        @foreach ($requests as $req)
                            <tr class="hover:bg-slate-100/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="py-3.5 pr-4 font-bold text-slate-700 dark:text-slate-300">
                                    {{ $req->leavePolicy ? $req->leavePolicy->name : 'N/A' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">
                                    {{ \Carbon\Carbon::parse($req->start_date)->format('M d, Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">
                                    {{ \Carbon\Carbon::parse($req->end_date)->format('M d, Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-semibold">
                                    {{ $req->total_days }} {{ Str::plural('day', (float)$req->total_days) }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 max-w-[200px] truncate" title="{{ $req->reason }}">
                                    {{ $req->reason }}
                                </td>
                                <td class="py-3.5 pl-4 text-right">
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase
                                        @if ($req->status === 'Approved') bg-success/10 text-success
                                        @elseif ($req->status === 'Pending') bg-warning/10 text-warning
                                        @else bg-danger/10 text-danger
                                        @endif">
                                        {{ $req->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Apply Leave Modal -->
    <x-ui.modal name="apply-leave" title="Apply for Leave">
        <form action="{{ route('ess.leave.apply') }}" method="POST" class="space-y-4" x-data="{ startDate: '', endDate: '' }">
            @csrf
            
            <!-- Leave Type -->
            <div class="space-y-1">
                <label for="leave_policy_id" class="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Leave Type</label>
                <select name="leave_policy_id" id="leave_policy_id" class="glass-input text-xs" required>
                    <option value="" disabled selected>Select Leave Type</option>
                    @foreach ($policies as $policy)
                        <option value="{{ $policy->id }}">{{ $policy->name }} (Total {{ $policy->total_days }} Days)</option>
                    @endforeach
                </select>
            </div>

            <!-- Date range grid -->
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="start_date" class="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Start Date</label>
                    <input type="date" name="start_date" id="start_date" x-model="startDate" class="glass-input text-xs" required min="{{ now()->toDateString() }}">
                </div>
                <div class="space-y-1">
                    <label for="end_date" class="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">End Date</label>
                    <input type="date" name="end_date" id="end_date" x-model="endDate" :min="startDate" class="glass-input text-xs" required min="{{ now()->toDateString() }}">
                </div>
            </div>

            <!-- Half day checker -->
            <div class="flex items-center gap-2 py-1">
                <input type="checkbox" name="half_day" id="half_day" value="1" class="w-4 h-4 rounded border-slate-300 text-primary-500 focus:ring-primary-500 bg-slate-950/40 cursor-pointer">
                <label for="half_day" class="text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer select-none">Apply as Half Day</label>
            </div>

            <!-- Reason -->
            <div class="space-y-1">
                <label for="reason" class="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Reason for Leave</label>
                <textarea name="reason" id="reason" class="glass-input text-xs resize-none" rows="4" placeholder="Brief explanation of your leave request..." required></textarea>
            </div>

            <!-- Actions buttons -->
            <div class="flex gap-4 pt-4 border-t border-slate-200 dark:border-slate-800/80">
                <button type="button" @click="$dispatch('close-modal')" class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl text-xs transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs transition-colors btn-glow">
                    Submit Request
                </button>
            </div>
        </form>
    </x-ui.modal>
</x-layouts.app>
