@extends('layouts.public')
@section('title', 'Track a Report')

@section('content')
<div class="page-title">
    <h1>Track a Report</h1>
    <p>Enter the reference number you received when you submitted your report.</p>
</div>

<div class="auth-wrap" style="flex-direction:column;align-items:center">
    <div class="glass track-box" style="width:100%">
        <form method="GET" action="{{ route('track') }}" class="track-form">
            <input type="search" name="reference" value="{{ $ref }}" placeholder="e.g. BRGY-{{ date('Y') }}-00001" aria-label="Reference number" required>
            <button class="btn btn-light" type="submit">Track</button>
        </form>
    </div>

    @if ($ref !== '')
        @if ($complaint)
            <div class="track-result" style="width:100%">
                <h3>{{ $complaint->reference_no }} <x-status-badge :status="$complaint->status" /></h3>
                <dl class="kv" style="margin-bottom:22px">
                    <dt>Complainant</dt><dd><span style="letter-spacing: 0.5px; font-weight: 500;">{{ $complaint->masked_complainant }}</span> <span class="muted" style="font-size:12px;">(masked for privacy)</span></dd>
                    <dt>Category</dt><dd>{{ $complaint->category ? $complaint->category->category_name : '—' }}</dd>
                    <dt>Area</dt><dd>{{ $complaint->area ? $complaint->area->area_name : '—' }}</dd>
                    <dt>Date filed</dt><dd>{{ $complaint->created_at->format('F j, Y') }}</dd>
                </dl>
                <x-status-timeline :complaint="$complaint" />
                <p class="muted" style="margin-top:14px;font-size:13.5px">For privacy, details of the report are only visible after you <a href="{{ route('login') }}">log in</a>.</p>
            </div>
        @else
            <div class="track-result" style="width:100%"><p style="margin:0">No report found for <b>{{ $ref }}</b>. Please check the number and try again.</p></div>
        @endif
    @endif
</div>
@endsection
