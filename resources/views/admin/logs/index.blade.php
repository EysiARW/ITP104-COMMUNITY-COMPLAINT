@extends('layouts.app')
@section('title', 'System & Audit Logs')
@section('heading', 'Audit Logs')
@section('subtitle', 'Trace of administrative activities, data changes, and security events.')

@section('content')
<div class="card">
    <form method="GET" class="filters">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search description, table, or IP" aria-label="Search">
        
        <select name="action" aria-label="Filter by action">
            <option value="">All Actions</option>
            @foreach ($actions as $act)
                <option value="{{ $act }}" @selected(request('action') === $act)>{{ $act }}</option>
            @endforeach
        </select>

        <select name="user_id" aria-label="Filter by user">
            <option value="">All Users</option>
            @foreach ($users as $u)
                <option value="{{ $u->user_id }}" @selected(request('user_id') == $u->user_id)>{{ $u->full_name }} ({{ $u->email }})</option>
            @endforeach
        </select>

        <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From Date" aria-label="From Date">
        <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To Date" aria-label="To Date">

        <button class="btn btn-outline" type="submit">Filter</button>
        @if (request()->hasAny(['q', 'action', 'user_id', 'date_from', 'date_to']))
            <a class="btn btn-outline" href="{{ route('admin.logs.index') }}">Clear</a>
        @endif
    </form>

    @if ($logs->isEmpty())
        <div class="empty">
            <p>No audit log records found for the selected criteria.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Target Table & ID</th>
                        <th>Description / Details</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td style="white-space:nowrap; font-size:13.5px;">
                            {{ $log->created_at ? $log->created_at->format('M d, Y h:i:s A') : '—' }}
                        </td>
                        <td>
                            @if ($log->user)
                                <b>{{ $log->user->full_name }}</b>
                                <div class="muted" style="font-size:12px;">{{ $log->user->email }}</div>
                            @else
                                <span class="muted">System / Guest</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($log->action) {
                                    'CREATE' => 'badge-resolved',
                                    'UPDATE' => 'badge-under-review',
                                    'DELETE' => 'badge-rejected',
                                    'LOGIN'  => 'badge-assigned',
                                    default  => 'badge-closed'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $log->action }}</span>
                        </td>
                        <td>
                            @if ($log->table_name)
                                <code>{{ $log->table_name }}</code>
                                @if ($log->record_id)
                                    <span class="muted">#{{ $log->record_id }}</span>
                                @endif
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td>{{ $log->description ?? '—' }}</td>
                        <td style="font-size:13px; font-family:monospace;">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $logs->links('pagination.custom') }}
    @endif
</div>
@endsection
