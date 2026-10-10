@extends('layouts.app')
@section('title', 'Pending Registrations')
@section('heading', 'Account Approval Queue')
@section('subtitle', 'Review submitted resident documents and approve or reject registrations.')
@section('actions')
    <a class="btn btn-outline" href="{{ route('admin.users.index') }}">
        <x-icon name="users" /> Back to All Users
    </a>
@endsection

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 style="margin-bottom: 2px;">Residents Awaiting Verification</h3>
            <p class="muted" style="margin: 0; font-size: 13.5px;">These accounts cannot log in until approved by barangay administration.</p>
        </div>
        <div>
            <span class="badge badge-pending" style="font-size: 14px; padding: 6px 14px;">
                {{ $pendingCount }} Pending {{ \Illuminate\Support\Str::plural('Applicant', $pendingCount) }}
            </span>
        </div>
    </div>

    @if ($pendingUsers->isEmpty())
        <div class="empty">
            <x-icon name="check-circle" style="width: 48px; height: 48px; color: var(--ok); margin-bottom: 12px;" />
            <h3 style="font-size: 18px; margin-bottom: 4px;">Queue is clear!</h3>
            <p>There are no resident account registrations awaiting approval.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Purok / Address</th>
                        <th>Contact</th>
                        <th>Submitted Document</th>
                        <th>Date Registered</th>
                        <th style="text-align: right;">Review Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($pendingUsers as $u)
                    <tr>
                        <td>
                            <b>{{ $u->full_name }}</b>
                            <div class="muted" style="font-size: 13px;">{{ $u->email }}</div>
                        </td>
                        <td>
                            <div><strong>{{ $u->area ? $u->area->area_name : 'Unspecified Purok' }}</strong></div>
                            <div class="muted" style="font-size: 12.5px;">{{ $u->address ?? 'No street address' }}</div>
                        </td>
                        <td>{{ $u->contact_number ?? '—' }}</td>
                        <td>
                            @php
                                $typeLabels = [
                                    'voters_cert' => "Voter's Certification",
                                    'valid_id' => 'Valid Government ID',
                                    'certificate_of_residency' => 'Certificate of Residency',
                                ];
                                $docLabel = $typeLabels[$u->verification_doc_type] ?? 'Identity Document';
                                $isPdf = str_ends_with(strtolower($u->verification_doc_name ?? ''), '.pdf');
                            @endphp
                            <div>
                                <span class="badge badge-assigned">{{ $docLabel }}</span>
                            </div>
                            @if ($u->verification_doc_path)
                                <div style="margin-top: 6px;">
                                    <button type="button" class="btn btn-outline btn-sm btn-doc-preview"
                                            data-url="{{ route('admin.users.document', $u) }}"
                                            data-name="{{ $u->full_name }}"
                                            data-type="{{ $docLabel }}"
                                            data-file="{{ $u->verification_doc_name ?? 'Document' }}"
                                            data-is-pdf="{{ $isPdf ? '1' : '0' }}">
                                        <x-icon name="search" /> Quick Preview
                                    </button>
                                </div>
                            @else
                                <span class="muted" style="font-size: 12px;">No document file</span>
                            @endif
                        </td>
                        <td>{{ $u->created_at ? $u->created_at->format('M d, Y h:i A') : '—' }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 8px;">
                                <form method="POST" action="{{ route('admin.users.approve', $u) }}" style="display: inline;">
                                    @csrf
                                    <button class="btn btn-primary btn-sm" type="submit"
                                            data-confirm="Approve account for resident {{ $u->full_name }}? They will immediately be granted login access."
                                            data-confirm-title="Approve Registration"
                                            data-confirm-ok="Approve Account">
                                        Approve
                                    </button>
                                </form>

                                <button class="btn btn-danger btn-sm btn-reject-user" type="button"
                                        data-user-id="{{ $u->user_id }}"
                                        data-full-name="{{ $u->full_name }}">
                                    Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $pendingUsers->links('pagination.custom') }}
    @endif
</div>

