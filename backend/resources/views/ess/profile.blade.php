<x-layouts.app>
    <!-- Header Summary -->
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Profile & Settings</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Manage your personal contact information, direct deposit accounts, and emergency contact details.
        </p>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Left Side: Profile Widget card -->
        <div class="glass-panel p-6 flex flex-col items-center text-center">
            
            <!-- Large Avatar -->
            <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-primary-500 to-secondary-500 flex items-center justify-center font-bold text-slate-950 text-3xl border-4 border-white/10 shadow-lg mb-4">
                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
            </div>

            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $employee->first_name }} {{ $employee->last_name }}</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-widest mt-1">{{ $employee->employee_id }}</p>

            <div class="w-full text-left mt-8 border-t border-slate-200 dark:border-slate-800/80 pt-6 space-y-4 text-xs font-semibold">
                <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800/50">
                    <span class="text-slate-400">Department</span>
                    <span class="text-slate-700 dark:text-slate-300">{{ $employee->department ? $employee->department->name : 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800/50">
                    <span class="text-slate-400">Designation</span>
                    <span class="text-slate-700 dark:text-slate-300">{{ $employee->designation ? $employee->designation->title : 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800/50">
                    <span class="text-slate-400">Email</span>
                    <span class="text-slate-700 dark:text-slate-300">{{ $employee->email }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">Joining Date</span>
                    <span class="text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($employee->joining_date)->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Right Side: Edit Profile settings Form -->
        <div class="lg:col-span-2 glass-panel p-6">
            <form action="{{ route('ess.profile.update') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Section 1: Contact info -->
                <div>
                    <h4 class="text-xs font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase border-b border-slate-200 dark:border-slate-800/80 pb-2 mb-4">Contact Settings</h4>
                    <div class="space-y-1">
                        <label for="phone" class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="glass-input text-xs" value="{{ old('phone', $employee->phone) }}" placeholder="+1 555-0199">
                    </div>
                </div>

                <!-- Section 2: Bank Details -->
                <div>
                    <h4 class="text-xs font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase border-b border-slate-200 dark:border-slate-800/80 pb-2 mb-4">Bank Deposit Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2 space-y-1">
                            <label for="bank_name" class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Bank Name</label>
                            <input type="text" name="bank_name" id="bank_name" class="glass-input text-xs" value="{{ old('bank_name', $employee->bank_details['bank_name'] ?? '') }}" placeholder="Federal Deposit Bank" required>
                        </div>
                        <div class="space-y-1">
                            <label for="account_number" class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Account Number</label>
                            <input type="text" name="account_number" id="account_number" class="glass-input text-xs" value="{{ old('account_number', $employee->bank_details['account_number'] ?? '') }}" placeholder="0123456789" required>
                        </div>
                        <div class="space-y-1">
                            <label for="ifsc_code" class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">IFSC Code / Routing No</label>
                            <input type="text" name="ifsc_code" id="ifsc_code" class="glass-input text-xs" value="{{ old('ifsc_code', $employee->bank_details['ifsc_code'] ?? '') }}" placeholder="FED0012345" required>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Emergency Contacts -->
                <div>
                    <h4 class="text-xs font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase border-b border-slate-200 dark:border-slate-800/80 pb-2 mb-4">Emergency Contact</h4>
                    @php
                        $emergency = $employee->emergency_contacts[0] ?? null;
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2 space-y-1">
                            <label for="emergency_name" class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Contact Name</label>
                            <input type="text" name="emergency_name" id="emergency_name" class="glass-input text-xs" value="{{ old('emergency_name', $emergency['name'] ?? '') }}" placeholder="Jane Doe" required>
                        </div>
                        <div class="space-y-1">
                            <label for="emergency_relationship" class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Relationship</label>
                            <input type="text" name="emergency_relationship" id="emergency_relationship" class="glass-input text-xs" value="{{ old('emergency_relationship', $emergency['relationship'] ?? '') }}" placeholder="Spouse" required>
                        </div>
                        <div class="space-y-1">
                            <label for="emergency_phone" class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase">Emergency Phone</label>
                            <input type="text" name="emergency_phone" id="emergency_phone" class="glass-input text-xs" value="{{ old('emergency_phone', $emergency['phone'] ?? '') }}" placeholder="+1 555-0100" required>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-800/80">
                    <button type="submit" class="px-5 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs transition-colors btn-glow">
                        Save Profile Settings
                    </button>
                </div>

            </form>
        </div>

    </div>
</x-layouts.app>
