{{-- STATUS BADGE. Usage: <x-status-badge :status="$complaint->status" />  (Balbero can extend this) --}}
@props(['status'])
<span class="badge badge-{{ \Illuminate\Support\Str::slug($status?->status_name ?? 'unknown') }}">{{ $status?->status_name ?? 'Unknown' }}</span>
