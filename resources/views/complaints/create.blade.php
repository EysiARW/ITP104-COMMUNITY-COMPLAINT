@extends('layouts.app')
@section('title', 'File a Complaint')
@section('heading', 'File a Complaint')
@section('subtitle', 'Tell us what happened. The more detail you give, the faster we can act.')

@section('content')
<form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data" class="card">
    @csrf
    <div class="grid-2">
        <div class="field">
            <label for="report_type_id">Type of report</label>
            <select id="report_type_id" name="report_type_id" required>
                <option value="">Select type</option>
                @foreach ($types as $t)
                    <option value="{{ $t->report_type_id }}" @selected(old('report_type_id') == $t->report_type_id)>{{ $t->type_name }}</option>
                @endforeach
            </select>
            @error('report_type_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select category</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->category_id }}" @selected(old('category_id') == $c->category_id)>{{ $c->category_name }}</option>
                @endforeach
            </select>
            @error('category_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field span-2">
            <label for="title">Short title</label>
            <input id="title" type="text" name="title" value="{{ old('title') }}" maxlength="150" placeholder="e.g. Loud karaoke past midnight" required>
            @error('title')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field span-2">
            <label for="description">What happened?</label>
            <textarea id="description" name="description" maxlength="5000" required placeholder="Describe the incident or concern: who, what, when, and how it affects you.">{{ old('description') }}</textarea>
            @error('description')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="area_id">Purok / Area where it happened</label>
            <select id="area_id" name="area_id" required>
                <option value="">Select area</option>
                @foreach ($areas as $a)
                    <option value="{{ $a->area_id }}" @selected(old('area_id', auth()->user()->area_id) == $a->area_id)>{{ $a->area_name }}</option>
                @endforeach
            </select>
            @error('area_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <label for="incident_date">Date and time it happened <small class="muted">(optional)</small></label>
            <input id="incident_date" type="datetime-local" name="incident_date" value="{{ old('incident_date') }}" max="{{ now()->format('Y-m-d\TH:i') }}">
            @error('incident_date')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="field span-2">
            <label for="location_details">Exact location or landmark <small class="muted">(optional)</small></label>
            <input id="location_details" type="text" name="location_details" value="{{ old('location_details') }}" maxlength="255" placeholder="e.g. In front of the basketball court, Rizal St.">
            @error('location_details')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="span-2">
            <x-file-upload name="evidence" label="Evidence (photos or documents)" />
        </div>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;margin-top:6px">
        <a class="btn btn-outline" href="{{ route('dashboard') }}">Cancel</a>
        <button type="submit" class="btn btn-primary"
                data-confirm="Please check your details first. You will not be able to edit the report after submitting."
                data-confirm-title="Submit this report?" data-confirm-ok="Yes, submit">Submit report</button>
    </div>
</form>
@endsection
