@extends('ess.layout')

@section('page_title', 'Documents & Payslips')
@section('page_subtitle', 'Access your digital onboarding documents, official letters, and monthly salary slips.')

@section('styles')
<style>
    .docs-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    @media (max-width: 992px) {
        .docs-split {
            grid-template-columns: 1fr;
        }
    }

    /* Document Lists */
    .doc-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .doc-item {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 1.25rem;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        transition: all 0.2s;
    }

    .doc-item:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--primary);
    }

    .doc-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: rgba(99, 102, 241, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.25rem;
    }

    .doc-details {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        overflow: hidden;
    }

    .doc-title {
        font-weight: 600;
        font-size: 0.95rem;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .doc-meta {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.2rem;
    }

    .btn-download {
        background: transparent;
        border: 1px solid var(--glass-border);
        color: var(--text-main);
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        transition: all 0.2s;
    }

    .btn-download:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }
</style>
@endsection

@section('content')
<div class="docs-split">

    <!-- Left Column: Official Documents -->
    <div class="glass-card">
        <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Official Documents
        </h3>

        @if ($documents->isEmpty())
            <p style="color: var(--text-muted); text-align: center; padding: 3rem 0;">No official letters or documents uploaded yet.</p>
        @else
            <div class="doc-list">
                @foreach ($documents as $doc)
                    <div class="doc-item">
                        <div class="doc-icon">📄</div>
                        <div class="doc-details">
                            <span class="doc-title" title="{{ $doc->document_name }}">{{ $doc->document_name }}</span>
                            <span class="doc-meta">Uploaded {{ $doc->created_at->format('M d, Y') }}</span>
                        </div>
                        <a href="{{ Storage::url($doc->file_path) }}" class="btn-download" target="_blank" download>
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Get
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Right Column: Payslips -->
    <div class="glass-card">
        <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Monthly Payslips
        </h3>

        <div class="doc-list">
            @foreach ($payslips as $payslip)
                <div class="doc-item">
                    <div class="doc-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">💵</div>
                    <div class="doc-details">
                        <span class="doc-title">{{ $payslip['month_name'] }}</span>
                        <span class="doc-meta">Net Salary Paid: ${{ number_format($payslip['net_pay'], 2) }}</span>
                    </div>
                    <a href="{{ route('ess.payslips.download', $payslip['id']) }}" class="btn-download" target="_blank">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print
                    </a>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
