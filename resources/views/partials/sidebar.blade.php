@php
    $user = auth()->user();

    // [label, icon, route name, active patterns (|-separated)]
    $menu = $user->isAdmin() ? [
        ['Dashboard', 'grid', 'admin.dashboard', 'admin.dashboard'],
        ['All Complaints', 'list', 'admin.complaints.index', 'admin.complaints.*'],
        ['My Investigations', 'search', 'admin.investigations.index', 'admin.investigations.*'],
        ['Manage Users', 'users', 'admin.users.index', 'admin.users.*'],
        ['Departments & Areas', 'map-pin', 'admin.areas.index', 'admin.areas.*|admin.departments.*'],
        ['Complaint Categories', 'tag', 'admin.categories.index', 'admin.categories.*'],
        ['Reports', 'bar-chart', 'admin.reports.index', 'admin.reports.*'],
        ['Audit Logs', 'shield', 'admin.logs.index', 'admin.logs.*'],
        ['My Profile', 'user', 'profile.edit', 'profile.*'],
    ] : [
        ['Dashboard', 'grid', 'dashboard', 'dashboard'],
        ['File a Complaint', 'file-plus', 'complaints.create', 'complaints.create'],
        ['My Complaints', 'list', 'complaints.index', 'complaints.index|complaints.show'],
        ['Track a Report', 'map-pin', 'track', 'track'],
        ['My Profile', 'user', 'profile.edit', 'profile.*'],
    ];
@endphp
<aside class="sidebar" id="sidebar" aria-label="Main navigation">
    <div class="side-head">
        <a class="side-brand" href="{{ $user->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" title="Brgy. Banlic CCIRS">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
            <span class="brand-text"><b>Brgy. Banlic</b><small>Complaint &amp; Incident Reports</small></span>
        </a>
        <button class="side-fold-btn" type="button" data-sidebar-fold aria-label="Toggle fold sidebar" title="Fold sidebar (hover to expand)">
            <svg class="fold-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
    </div>

    <nav class="side-nav">
        @php
            $pendingUsersCount = $user->isAdmin() ? \App\Models\User::where('verification_status', \App\Models\User::STATUS_PENDING)->count() : 0;
        @endphp
        @foreach ($menu as [$label, $icon, $route, $active])
            {{-- Links appear automatically once a teammate defines the route. --}}
            @if (Route::has($route))
                <a href="{{ route($route) }}" class="{{ request()->routeIs(...explode('|', $active)) ? 'active' : '' }}" title="{{ $label }}">
                    <span class="nav-item-inner">
                        <x-icon :name="$icon" class="nav-icon" />
                        <span class="nav-label">{{ $label }}</span>
                    </span>
                    @if ($route === 'admin.users.index' && $pendingUsersCount > 0)
                        <span class="nav-badge">{{ $pendingUsersCount }}</span>
                    @endif
                </a>
            @endif
        @endforeach
    </nav>

    <div class="side-user">
        <div class="side-user-inner">
            <span class="side-user-avatar">{{ $user->initials }}</span>
            <div class="side-user-meta">
                <b>{{ $user->full_name }}</b>
                <span>{{ $user->isAdmin() ? 'Barangay Staff (Admin)' : 'Resident' }}</span>
            </div>
        </div>
    </div>
</aside>
