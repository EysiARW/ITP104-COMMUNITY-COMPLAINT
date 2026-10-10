@extends('layouts.app')
@section('title', 'All Complaints')
@section('heading', 'All Complaints')
@section('subtitle', 'Comprehensive list of community complaints, incident reports, and concerns.')

@section('content')
<div class="card">
    {{-- Quick-filter pills --}}
    <div class="tab-pills" style="margin-bottom:1.25rem; border-bottom:1px solid var(--line); padding-bottom:0.85rem;">
        <a href="{{ route('admin.complaints.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'all'])) }}"
           class="btn {{ ($tab ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline' }} btn-sm" style="border-radius:9999px;">
            All <span style="opacity:0.8; font-size:12px; margin-left:4px;">({{ $pillCounts['all'] ?? 0 }})</span>
        </a>
        <a href="{{ route('admin.complaints.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'needs_review'])) }}"
           class="btn {{ ($tab ?? '') === 'needs_review' ? 'btn-primary' : 'btn-outline' }} btn-sm" style="border-radius:9999px;">
            Needs Review <span style="opacity:0.8; font-size:12px; margin-left:4px;">({{ $pillCounts['needs_review'] ?? 0 }})</span>
        </a>
        <a href="{{ route('admin.complaints.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'unassigned'])) }}"
           class="btn {{ ($tab ?? '') === 'unassigned' ? 'btn-primary' : 'btn-outline' }} btn-sm" style="border-radius:9999px;">
            Unassigned <span style="opacity:0.8; font-size:12px; margin-left:4px;">({{ $pillCounts['unassigned'] ?? 0 }})</span>
        </a>
        <a href="{{ route('admin.complaints.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'in_progress'])) }}"
           class="btn {{ ($tab ?? '') === 'in_progress' ? 'btn-primary' : 'btn-outline' }} btn-sm" style="border-radius:9999px;">
            In Progress <span style="opacity:0.8; font-size:12px; margin-left:4px;">({{ $pillCounts['in_progress'] ?? 0 }})</span>
        </a>
        <a href="{{ route('admin.complaints.index', array_merge(request()->except(['tab', 'page']), ['tab' => 'resolved'])) }}"
           class="btn {{ ($tab ?? '') === 'resolved' ? 'btn-primary' : 'btn-outline' }} btn-sm" style="border-radius:9999px;">
            Resolved <span style="opacity:0.8; font-size:12px; margin-left:4px;">({{ $pillCounts['resolved'] ?? 0 }})</span>
        </a>
    </div>

    <form method="GET" class="filters">
        <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search title, reference no., or complainant" aria-label="Search">
        
        <select name="status_id" aria-label="Filter by status">
            <option value="">All statuses</option>
            @foreach ($statuses as $s)
                <option value="{{ $s->status_id }}" @selected(request('status_id') == $s->status_id)>{{ $s->status_name }}</option>
            @endforeach
        </select>

        <select name="category_id" aria-label="Filter by category">
            <option value="">All categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->category_id }}" @selected(request('category_id') == $cat->category_id)>{{ $cat->category_name }}</option>
            @endforeach
        </select>

        <select name="priority_id" aria-label="Filter by priority">
            <option value="">All priorities</option>
            @foreach ($priorities as $p)
                <option value="{{ $p->priority_id }}" @selected(request('priority_id') == $p->priority_id)>{{ $p->priority_name }}</option>
            @endforeach
        </select>

        <select name="area_id" aria-label="Filter by area">
            <option value="">All areas / puroks</option>
            @foreach ($areas as $a)
                <option value="{{ $a->area_id }}" @selected(request('area_id') == $a->area_id)>{{ $a->area_name }}</option>
            @endforeach
        </select>

        <select name="assigned" aria-label="Filter by assignment">
            <option value="">All assignments</option>
            <option value="unassigned" @selected(request('assigned') === 'unassigned')>Unassigned</option>
            <option value="me" @selected(request('assigned') === 'me')>Assigned to Me</option>
        </select>

        <button class="btn btn-outline" type="submit">Filter</button>
        @if (request()->hasAny(['q', 'status_id', 'category_id', 'priority_id', 'area_id', 'assigned']))
            <a class="btn btn-outline" href="{{ route('admin.complaints.index', ['tab' => $tab ?? 'all']) }}">Clear</a>
        @endif
    </form>

    @if ($complaints->isEmpty())
        <div class="empty">
            <p>No complaints found matching your filters.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Title &amp; Complainant</th>
                        <th>Category</th>
                        <th>Purok / Area</th>
                        <th>Priority</th>
                        <th>Assigned Officer</th>
                        <th>Status</th>
                        <th>Filed Date</th>
                        <th class="col-action" style="text-align: right; white-space: nowrap; min-width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($complaints as $c)
                    <tr>
                        <td>
                            <strong>{{ $c->reference_no }}</strong>
                        </td>
                        <td>
                            <b>{{ \Illuminate\Support\Str::limit($c->title, 45) }}</b>
                            <div class="muted" style="font-size: 12.5px;">
                                By {{ $c->complainant ? $c->complainant->full_name : 'Guest' }}
                            </div>
                        </td>
                        <td>{{ $c->category ? $c->category->category_name : '—' }}</td>
                        <td>{{ $c->area ? $c->area->area_name : '—' }}</td>
                        <td>
                            @php
                                $pBadge = match(strtolower($c->priority->priority_name ?? '')) {
                                    'urgent' => 'badge-rejected',
                                    'high' => 'badge-in-progress',
                                    'medium' => 'badge-assigned',
                                    default => 'badge-closed'
                                };
                            @endphp
                            <span class="badge {{ $pBadge }}">{{ $c->priority->priority_name ?? '—' }}</span>
                        </td>
                        <td>
                            @if ($c->currentAssignment && $c->currentAssignment->assignee)
                                <span>{{ $c->currentAssignment->assignee->full_name }}</span>
                            @else
                                <span class="badge badge-pending">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            <x-status-badge :status="$c->status" />
                        </td>
                        <td style="font-size: 13px;">
                            {{ $c->created_at ? $c->created_at->format('M d, Y') : '—' }}
                            @if ($c->status_id == 1 && $c->created_at && $c->created_at->diffInHours(now()) >= 48)
                                <div style="margin-top: 4px;">
                                    <span class="badge badge-rejected" style="font-size: 11px; padding: 2px 7px; display: inline-flex; align-items: center; gap: 3px;" title="Pending review for more than 48 hours without action">
                                        ⚠️ Overdue (&gt;48h)
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="col-action" style="text-align: right; white-space: nowrap;">
                            <a class="btn btn-outline btn-sm" href="{{ route('admin.complaints.show', $c) }}" style="white-space: nowrap;">
                                Review &amp; Assign
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $complaints->links('pagination.custom') }}
    @endif
</div>
@endsection
