@extends('layouts.app')
@section('title', 'Edit User: ' . $user->full_name)
@section('heading', 'Edit User Account')
@section('subtitle', 'Modify account details, roles, assignments, or update password.')
@section('actions')
    <a class="btn btn-outline" href="{{ route('admin.users.index') }}">Back to Users</a>
@endsection

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="grid-2">
            <div class="field">
                <label for="role_id">System Role <span class="req">*</span></label>
                <select name="role_id" id="role_id" required>
                    @foreach ($roles as $r)
                        <option value="{{ $r->role_id }}" @selected(old('role_id', $user->role_id) == $r->role_id)>{{ ucfirst($r->role_name) }}</option>
                    @endforeach
                </select>
                @error('role_id')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="email">Email Address <span class="req">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="grid-3">
            <div class="field">
                <label for="first_name">First Name <span class="req">*</span></label>
                <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="middle_name">Middle Name</label>
                <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name', $user->middle_name) }}">
                @error('middle_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="last_name">Last Name <span class="req">*</span></label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="grid-2">
            <div class="field">
                <label for="contact_number">Mobile Number</label>
                <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number', $user->contact_number) }}" placeholder="09171234567">
                @error('contact_number')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="password">Change Password (leave blank to keep current)</label>
                <input type="password" name="password" id="password" minlength="8" placeholder="Enter new password if updating">
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="grid-2">
            <div class="field">
                <label for="department_id">Department / Committee (Staff & Officers)</label>
                <select name="department_id" id="department_id">
                    <option value="">None / Not applicable</option>
                    @foreach ($departments as $d)
                        <option value="{{ $d->department_id }}" @selected(old('department_id', $user->department_id) == $d->department_id)>{{ $d->department_name }}</option>
                    @endforeach
                </select>
                @error('department_id')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="committee_position">Committee Position / Role</label>
                <input type="text" name="committee_position" id="committee_position" value="{{ old('committee_position', $user->committee_position) }}" placeholder="e.g. Chairman, Member, Inspector / Tanod">
                @error('committee_position')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="field">
            <label for="area_id">Assigned Area / Purok</label>
            <select name="area_id" id="area_id">
                <option value="">None / Unassigned</option>
                @foreach ($areas as $a)
                    <option value="{{ $a->area_id }}" @selected(old('area_id', $user->area_id) == $a->area_id)>{{ $a->area_name }}</option>
                @endforeach
            </select>
            @error('area_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label for="address">Residential Address</label>
            <input type="text" name="address" id="address" value="{{ old('address', $user->address) }}" placeholder="Street, block, or landmark">
            @error('address')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label class="check">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                <span>Account is active (can log in)</span>
            </label>
            @if ($user->user_id === auth()->id())
                <div class="hint">You cannot deactivate your own logged-in account.</div>
            @endif
        </div>

        <div class="form-actions">
            <a class="btn btn-outline" href="{{ route('admin.users.index') }}">Cancel</a>
            <button class="btn btn-primary" type="submit">Save Changes</button>
        </div>
    </form>
</div>
@endsection
