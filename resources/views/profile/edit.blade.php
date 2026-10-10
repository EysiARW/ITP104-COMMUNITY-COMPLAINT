@extends('layouts.app')
@section('title', 'Account Settings')
@section('heading', 'Account Settings')
@section('subtitle', 'Keep your contact details up to date so we can reach you about your reports.')

@section('content')
<form method="POST" action="{{ route('profile.update') }}" class="card">
    @csrf @method('PUT')
    <h3>Personal information</h3>
    <div class="grid-3">
        <div class="field">
            <label for="first_name">First name</label>
            <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
            @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="middle_name">Middle name</label>
            <input id="middle_name" type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}">
        </div>
        <div class="field">
            <label for="last_name">Last name</label>
            <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
            @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="grid-2">
        <div class="field">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            @error('email')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="contact_number">Mobile number</label>
            <input id="contact_number" type="tel" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" required>
            @error('contact_number')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="area_id">Purok / Area</label>
            <select id="area_id" name="area_id">
                <option value="">None</option>
                @foreach ($areas as $a)
                    <option value="{{ $a->area_id }}" @selected(old('area_id', $user->area_id) == $a->area_id)>{{ $a->area_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="address">Street / House no.</label>
            <input id="address" type="text" name="address" value="{{ old('address', $user->address) }}">
        </div>
    </div>
    <p class="muted" style="font-size:13.5px">Account type: <b>{{ ucfirst($user->role->role_name ?? 'Resident') }}</b> &middot; Member since {{ $user->created_at ? $user->created_at->format('F Y') : now()->format('F Y') }}</p>
    <div style="text-align:right"><button class="btn btn-primary" type="submit">Save changes</button></div>
</form>

<form method="POST" action="{{ route('profile.password') }}" class="card">
    @csrf @method('PUT')
    <h3>Change password</h3>
    <div class="grid-3">
        <div class="field">
            <label for="current_password">Current password</label>
            <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
            @if ($errors->password->has('current_password'))<span class="field-error">{{ $errors->password->first('current_password') }}</span>@endif
        </div>
        <div class="field">
            <label for="new_password">New password</label>
            <input id="new_password" type="password" name="password" required minlength="8" autocomplete="new-password">
            @if ($errors->password->has('password'))<span class="field-error">{{ $errors->password->first('password') }}</span>@endif
        </div>
        <div class="field">
            <label for="new_password_confirmation">Confirm new password</label>
            <input id="new_password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>
    </div>
    <div style="text-align:right">
        <button class="btn btn-primary" type="submit" data-confirm="You will stay logged in on this device." data-confirm-title="Change your password?" data-confirm-ok="Change password">Update password</button>
    </div>
</form>
@endsection
