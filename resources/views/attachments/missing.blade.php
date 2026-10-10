@extends('layouts.app')
@section('title', 'Attachment Unavailable')
@section('heading', 'Attachment Unavailable')
@section('subtitle', 'The requested evidence file could not be located on the server storage.')

@section('content')
<div class="card" style="max-width: 680px; margin: 0 auto;">
    <div style="display: flex; align-items: flex-start; gap: 16px; margin-bottom: 20px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(239, 68, 68, 0.12); color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 24px;">
            ⚠️
        </div>
        <div>
            <h2 style="font-size: 18px; margin: 0 0 6px 0;">Evidence File Not Found on Storage Disk</h2>
            <p style="margin: 0; color: var(--muted, #64748b); font-size: 14px; line-height: 1.5;">
                The attachment record exists in the database, but the physical file is no longer present on the server storage disk.
            </p>
        </div>
    </div>

    <div style="background: var(--bg-surface, #f8fafc); border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;">
        <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--muted, #64748b); letter-spacing: 0.05em; margin-bottom: 10px;">
            Attachment Metadata
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
            <tr>
                <td style="padding: 4px 0; color: var(--muted, #64748b); width: 140px;">File Name</td>
                <td style="padding: 4px 0; font-weight: 500;"><strong>{{ $attachment->file_name }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 4px 0; color: var(--muted, #64748b);">File Type</td>
                <td style="padding: 4px 0;">{{ $attachment->file_type ?? 'Unknown' }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0; color: var(--muted, #64748b);">File Size</td>
                <td style="padding: 4px 0;">{{ $attachment->formatted_size }}</td>
            </tr>
            @if ($complaint)
            <tr>
                <td style="padding: 4px 0; color: var(--muted, #64748b);">Complaint Ref</td>
                <td style="padding: 4px 0;"><span class="badge">{{ $complaint->reference_no }}</span></td>
            </tr>
            @endif
            @if ($attachment->uploaded_at)
            <tr>
                <td style="padding: 4px 0; color: var(--muted, #64748b);">Uploaded At</td>
                <td style="padding: 4px 0;">{{ $attachment->uploaded_at->format('M j, Y g:i A') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div style="background: rgba(59, 130, 246, 0.08); border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 4px; margin-bottom: 24px; font-size: 13.5px; line-height: 1.55;">
        <strong>Why did this happen?</strong>
        <p style="margin: 6px 0 0 0; color: var(--text-dark, #334155);">
            Cloud container platforms like Railway use ephemeral disks by default. When the application service restarts or redeploys after a git commit, files saved inside the container are reset unless a persistent storage volume is attached.
        </p>
        <p style="margin: 8px 0 0 0; color: var(--text-dark, #334155);">
            <strong>To retain future uploads permanently:</strong> In your Railway project dashboard, select your Laravel web service &rarr; <strong>Volumes</strong> &rarr; <strong>Add Volume</strong> with mount path <code>/app/storage/app</code>.
        </p>
    </div>

    <div style="display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap;">
        @if ($complaint)
            <a href="{{ auth()->user()->isAdmin() ? route('admin.complaints.show', $complaint->reference_no) : route('complaints.show', $complaint->reference_no) }}" class="btn btn-primary">
                Return to Complaint ({{ $complaint->reference_no }})
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Return to Dashboard</a>
        @endif
    </div>
</div>
@endsection
