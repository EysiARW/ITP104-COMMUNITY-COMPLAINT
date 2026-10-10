@extends('layouts.public')
@section('title', 'Login')

@section('content')
<div class="auth-wrap">
    <div class="glass auth-card">
        <h2>Welcome back</h2>
        <p class="lead">Log in to file and track your reports.</p>
        @include('partials.alerts')

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="pw">
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                    <button type="button" data-toggle-password>Show</button>
                </div>
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="row-between">
                <label class="check"><input type="checkbox" name="remember" value="1"> Remember me</label>
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>
            <button class="btn btn-light btn-block" type="submit">Log in</button>
        </form>
        <p class="center" style="margin:18px 0 0">No account yet? <a href="{{ route('register') }}">Register here</a></p>
    </div>
</div>
@endsection
