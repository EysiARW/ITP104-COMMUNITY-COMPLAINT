@extends('layouts.app')
@section('title', 'Add New User')
@section('heading', 'Add New User')
@section('subtitle', 'Create a new staff or resident account.')
@section('actions')
    <a class="btn btn-outline" href="{{ route('admin.users.index') }}">Back to Users</a>
@endsection

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div class="grid-2">
            <div class="field">
                <label for="role_id">System Role <span class="req">*</span></label>
                <select name="role_id" id="role_id" required>
                    @foreach ($roles as $r)
                        <option value="{{ $r->role_id }}" @selected(old('role_id') == $r->role_id)>{{ ucfirst($r->role_name) }}</option>
                    @endforeach
                </select>
                @error('role_id')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="email">Email Address <span class="req">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="grid-3">
            <div class="field">
                <label for="first_name">First Name <span class="req">*</span></label>
                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required>
                @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="middle_name">Middle Name</label>
                <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name') }}">
                @error('middle_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="last_name">Last Name <span class="req">*</span></label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required>
                @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="grid-2">
            <div class="field">
                <label for="contact_number">Mobile Number</label>
                <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number') }}" placeholder="09171234567">
                @error('contact_number')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="password">Initial Password <span class="req">*</span></label>
                <input type="password" name="password" id="password" required minlength="8" placeholder="At least 8 characters">
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="grid-2">
            <div class="field">
                <label for="department_id">Department / Committee (Staff & Officers)</label>
                <select name="department_id" id="department_id">
                    <option value="">None / Not applicable</option>
                    @foreach ($departments as $d)
                        <option value="{{ $d->department_id }}" @selected(old('department_id') == $d->department_id)>{{ $d->department_name }}</option>
                    @endforeach
                </select>
                @error('department_id')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="committee_position">Committee Position / Role</label>
                <input type="text" name="committee_position" id="committee_position" value="{{ old('committee_position') }}" placeholder="e.g. Chairman, Member, Inspector / Tanod">
                @error('committee_position')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="field">
            <label for="area_id">Assigned Area / Purok</label>
            <select name="area_id" id="area_id">
                <option value="">None / Unassigned</option>
                @foreach ($areas as $a)
                    <option value="{{ $a->area_id }}" @selected(old('area_id') == $a->area_id)>{{ $a->area_name }}</option>
                @endforeach
            </select>
            @error('area_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label for="address">Residential Address</label>
            <input type="text" name="address" id="address" value="{{ old('address') }}" placeholder="Street, block, or landmark">
            @error('address')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label class="check">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span>Account is active (can log in)</span>
            </label>
        </div>

        <div class="form-actions">
            <a class="btn btn-outline" href="{{ route('admin.users.index') }}">Cancel</a>
            <button class="btn btn-primary" type="submit">Create Account</button>
        </div>
    </form>
</div>
@endsection
