@extends('layouts.app')
@section('title', 'My Complaints')
@section('heading', 'My Complaints')
@section('subtitle', 'Every report you have filed, newest first.')
@section('actions')
    <a class="btn btn-primary" href="{{ route('complaints.create') }}">File a complaint</a>
@endsection

@section('content')
<div class="card">
    <form method="GET" class="filters">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search title or reference no." aria-label="Search">
        <select name="status" aria-label="Filter by status">
            <option value="">All statuses</option>
            @foreach ($statuses as $s)
                <option value="{{ $s->status_id }}" @selected(request('status') == $s->status_id)>{{ $s->status_name }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline" type="submit">Filter</button>
        @if (request()->hasAny(['q', 'status']))<a class="btn btn-outline" href="{{ route('complaints.index') }}">Clear</a>@endif
    </form>

    @if ($complaints->isEmpty())
        <div class="empty"><p>No reports found.</p></div>
    @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Reference</th><th>Title</th><th>Category</th><th>Status</th><th>Filed</th><th class="col-action"></th></tr></thead>
                <tbody>
                @foreach ($complaints as $c)
                    <tr>
                        <td>{{ $c->reference_no }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($c->title, 50) }}</td>
                        <td>{{ $c->category ? $c->category->category_name : '—' }}</td>
                        <td><x-status-badge :status="$c->status" /></td>
                        <td>{{ $c->created_at->format('M d, Y') }}</td>
                        <td class="col-action"><a class="btn btn-outline btn-sm" href="{{ route('complaints.show', $c) }}">View</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $complaints->links('pagination.custom') }}
    @endif
</div>
@endsection
