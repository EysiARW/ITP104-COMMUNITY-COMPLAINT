@extends('layouts.app')
@section('title', 'Review Complaint: ' . $complaint->reference_no)
@section('heading', $complaint->reference_no)
@section('subtitle', 'Filed on ' . $complaint->created_at->format('F j, Y \a\t g:i A') . ' by ' . ($complaint->complainant ? $complaint->complainant->full_name : 'Unknown'))
@section('actions')
    <a class="btn btn-outline" href="{{ route('admin.complaints.index') }}">&larr; Back to Complaints</a>
@endsection

@section('content')
<!-- STEP-BY-STEP PROGRESS TIMELINE -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
        <h3 style="margin: 0;">Status Timeline &amp; Progress</h3>
        <div>
            <x-status-badge :status="$complaint->status" />
        </div>
    </div>
    <x-status-timeline :complaint="$complaint" />
</div>

<div class="detail-grid">
    <!-- LEFT COLUMN: INCIDENT DETAILS & EVIDENCE -->
    <div>
        <div class="card">
            <h3>{{ $complaint->title }}</h3>
            <p style="white-space: pre-line; line-height: 1.6;">{{ $complaint->description }}</p>

            <dl class="kv" style="margin-top: 20px;">
                <dt>Report Type</dt><dd>{{ $complaint->reportType->type_name }}</dd>
                <dt>Category</dt><dd>{{ $complaint->category ? $complaint->category->category_name : '—' }}</dd>
                <dt>Purok / Area</dt><dd>{{ $complaint->area ? $complaint->area->area_name : '—' }}</dd>
                @if ($complaint->location_details)
                    <dt>Specific Location</dt><dd>{{ $complaint->location_details }}</dd>
                @endif
                @if ($complaint->incident_date)
                    <dt>Incident Date</dt><dd>{{ $complaint->incident_date->format('F j, Y \a\t g:i A') }}</dd>
                @endif
            </dl>
        </div>

        <!-- COMPLAINANT INFORMATION -->
        <div class="card">
            <h3>Complainant Information</h3>
            @if ($complaint->complainant)
                <dl class="kv">
                    <dt>Full Name</dt><dd><b>{{ $complaint->complainant->full_name }}</b></dd>
                    <dt>Email Address</dt><dd>{{ $complaint->complainant->email }}</dd>
                    <dt>Mobile Number</dt><dd>{{ $complaint->complainant->contact_number ?? 'Not provided' }}</dd>
                    <dt>Residential Address</dt><dd>{{ $complaint->complainant->address ?? 'Not provided' }}</dd>
                    <dt>Account Status</dt>
                    <dd>
                        @if ($complaint->complainant->is_active)
                            <span class="badge badge-resolved">Active Resident</span>
                        @else
                            <span class="badge badge-rejected">Inactive / Pending</span>
                        @endif
                    </dd>
                </dl>
            @else
                <p class="muted">Complainant details unavailable.</p>
            @endif
        </div>

        <!-- ATTACHED EVIDENCE -->
        @if ($complaint->attachments->isNotEmpty())
            <div class="card">
                <h3>Uploaded Evidence ({{ $complaint->attachments->count() }})</h3>
                <div class="attach-grid">
                    @foreach ($complaint->attachments as $a)
                        <a class="attach" href="{{ route('attachments.show', $a->attachment_id) }}" target="_blank" rel="noopener">
                            @if ($a->isImage())
                                <img src="{{ route('attachments.show', $a->attachment_id) }}" alt="" loading="lazy">
                            @else
                                <div class="ph">FILE</div>
                            @endif
                            <span>{{ $a->file_name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- RESOLUTION (IF RESOLVED) -->
        @if ($complaint->resolution)
            <div class="card" style="border-left: 4px solid var(--ok);">
                <h3 style="color: var(--ok);">Case Resolution</h3>
                <p style="white-space: pre-line;">{{ $complaint->resolution->resolution_details }}</p>
                <p class="muted" style="font-size: 13px; margin: 0;">
                    Resolved on {{ $complaint->resolution->resolved_at->format('F j, Y g:i A') }}
                    @if ($complaint->resolution->resolver)
                        by {{ $complaint->resolution->resolver->full_name }}
                    @endif
                </p>
            </div>
        @endif
    </div>

    <!-- RIGHT COLUMN: ACTIONS (ASSIGN OFFICER & CHANGE STATUS) -->
    <div>
        <!-- ASSIGN TO OFFICER SECTION -->
        <div class="card">
            <h3>Assign to Barangay Officer</h3>
            @if ($complaint->currentAssignment)
                <div style="background: var(--bg); border: 1px solid var(--line); border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;">
                    <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 700;">Currently Assigned To</div>
                    <div style="font-size: 16px; font-weight: 700; color: var(--navy); margin-top: 2px;">
                        {{ $complaint->currentAssignment->assignee ? $complaint->currentAssignment->assignee->full_name : 'Assigned Staff' }}
                    </div>
                    @if ($complaint->currentAssignment->assignee && $complaint->currentAssignment->assignee->committee_position)
                        <div style="font-size: 12.5px; color: var(--blue); font-weight: 600;">
                            {{ $complaint->currentAssignment->assignee->committee_position }}
                        </div>
                    @endif
                    @if ($complaint->currentAssignment->assignee && $complaint->currentAssignment->assignee->department)
                        <div class="muted" style="font-size: 13px;">{{ $complaint->currentAssignment->assignee->department->department_name }}</div>
                    @endif
                    <div class="muted" style="font-size: 12px; margin-top: 4px;">
                        Assigned on {{ $complaint->currentAssignment->assigned_at ? $complaint->currentAssignment->assigned_at->format('M d, Y h:i A') : '—' }}
                    </div>
                    @if ($complaint->currentAssignment->remarks)
                        <div style="font-size: 13px; margin-top: 6px; font-style: italic;">
                            "{{ $complaint->currentAssignment->remarks }}"
                        </div>
                    @endif
                </div>
            @else
                <div class="alert alert-info" style="font-size: 13.5px;">
                    This complaint is currently <strong>unassigned</strong>. Select a barangay staff or committee officer below.
                </div>
            @endif

            @php
                $recDept = $complaint->category?->department;
                $recOfficers = $recDept ? $officers->where('department_id', $recDept->department_id) : collect();
                $otherOfficers = $recDept ? $officers->where('department_id', '!=', $recDept->department_id) : $officers;
            @endphp

            @if ($recDept)
                <div class="rec-dept-box">
                    <div class="rec-tag">
                        ★ Recommended Committee
                    </div>
                    <div class="rec-title">
                        {{ $recDept->department_name }}
                    </div>
                    <div class="rec-desc">
                        Reports under "{{ $complaint->category->category_name }}" are handled by this committee.
                    </div>
                </div>

                @if ($recOfficers->isNotEmpty())
                    <div style="margin-bottom: 12px;">
                        <div class="muted" style="font-size: 11.5px; margin-bottom: 6px; font-weight: 600;">Quick Pick Committee Member:</div>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            @foreach ($recOfficers as $ro)
                                <button type="button" class="btn btn-outline btn-sm" style="font-size: 12px; padding: 3px 10px; border-radius: 9999px; white-space: normal; text-align: left; max-width: 100%;"
                                        onclick="document.getElementById('assigned_to').value = '{{ $ro->user_id }}'">
                                    {{ $ro->full_name }} <span class="muted">({{ $ro->committee_position ?: 'Member' }})</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            <form method="POST" action="{{ route('admin.complaints.assign', $complaint) }}">
                @csrf
                <div class="field">
                    <label for="assigned_to">Assign Officer / Investigator <span class="req">*</span></label>
                    <select name="assigned_to" id="assigned_to" required>
                        <option value="">Select staff member</option>
                        
                        @if ($recDept && $recOfficers->isNotEmpty())
                            <optgroup label="★ {{ $recDept->department_name }} (Recommended)">
                                @foreach ($recOfficers as $off)
                                    <option value="{{ $off->user_id }}" @selected(old('assigned_to', $complaint->currentAssignment?->assigned_to) == $off->user_id)>
                                        {{ $off->full_name }} — {{ $off->committee_position ?: 'Member' }} ({{ $off->active_assignments_count }} active cases)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        @foreach ($departments as $d)
                            @if ($recDept && $d->department_id == $recDept->department_id)
                                @continue
                            @endif
                            @php
                                $deptStaff = $officers->where('department_id', $d->department_id);
                            @endphp
                            @if ($deptStaff->isNotEmpty())
                                <optgroup label="{{ $d->department_name }}">
                                    @foreach ($deptStaff as $off)
                                        <option value="{{ $off->user_id }}" @selected(old('assigned_to', $complaint->currentAssignment?->assigned_to) == $off->user_id)>
                                            {{ $off->full_name }} — {{ $off->committee_position ?: 'Member' }} ({{ $off->active_assignments_count }} active cases)
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach

                        @php
                            $unassignedStaff = $officers->whereNull('department_id');
                        @endphp
                        @if ($unassignedStaff->isNotEmpty())
                            <optgroup label="General Staff (Unassigned to Committee)">
                                @foreach ($unassignedStaff as $off)
                                    <option value="{{ $off->user_id }}" @selected(old('assigned_to', $complaint->currentAssignment?->assigned_to) == $off->user_id)>
                                        {{ $off->full_name }} ({{ $off->active_assignments_count }} active cases)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <div class="field">
                    <label for="assign_remarks">Assignment Instructions / Notes</label>
                    <textarea name="remarks" id="assign_remarks" rows="2" style="min-height: 70px;" placeholder="Instructions for officer..."></textarea>
                </div>

                <button class="btn btn-primary btn-block" type="submit">
                    {{ $complaint->currentAssignment ? 'Re-assign Officer' : 'Assign to Officer' }}
                </button>
            </form>
        </div>

        <!-- UPDATE STATUS & PRIORITY -->
        <div class="card">
            <h3>Update Status &amp; Priority</h3>
            <form method="POST" action="{{ route('admin.complaints.status', $complaint) }}">
                @csrf
                @method('PUT')

                <div class="field">
                    <label for="status_id">Complaint Status <span class="req">*</span></label>
                    <select name="status_id" id="status_id" required>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->status_id }}" @selected(old('status_id', $complaint->status_id) == $st->status_id)>
                                {{ $st->status_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="priority_id">Priority Level <span class="req">*</span></label>
                    <select name="priority_id" id="priority_id" required>
                        @foreach ($priorities as $pr)
                            <option value="{{ $pr->priority_id }}" @selected(old('priority_id', $complaint->priority_id) == $pr->priority_id)>
                                {{ $pr->priority_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="status_remarks">Status Update Remarks</label>
                    <textarea name="remarks" id="status_remarks" rows="2" style="min-height: 70px;" placeholder="Optional remarks for complainant and timeline log..."></textarea>
                </div>

                <button class="btn btn-outline btn-block" type="submit">
                    Save Status Update
                </button>
            </form>
        </div>

        <!-- ACTIVITY / AUDIT LOG OF REPORT -->
        <div class="card">
            <h3>Status History</h3>
            <ul class="log">
                @foreach ($complaint->history->reverse() as $h)
                    <li>
                        <b>{{ $h->newStatus->status_name ?? 'Updated' }}</b>
                        @if ($h->changedBy)
                            <span class="muted" style="font-size: 12px;">by {{ $h->changedBy->full_name }}</span>
                        @endif
                        @if ($h->remarks)
                            <div style="margin-top: 3px;">{{ $h->remarks }}</div>
                        @endif
                        <small>{{ $h->changed_at ? $h->changed_at->format('M d, Y h:i A') : '—' }}</small>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
