@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('heading', 'Barangay Admin Dashboard')
@section('subtitle', 'Welcome back, ' . auth()->user()->first_name . '. Here is the community incident reporting workspace.')
@section('actions')
    <div style="display:flex; gap:8px;">
        <a class="btn btn-outline" href="{{ route('admin.complaints.index', ['assigned' => 'unassigned']) }}">
            <x-icon name="list" /> View Unassigned ({{ $unassignedCount }})
        </a>
        <a class="btn btn-primary" href="{{ route('admin.complaints.index') }}">
            All Complaints
        </a>
    </div>
@endsection

@section('content')
<!-- SUMMARY METRICS CARDS -->
<div class="stats">
    <div class="stat">
        <b>{{ $complaints }}</b>
        <span>Total Complaints Filed</span>
    </div>
    <div class="stat" style="border-left: 4px solid #f59e0b;">
        <b>{{ $pending }}</b>
        <span>
            Pending Review
            @if ($pending > 0)
                &bull; <a href="{{ route('admin.complaints.index', ['status_id' => 1]) }}">Review</a>
            @endif
        </span>
    </div>
    <div class="stat" style="border-left: 4px solid var(--blue);">
        <b>{{ $assigned }}</b>
        <span>In Progress / Assigned</span>
    </div>
    <div class="stat" style="border-left: 4px solid var(--ok);">
        <b>{{ $resolved }}</b>
        <span>Cases Resolved</span>
    </div>
    <div class="stat" style="border-left: 4px solid var(--bad);">
        <b>{{ $pendingRegistrations }}</b>
        <span>
            Pending Registrations
            @if ($pendingRegistrations > 0)
                &bull; <a href="{{ route('admin.users.pending') }}">Verify</a>
            @endif
        </span>
    </div>
</div>

<!-- CHARTS SECTION: MONTHLY TREND & STATUS DISTRIBUTION -->
<div class="grid-2" style="margin-bottom: 24px;">
    <div class="card">
        <h3>6-Month Report Volume Trend</h3>
        <div style="position:relative; height:240px;">
            <canvas id="dashTrendChart"></canvas>
        </div>
    </div>
    <div class="card">
        <h3>Complaints by Status</h3>
        <div style="position:relative; height:240px;">
            <canvas id="dashStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- RECENT COMPLAINTS TABLE -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
        <div>
            <h3 style="margin:0;">Recent Complaints Requiring Review</h3>
            <p class="muted" style="margin:2px 0 0; font-size:13.5px;">Latest reports submitted by Barangay Banlic residents.</p>
        </div>
        <a class="btn btn-outline btn-sm" href="{{ route('admin.complaints.index') }}">
            View All Complaints &rarr;
        </a>
    </div>

    @if ($recentComplaints->isEmpty())
        <div class="empty">
            <p>No complaints submitted yet.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Title</th>
                        <th>Complainant</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Assigned Officer</th>
                        <th>Status</th>
                        <th class="col-action" style="text-align:right; white-space:nowrap;">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($recentComplaints as $c)
                    <tr>
                        <td><strong>{{ $c->reference_no }}</strong></td>
                        <td>{{ \Illuminate\Support\Str::limit($c->title, 40) }}</td>
                        <td>{{ $c->complainant ? $c->complainant->full_name : 'Guest' }}</td>
                        <td>{{ $c->category ? $c->category->category_name : '—' }}</td>
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
                        <td><x-status-badge :status="$c->status" /></td>
                        <td class="col-action" style="text-align:right; white-space:nowrap;">
                            <a class="btn btn-outline btn-sm" href="{{ route('admin.complaints.show', $c) }}" style="white-space:nowrap;">
                                Review
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trend chart
    const trendCtx = document.getElementById('dashTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [{
                    label: 'Complaints Filed',
                    data: {!! json_encode($trendData) !!},
                    borderColor: '#3552e0',
                    backgroundColor: 'rgba(53, 82, 224, 0.1)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: '#141f5e'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    // Status breakdown chart
    const statusCtx = document.getElementById('dashStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($statuses->pluck('status_name')) !!},
                datasets: [{
                    data: {!! json_encode($statuses->map(fn($s) => $statusCounts[$s->status_id] ?? 0)) !!},
                    backgroundColor: ['#f59e0b', '#3b82f6', '#6366f1', '#f97316', '#10b981', '#6b7280', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { family: 'Roboto' } } }
                }
            }
        });
    }
});
</script>
@endpush
