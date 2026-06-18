<x-layouts.app>
    <!-- Header summary -->
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Documents & Payslips</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Access your digital onboarding checklists, official letters, and monthly salary slips.
        </p>
    </div>

    <!-- Split Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Left Side: Official Documents -->
        <div class="glass-panel p-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-6 border-b border-slate-200 dark:border-slate-800/80 pb-4 flex items-center gap-2">
                <span>📁</span> Official Letters & Documents
            </h3>

            @if ($documents->isEmpty())
                <div class="text-center py-12 text-slate-400">
                    <span class="text-4xl block mb-2">📄</span>
                    <p class="text-sm">No official letters or documents uploaded yet.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($documents as $doc)
                        <div class="flex items-center justify-between p-4 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-2xl hover:border-primary-500/30 transition-all duration-300">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">📄</span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate max-w-[180px]" title="{{ $doc->document_name }}">
                                        {{ $doc->document_name }}
                                    </p>
                                    <p class="text-[9px] text-slate-400 mt-0.5">Uploaded {{ $doc->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($doc->file_path) }}" 
                               target="_blank" 
                               download
                               class="px-3 py-1.5 border border-slate-200 dark:border-slate-800 hover:border-primary-500 text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-slate-950 hover:bg-primary-500 font-bold rounded-lg text-[10px] flex items-center gap-1 transition-all duration-300">
                                📥 Download
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right Side: Payslips List -->
        <div class="glass-panel p-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-6 border-b border-slate-200 dark:border-slate-800/80 pb-4 flex items-center gap-2">
                <span>💵</span> Monthly Payslips
            </h3>

            @if (empty($payslips))
                <div class="text-center py-12 text-slate-400">
                    <span class="text-4xl block mb-2">💳</span>
                    <p class="text-sm">No salary slips generated yet.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($payslips as $payslip)
                        <div class="flex items-center justify-between p-4 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-2xl hover:border-primary-500/30 transition-all duration-300">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">💵</span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $payslip['month_name'] }}</p>
                                    <p class="text-[9px] text-slate-400 mt-0.5">Net Pay: ${{ number_format($payslip['net_pay'], 2) }}</p>
                                </div>
                            </div>
                            <a href="{{ route('ess.payslips.download', $payslip['id']) }}" 
                               target="_blank"
                               class="px-3 py-1.5 border border-slate-200 dark:border-slate-800 hover:border-primary-500 text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-slate-950 hover:bg-primary-500 font-bold rounded-lg text-[10px] flex items-center gap-1 transition-all duration-300">
                                🖨️ Print Slip
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
