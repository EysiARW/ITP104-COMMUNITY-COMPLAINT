@extends('layouts.app')
@section('title', $complaint->reference_no)
@section('heading', $complaint->reference_no)
@section('subtitle', 'Filed ' . $complaint->created_at->format('F j, Y \a\t g:i A'))
@section('actions')
    <a class="btn btn-outline" href="{{ route('complaints.index') }}">&larr; My complaints</a>
@endsection

@section('content')
<div class="card">
    <h3>Progress</h3>
    <x-status-timeline :complaint="$complaint" />
</div>

<div class="detail-grid">
    <div>
        <div class="card">
            <h3>{{ $complaint->title }}</h3>
            <p style="white-space:pre-line">{{ $complaint->description }}</p>
            <dl class="kv" style="margin-top:18px">
                <dt>Type</dt><dd>{{ $complaint->reportType ? $complaint->reportType->type_name : '—' }}</dd>
                <dt>Category</dt><dd>{{ $complaint->category ? $complaint->category->category_name : '—' }}</dd>
                <dt>Area</dt><dd>{{ $complaint->area ? $complaint->area->area_name : '—' }}</dd>
                @if ($complaint->location_details)<dt>Location</dt><dd>{{ $complaint->location_details }}</dd>@endif
                @if ($complaint->incident_date)<dt>Happened on</dt><dd>{{ $complaint->incident_date->format('F j, Y g:i A') }}</dd>@endif
            </dl>
        </div>

        @if ($complaint->attachments->isNotEmpty())
            <div class="card">
                <h3>Evidence you attached</h3>
                <div class="attach-grid">
                    @foreach ($complaint->attachments as $a)
                        <a class="attach" href="{{ route('attachments.show', $a->attachment_id) }}" target="_blank" rel="noopener">
                            @if ($a->isImage())<img src="{{ route('attachments.show', $a->attachment_id) }}" alt="" loading="lazy">
                            @else<div class="ph">PDF</div>@endif
                            <span>{{ $a->file_name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($complaint->resolution)
            <div class="card" style="border-color:#b7e4c4">
                <h3>Resolution</h3>
                <p style="white-space:pre-line">{{ $complaint->resolution->resolution_details }}</p>
                <p class="muted" style="font-size:13.5px;margin:0">Resolved on {{ $complaint->resolution->resolved_at ? $complaint->resolution->resolved_at->format('F j, Y') : '—' }}</p>
            </div>
        @endif
    </div>

    <div>
        <div class="card">
            <h3>Status</h3>
            <p><x-status-badge :status="$complaint->status" /></p>
            <dl class="kv" style="grid-template-columns:100px 1fr">
                <dt>Priority</dt><dd>{{ $complaint->priority ? $complaint->priority->priority_name : '—' }}</dd>
                <dt>Handled by</dt>
                <dd>
                    @if ($complaint->currentAssignment && $complaint->currentAssignment->assignee)
                        {{ $complaint->currentAssignment->assignee->full_name }}
                        @if ($complaint->currentAssignment->assignee->department)<br><small class="muted">{{ $complaint->currentAssignment->assignee->department->department_name }}</small>@endif
                    @else
                        <span class="muted">Not yet assigned</span>
                    @endif
                </dd>
            </dl>
        </div>

        <div class="card">
            <h3>Activity</h3>
            <ul class="log">
                @foreach ($complaint->history->reverse() as $h)
                    <li>
                        <b>{{ $h->newStatus ? $h->newStatus->status_name : 'Updated' }}</b>
                        @if ($h->remarks)<br>{{ $h->remarks }}@endif
                        <small>{{ $h->changed_at ? $h->changed_at->format('M d, Y g:i A') : '—' }}</small>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
