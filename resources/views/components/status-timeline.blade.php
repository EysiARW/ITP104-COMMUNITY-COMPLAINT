{{-- STATUS TIMELINE (step tracker). Usage: <x-status-timeline :complaint="$complaint" /> --}}
@props(['complaint'])
@php
    $complaint->loadMissing('history', 'status');
    $cur = $complaint->status?->sort_order ?? 1;
    $reached = $complaint->history->groupBy('new_status_id')->map(fn ($g) => $g->first()->changed_at);
    $rejectionHistory = $complaint->history->where('new_status_id', 7)->last();
@endphp

@if ($cur == 7)
    <div class="alert alert-error" style="margin-bottom: 1rem;">
        <strong>Report Rejected / Declined:</strong>
        @if ($rejectionHistory && !empty($rejectionHistory->remarks))
            {{ $rejectionHistory->remarks }}
        @else
            This report was not accepted by the barangay. Please check with the barangay hall for details.
        @endif
    </div>

    <ol class="steps">
        <li class="step done">
            <span class="dot">✓</span>
            <b>Submitted</b>
            <small>{{ $complaint->created_at ? $complaint->created_at->format('M d, Y') : '—' }}</small>
        </li>
        <li class="step rejected" aria-current="step">
            <span class="dot">✕</span>
            <b>Rejected / Declined</b>
            <small>{{ $rejectionHistory && $rejectionHistory->changed_at ? $rejectionHistory->changed_at->format('M d, Y') : ($complaint->updated_at ? $complaint->updated_at->format('M d, Y') : '—') }}</small>
        </li>
    </ol>
@else
    @php
        $steps = \App\Models\ComplaintStatus::where('sort_order', '<=', 5)->orderBy('sort_order')->get();
    @endphp
    <ol class="steps">
        @foreach ($steps as $s)
            @php
                $state = ($s->sort_order < $cur || $cur >= 5) ? 'done' : ($s->sort_order == $cur ? 'current' : '');
            @endphp
            <li class="step {{ $state }}" @if($state === 'current') aria-current="step" @endif>
                <span class="dot">{{ $state === 'done' ? '✓' : $s->sort_order }}</span>
                <b>{{ $s->status_name }}</b>
                <small>{{ isset($reached[$s->status_id]) && $reached[$s->status_id] ? $reached[$s->status_id]->format('M d, Y') : '—' }}</small>
            </li>
        @endforeach
    </ol>
@endif

