<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>@yield('title', 'Home') | Brgy. Banlic CCIRS</title>
</head>
@php
    $isAuthCleanPage = request()->routeIs('login', 'register', 'password.*');
@endphp
<body class="public {{ $isAuthCleanPage ? 'auth-clean-bg' : 'has-hall-bg' }}">
<div class="public-bg {{ $isAuthCleanPage ? 'no-bg-image' : 'has-bg-image' }}">
    <header class="pub-nav">
        <a class="brand" href="{{ route('home') }}" aria-label="Home"><img src="{{ asset('images/logo.png') }}" alt="Barangay Banlic seal"></a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Toggle navigation menu">
            <svg class="hamburger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="3.5" y1="6" x2="20.5" y2="6"></line>
                <line x1="3.5" y1="12" x2="20.5" y2="12"></line>
                <line x1="3.5" y1="18" x2="20.5" y2="18"></line>
            </svg>
        </button>
        <nav id="pubnav">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('report') }}">Report</a>
            <a href="{{ route('track') }}">Track</a>
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a href="{{ route('login') }}">Login</a>
            @endauth
            <button class="icon-btn theme-toggle" type="button" data-theme-toggle aria-label="Toggle dark mode" title="Toggle dark mode" style="color:#fff; width:36px; height:36px;">
                <span class="theme-icon-light"><x-icon name="moon" /></span>
                <span class="theme-icon-dark"><x-icon name="sun" /></span>
            </button>
        </nav>
    </header>

    <main class="pub-main">
        @yield('content')
    </main>

    @include('partials.footer')
</div>
<x-confirm-modal />
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
