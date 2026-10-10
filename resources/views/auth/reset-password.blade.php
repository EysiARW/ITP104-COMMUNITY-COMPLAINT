@extends('layouts.public')
@section('title', 'Reset Password')

@section('content')
<div class="auth-wrap">
    <div class="glass auth-card">
        <h2>Set a new password</h2>
        <p class="lead">Choose a strong password you haven't used before.</p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="field">
                <label for="email">Email address <span class="req">*</span></label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required>
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="password">New password <span class="req">*</span></label>
                <div class="pw">
                    <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password">
                    <button type="button" data-toggle-password>Show</button>
                </div>
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm new password <span class="req">*</span></label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>
            <button class="btn btn-light btn-block" type="submit">Reset password</button>
        </form>
    </div>
</div>
@endsection
