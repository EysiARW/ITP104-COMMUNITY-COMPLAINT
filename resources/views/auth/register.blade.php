@extends('layouts.public')
@section('title', 'Register')

@section('content')
<div class="auth-wrap">
    <div class="glass auth-card wide">
        <h2>Create your account</h2>
        <p class="lead">Registered residents can file reports and track them online.</p>

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid-3">
                <div class="field">
                    <label for="first_name">First name</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name">
                    @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="middle_name">Middle name <small>(optional)</small></label>
                    <input id="middle_name" type="text" name="middle_name" value="{{ old('middle_name') }}" autocomplete="additional-name">
                    @error('middle_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="last_name">Last name</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">
                    @error('last_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="grid-2">
                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="contact_number">Mobile number</label>
                    <input id="contact_number" type="tel" name="contact_number" value="{{ old('contact_number') }}" placeholder="09171234567" required autocomplete="tel">
                    @error('contact_number')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="area_id">Purok / Area</label>
                    <select id="area_id" name="area_id" required>
                        <option value="">Select your area</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->area_id }}" @selected(old('area_id') == $area->area_id)>{{ $area->area_name }}</option>
                        @endforeach
                    </select>
                    @error('area_id')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="address">Street / House no.</label>
                    <input id="address" type="text" name="address" value="{{ old('address') }}" required autocomplete="street-address">
                    @error('address')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <div class="pw">
                        <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password">
                        <button type="button" data-toggle-password>Show</button>
                    </div>
                    <div class="hint">At least 8 characters.</div>
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                </div>
            </div>

            <!-- Verification Document Requirement -->
            <div style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 12px; padding: 18px 20px; margin: 10px 0 20px;">
                <h4 style="font-size: 16px; margin-bottom: 6px; color: #fff;">Proof of Residency &amp; Identity Verification</h4>
                <p style="font-size: 13px; opacity: 0.9; margin-bottom: 14px;">
                    To protect our community, account registration requires submitting at least one proof of residency document for Barangay verification.
                </p>

                <div class="grid-2">
                    <div class="field">
                        <label for="verification_doc_type">Document Type *</label>
                        <select id="verification_doc_type" name="verification_doc_type" required>
                            <option value="">Select document type</option>
                            <option value="voters_cert" @selected(old('verification_doc_type') === 'voters_cert')>Voter's Certification</option>
                            <option value="valid_id" @selected(old('verification_doc_type') === 'valid_id')>Valid Government ID (PhilID, Driver's License, etc.)</option>
                            <option value="certificate_of_residency" @selected(old('verification_doc_type') === 'certificate_of_residency')>Certificate of Residency</option>
                        </select>
                        @error('verification_doc_type')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="verification_doc">Upload Document or Take Photo *</label>
                        <input id="verification_doc" type="file" name="verification_doc" accept=".jpg,.jpeg,.png,.pdf,image/*" capture="environment" required style="padding: 8px;">
                        <div class="hint">Accepted: JPG, PNG, PDF (Max 5MB). Mobile devices can snap photo directly.</div>
                        @error('verification_doc')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="check">
                    <input type="checkbox" name="privacy" value="1" @checked(old('privacy')) required>
                    <span>I agree to the collection and use of my information as described in the <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>.</span>
                </label>
                @error('privacy')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <button class="btn btn-light btn-block" type="submit">Create account</button>
        </form>
        <p class="center" style="margin:18px 0 0">Already registered? <a href="{{ route('login') }}">Log in</a></p>
    </div>
</div>
@endsection
