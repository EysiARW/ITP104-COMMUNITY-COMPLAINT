@php
    $user = auth()->user();
    $unread = $user->notifications()->where('is_read', false)->count();
    $notifs = $user->notifications()->with('complaint')->latest('created_at')->limit(8)->get();
@endphp
<header class="topbar">
    <div class="left">
        <button class="icon-btn menu-btn" type="button" data-sidebar-toggle aria-label="Open menu"><x-icon name="menu" /></button>
        <span class="muted topbar-date">
            <span class="date-full">{{ now()->format('l, F j, Y') }}</span>
            <span class="date-short">{{ now()->format('M j, Y') }}</span>
        </span>
    </div>

    <div class="top-right">
        {{-- Dark mode toggle --}}
        <button class="icon-btn theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode" title="Toggle dark mode">
            <span class="theme-icon-light"><x-icon name="moon" /></span>
            <span class="theme-icon-dark"><x-icon name="sun" /></span>
        </button>

        {{-- Notification bell --}}
        <div class="dropdown" data-dropdown>
            <button class="icon-btn" type="button" data-dropdown-btn aria-label="Notifications">
                <x-icon name="bell" />
                @if ($unread)<span class="dot-count">{{ $unread > 9 ? '9+' : $unread }}</span>@endif
            </button>
            <div class="dd-menu">
                <div class="dd-head">
                    <span>Notifications</span>
                    @if ($unread)
                        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button type="submit">Mark all read</button></form>
                    @endif
                </div>
                <div class="dd-list">
                    @forelse ($notifs as $n)
                        <a class="dd-item {{ $n->is_read ? '' : 'unread' }}" href="{{ route('notifications.open', $n->notification_id) }}">
                            {{ $n->message }}
                            <small>{{ $n->created_at ? $n->created_at->diffForHumans() : 'Just now' }}</small>
                        </a>
                    @empty
                        <div class="dd-empty">You're all caught up.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- User menu --}}
        <div class="dropdown" data-dropdown>
            <button class="user-btn" type="button" data-dropdown-btn>
                <span class="avatar">{{ $user->initials }}</span>
                <span class="uname">{{ $user->first_name }}</span>
            </button>
            <div class="dd-menu narrow">
                <a class="dd-item" href="{{ route('profile.edit') }}">Account settings</a>
                <button type="button" class="dd-item" data-theme-toggle style="display:flex; justify-content:space-between; align-items:center;">
                    <span>Appearance</span>
                    <span class="theme-label-mode" style="font-size:12px; color:var(--muted);">Light</span>
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dd-item" data-confirm="You will need to log in again to access your account."
                            data-confirm-title="Log out?" data-confirm-ok="Log out">Log out</button>
                </form>
            </div>
        </div>
    </div>
</header>
