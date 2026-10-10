@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Hello, ' . auth()->user()->first_name)
@section('subtitle', 'Here is a summary of your reports.')
@section('actions')
    <a class="btn btn-primary" href="{{ route('complaints.create') }}"><x-icon name="file-plus" style="width:18px;height:18px" /> File a complaint</a>
@endsection

@section('content')
<div class="stats">
    <div class="stat"><b>{{ $stats['total'] }}</b><span>Total reports</span></div>
    <div class="stat"><b>{{ $stats['pending'] }}</b><span>Awaiting review</span></div>
    <div class="stat"><b>{{ $stats['active'] }}</b><span>Being handled</span></div>
    <div class="stat"><b>{{ $stats['resolved'] }}</b><span>Resolved</span></div>
</div>

<div class="card">
    <div class="row-between" style="margin-bottom:6px"><h3 style="margin:0">Recent reports</h3><a href="{{ route('complaints.index') }}">View all</a></div>
    @if ($recent->isEmpty())
        <div class="empty">
            <p>You haven't filed any reports yet.</p>
            <a class="btn btn-primary" href="{{ route('complaints.create') }}">File your first report</a>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Reference</th><th>Title</th><th>Category</th><th>Status</th><th>Filed</th></tr></thead>
                <tbody>
                @foreach ($recent as $c)
                    <tr>
                        <td><a href="{{ route('complaints.show', $c) }}">{{ $c->reference_no }}</a></td>
                        <td>{{ \Illuminate\Support\Str::limit($c->title, 45) }}</td>
                        <td>{{ $c->category ? $c->category->category_name : '—' }}</td>
                        <td><x-status-badge :status="$c->status" /></td>
                        <td>{{ $c->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="alert alert-info">For emergencies that need immediate help, call <b>911</b> or go to the barangay hall. This system is for non-urgent reports.</div>
@endsection
