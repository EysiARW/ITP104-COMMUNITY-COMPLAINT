@extends('layouts.app')
@section('title', 'Reports & Analytics')
@section('heading', 'Reports & Analytics')
@section('subtitle', 'Aggregate metrics, trends, and filtered complaint incident reports.')
@section('actions')
    <div style="display:flex; gap:8px;">
        <a class="btn btn-outline" href="{{ route('admin.reports.print', request()->query()) }}" target="_blank">
            <x-icon name="file-plus" /> Print / Export PDF
        </a>
        <a class="btn btn-primary" href="{{ route('admin.reports.export', request()->query()) }}">
            <x-icon name="upload" /> Export CSV
        </a>
    </div>
@endsection

@section('content')
<!-- SUMMARY STATS -->
<div class="stats">
    <div class="stat">
        <b>{{ $totalCount }}</b>
        <span>Filtered Records</span>
    </div>
    <div class="stat">
        <b>{{ $statusBreakdown[1] ?? 0 }}</b>
        <span>Pending Review</span>
    </div>
    <div class="stat">
        <b>{{ ($statusBreakdown[3] ?? 0) + ($statusBreakdown[4] ?? 0) }}</b>
        <span>In Progress / Assigned</span>
    </div>
    <div class="stat">
        <b>{{ $statusBreakdown[5] ?? 0 }}</b>
        <span>Resolved</span>
    </div>
</div>

<!-- CHARTS SECTION -->
<div class="grid-2" style="margin-bottom:24px;">
    <div class="card">
        <h3>Complaints by Status</h3>
        <div style="position:relative; height:240px;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
    <div class="card">
        <h3>Complaints by Category</h3>
        <div style="position:relative; height:240px;">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<div class="grid-2" style="margin-bottom:24px;">
    <div class="card">
        <h3>Complaints by Purok / Area</h3>
        <div style="position:relative; height:240px;">
            <canvas id="areaChart"></canvas>
        </div>
    </div>
    <div class="card">
        <h3>6-Month Filing Trend</h3>
        <div style="position:relative; height:240px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>

<!-- FILTER CARD & DATA TABLE -->
<div class="card">
    <h3>Filter Complaint Records</h3>
    <form method="GET" class="filters" style="margin-bottom: 20px;">
        <div style="display:flex; gap:10px; flex-wrap:wrap; width:100%;">
            <select name="status_id" aria-label="Status">
                <option value="">All Statuses</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->status_id }}" @selected(request('status_id') == $s->status_id)>{{ $s->status_name }}</option>
                @endforeach
            </select>

            <select name="category_id" aria-label="Category">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->category_id }}" @selected(request('category_id') == $cat->category_id)>{{ $cat->category_name }}</option>
                @endforeach
            </select>

            <select name="priority_id" aria-label="Priority">
                <option value="">All Priorities</option>
                @foreach ($priorities as $p)
                    <option value="{{ $p->priority_id }}" @selected(request('priority_id') == $p->priority_id)>{{ $p->priority_name }}</option>
                @endforeach
            </select>

            <select name="area_id" aria-label="Area">
                <option value="">All Areas / Puroks</option>
                @foreach ($areas as $a)
                    <option value="{{ $a->area_id }}" @selected(request('area_id') == $a->area_id)>{{ $a->area_name }}</option>
                @endforeach
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Date From" aria-label="From">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Date To" aria-label="To">

            <button class="btn btn-outline" type="submit">Apply Filters</button>
            @if (request()->hasAny(['status_id', 'category_id', 'priority_id', 'area_id', 'date_from', 'date_to']))
                <a class="btn btn-outline" href="{{ route('admin.reports.index') }}">Reset</a>
            @endif
        </div>
    </form>

    @if ($complaints->isEmpty())
        <div class="empty">
            <p>No complaint records matched the given criteria.</p>
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
                        <th>Purok / Area</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Filed Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($complaints as $c)
                    <tr>
                        <td><strong>{{ $c->reference_no }}</strong></td>
                        <td>{{ \Illuminate\Support\Str::limit($c->title, 40) }}</td>
                        <td>{{ $c->complainant ? $c->complainant->full_name : 'N/A' }}</td>
                        <td>{{ $c->category ? $c->category->category_name : '—' }}</td>
                        <td>{{ $c->area ? $c->area->area_name : '—' }}</td>
                        <td>
                            @php
                                $pColor = match(strtolower($c->priority->priority_name ?? '')) {
                                    'urgent' => 'badge-rejected',
                                    'high' => 'badge-in-progress',
                                    'medium' => 'badge-assigned',
                                    default => 'badge-closed'
                                };
                            @endphp
                            <span class="badge {{ $pColor }}">{{ $c->priority->priority_name ?? '—' }}</span>
                        </td>
                        <td><x-status-badge :status="$c->status" /></td>
                        <td>{{ $c->created_at ? $c->created_at->format('M d, Y') : '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $complaints->links('pagination.custom') }}
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status breakdown chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($statuses->pluck('status_name')) !!},
                datasets: [{
                    data: {!! json_encode($statuses->map(fn($s) => $statusBreakdown[$s->status_id] ?? 0)) !!},
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

    // Category breakdown chart
    const catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        new Chart(catCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($categories->pluck('category_name')) !!},
                datasets: [{
                    label: 'Complaints',
                    data: {!! json_encode($categories->map(fn($c) => $categoryBreakdown[$c->category_id] ?? 0)) !!},
                    backgroundColor: '#3552e0',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { ticks: { autoSkip: false, maxRotation: 45, minRotation: 45, font: { size: 11 } } }
                }
            }
        });
    }

    // Area / Purok breakdown chart
    const areaCtx = document.getElementById('areaChart');
    if (areaCtx) {
        new Chart(areaCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($areas->pluck('area_name')) !!},
                datasets: [{
                    label: 'Complaints',
                    data: {!! json_encode($areas->map(fn($a) => $areaBreakdown[$a->area_id] ?? 0)) !!},
                    backgroundColor: '#0d9488',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { ticks: { autoSkip: false, maxRotation: 45, minRotation: 45, font: { size: 11 } } }
                }
            }
        });
    }

    // 6-Month Trend chart
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($trendMonths) !!},
                datasets: [{
                    label: 'Reports Filed',
                    data: {!! json_encode($trendCounts) !!},
                    borderColor: '#141f5e',
                    backgroundColor: 'rgba(20, 31, 94, 0.08)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#3552e0'
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
});
</script>
@endpush
