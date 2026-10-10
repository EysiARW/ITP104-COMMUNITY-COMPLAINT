@extends('layouts.public')
@section('title', 'Forgot Password')

@section('content')
<div class="auth-wrap">
    <div class="glass auth-card">
        <h2>Forgot password?</h2>
        <p class="lead">Enter your email and we'll send you a link to reset it.</p>
        @include('partials.alerts')

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <button class="btn btn-light btn-block" type="submit">Send reset link</button>
        </form>
        <p class="center" style="margin:18px 0 0"><a href="{{ route('login') }}">&larr; Back to login</a></p>
    </div>
</div>
@endsection
