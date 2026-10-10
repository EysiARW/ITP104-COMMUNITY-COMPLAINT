<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>@yield('title', 'Home') | Brgy. Banlic CCIRS</title>
</head>
<body class="public">
<div class="public-bg">
    <header class="pub-nav">
        <a class="brand" href="{{ route('home') }}" aria-label="Home"><img src="{{ asset('images/logo.png') }}" alt="Barangay Banlic seal"></a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Toggle menu"><x-icon name="menu" /></button>
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