<!-- IN-APP DOCUMENT PREVIEW LIGHTBOX MODAL -->
<div class="modal" id="doc-preview-modal" style="z-index: 250;">
    <div class="modal-box" style="max-width: 780px; width: 95%; max-height: 90vh; display: flex; flex-direction: column; padding: 22px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; border-bottom: 1px solid var(--line); padding-bottom: 10px;">
            <div>
                <h3 id="doc-modal-title" style="margin: 0; font-size: 20px;">Document Preview</h3>
                <p id="doc-modal-subtitle" class="muted" style="margin: 2px 0 0; font-size: 13px;"></p>
            </div>
            <button type="button" onclick="closeDocPreviewModal()" style="border: 0; background: none; font-size: 24px; cursor: pointer; color: var(--muted); line-height: 1; padding: 0 4px;">
                &times;
            </button>
        </div>

        <div id="doc-preview-container" style="flex: 1; min-height: 320px; max-height: 60vh; max-height: 60dvh; overflow: auto; display: flex; justify-content: center; align-items: center; background: var(--bg); border-radius: 10px; border: 1px solid var(--line); padding: 12px;">
            <!-- Rendered image or iframe injected via JS -->
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 10px; border-top: 1px solid var(--line);">
            <a id="doc-modal-external" href="#" target="_blank" class="btn btn-outline btn-sm">
                Open in New Tab &nearr;
            </a>
            <button type="button" class="btn btn-light" onclick="closeDocPreviewModal()">
                Close Preview
            </button>
        </div>
    </div>
</div>

<!-- REJECT REASON MODAL -->
<div class="modal" id="reject-modal">
    <div class="modal-box">
        <h3 style="color: var(--bad);">Decline Registration</h3>
        <p class="muted" id="reject-user-label">Please explain why this registration is being rejected so the applicant can correct it.</p>
        <form method="POST" id="reject-form">
            @csrf
            <div class="field">
                <label for="rejection_reason">Reason for Rejection <span class="req">*</span></label>
                <textarea name="rejection_reason" id="rejection_reason" rows="3" required placeholder="e.g. Blurry ID, Address not within Barangay Banlic, Expired document..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn btn-danger">Decline Registration</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openDocPreviewModal(url, fullName, docType, fileName, isPdf) {
    const modal = document.getElementById('doc-preview-modal');
    const title = document.getElementById('doc-modal-title');
    const subtitle = document.getElementById('doc-modal-subtitle');
    const container = document.getElementById('doc-preview-container');
    const externalLink = document.getElementById('doc-modal-external');

    title.textContent = fullName + " — " + docType;
    subtitle.textContent = "File: " + fileName;
    externalLink.href = url;

    container.innerHTML = "";
    if (isPdf) {
        const iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.style.width = "100%";
        iframe.style.height = "480px";
        iframe.style.border = "none";
        iframe.style.borderRadius = "8px";
        container.appendChild(iframe);
    } else {
        const img = document.createElement('img');
        img.src = url;
        img.alt = docType;
        img.style.maxWidth = "100%";
        img.style.maxHeight = "55vh";
        img.style.objectFit = "contain";
        img.style.borderRadius = "8px";
        img.style.boxShadow = "0 4px 16px rgba(0,0,0,0.1)";
        img.onerror = function() {
            container.innerHTML = '<div style="text-align: center; padding: 28px 16px; color: var(--muted);"><p style="font-weight: 600; color: var(--bad); margin-bottom: 6px;">Document file unavailable</p><p style="font-size: 13px; max-width: 440px; margin: 0 auto;">The uploaded file was stored in a previous deployment container and is not present in persistent storage. You can still approve or reject based on resident details.</p></div>';
        };
        container.appendChild(img);
    }

    modal.classList.add('show');
}

function closeDocPreviewModal() {
    const modal = document.getElementById('doc-preview-modal');
    if (modal) {
        modal.classList.remove('show');
        const container = document.getElementById('doc-preview-container');
        if (container) container.innerHTML = "";
    }
}

// Close preview modal on ESC or backdrop click
document.getElementById('doc-preview-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDocPreviewModal();
    }
});

function openRejectModal(userId, fullName) {
    var form = document.getElementById('reject-form');
    form.action = "{{ url('admin/users') }}/" + userId + "/reject";
    document.getElementById('reject-user-label').textContent = "Reject registration for " + fullName + ". Please specify the reason:";
    document.getElementById('rejection_reason').value = "";
    document.getElementById('reject-modal').classList.add('show');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-doc-preview').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openDocPreviewModal(
                this.dataset.url,
                this.dataset.name,
                this.dataset.type,
                this.dataset.file,
                this.dataset.isPdf === '1'
            );
        });
    });

    document.querySelectorAll('.btn-reject-user').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openRejectModal(this.dataset.userId, this.dataset.fullName);
        });
    });
});
</script>
@endpush
@endsection
