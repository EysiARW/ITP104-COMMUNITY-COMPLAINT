{{-- BASE LAYOUT: every logged-in page extends this.
     Sections: title, heading, subtitle (optional), actions (optional), content. Use @push('scripts') for page JS. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>@yield('title', 'Dashboard') | Brgy. Banlic CCIRS</title>
</head>
<body class="app-body has-hall-bg">
<div class="app">
    @include('partials.sidebar')
    <div class="scrim" id="scrim"></div>

    <div class="main">
        @include('partials.topbar')

        <main class="content">
            <div class="page-head">
                <div>
                    <h1>@yield('heading')</h1>
                    @hasSection('subtitle')<p class="muted">@yield('subtitle')</p>@endif
                </div>
                @yield('actions')
            </div>

            @include('partials.alerts')
            @yield('content')
        </main>
    </div>
</div>

<x-confirm-modal />
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
