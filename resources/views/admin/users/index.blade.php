@extends('layouts.app')
@section('title', 'Manage Users')
@section('heading', 'Manage Users')
@section('subtitle', 'View, add, edit, and manage accounts and role assignments.')
@section('actions')
    <div style="display: flex; gap: 8px;">
        <a class="btn btn-outline" href="{{ route('admin.users.pending') }}" style="position: relative;">
            <x-icon name="shield" /> Pending Approvals
            @if ($pendingCount > 0)
                <span style="background: var(--bad); color: #fff; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 99px; margin-left: 4px;">{{ $pendingCount }}</span>
            @endif
        </a>
        <a class="btn btn-primary" href="{{ route('admin.users.create') }}">
            <x-icon name="user" /> Add New User
        </a>
    </div>
@endsection

@section('content')
<div class="card">
    <form method="GET" class="filters">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, email, or contact" aria-label="Search">
        <select name="role_id" aria-label="Filter by role">
            <option value="">All roles</option>
            @foreach ($roles as $r)
                <option value="{{ $r->role_id }}" @selected(request('role_id') == $r->role_id)>{{ ucfirst($r->role_name) }}</option>
            @endforeach
        </select>
        <select name="verification_status" aria-label="Filter by verification">
            <option value="">All verifications</option>
            <option value="approved" @selected(request('verification_status') === 'approved')>Approved</option>
            <option value="pending" @selected(request('verification_status') === 'pending')>Pending Verification</option>
            <option value="rejected" @selected(request('verification_status') === 'rejected')>Rejected</option>
        </select>
        <select name="status" aria-label="Filter by status">
            <option value="">All account statuses</option>
            <option value="1" @selected(request('status') === '1')>Active</option>
            <option value="0" @selected(request('status') === '0')>Inactive</option>
        </select>
        <button class="btn btn-outline" type="submit">Filter</button>
        @if (request()->hasAny(['q', 'role_id', 'verification_status', 'status']))
            <a class="btn btn-outline" href="{{ route('admin.users.index') }}">Clear</a>
        @endif
    </form>

    @if ($users->isEmpty())
        <div class="empty">
            <p>No users found matching your search or filters.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Department / Area</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($users as $u)
                    <tr>
                        <td>
                            <b>{{ $u->full_name }}</b>
                            <div class="muted" style="font-size: 13px;">{{ $u->email }}</div>
                        </td>
                        <td>
                            @if ($u->isAdmin())
                                <span class="badge badge-assigned">Staff / Admin</span>
                            @else
                                <span class="badge badge-under-review">Resident</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size: 13.5px;">
                                @if ($u->department)
                                    <div><strong>Dept:</strong> {{ $u->department->department_name }}</div>
                                @endif
                                @if ($u->area)
                                    <div><strong>Area:</strong> {{ $u->area->area_name }}</div>
                                @endif
                                @if (!$u->department && !$u->area)
                                    <span class="muted">—</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $u->contact_number ?? '—' }}</td>
                        <td>
                            @if ($u->is_active)
                                <span class="badge badge-resolved">Active</span>
                            @else
                                <span class="badge badge-rejected">Inactive</span>
                            @endif

                            @if ($u->verification_status === 'pending')
                                <div style="margin-top: 4px;">
                                    <span class="badge badge-pending">Pending ID</span>
                                </div>
                            @elseif ($u->verification_status === 'rejected')
                                <div style="margin-top: 4px;">
                                    <span class="badge badge-rejected">Declined</span>
                                </div>
                            @endif
                        </td>
                        <td>{{ $u->created_at ? $u->created_at->format('M d, Y') : '—' }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                @if ($u->verification_doc_path)
                                    <a class="btn btn-outline btn-sm" href="{{ route('admin.users.document', $u) }}" target="_blank" title="View Verification Document">
                                        Doc
                                    </a>
                                @endif
                                <a class="btn btn-outline btn-sm" href="{{ route('admin.users.edit', $u) }}">Edit</a>
                                @if ($u->user_id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit" 
                                                data-confirm="Are you sure you want to remove or deactivate user '{{ $u->full_name }}'?" 
                                                data-confirm-title="Remove User" 
                                                data-confirm-ok="Remove" 
                                                data-confirm-type="danger">
                                            Remove
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links('pagination.custom') }}
    @endif
</div>
@endsection
