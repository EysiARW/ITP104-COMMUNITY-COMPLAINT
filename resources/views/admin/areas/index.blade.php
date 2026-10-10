@extends('layouts.app')
@section('title', 'Departments & Areas')
@section('heading', 'Manage Departments & Areas')
@section('subtitle', 'Manage barangay offices, committees, and jurisdiction puroks/areas.')

@section('content')
<div class="grid-2">
    <!-- DEPARTMENTS SECTION -->
    <div>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3>Barangay Departments & Committees</h3>
                <button class="btn btn-primary btn-sm" onclick="document.getElementById('add-dept-modal').classList.add('show')">
                    + Add Department
                </button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Department Name</th>
                            <th>Staff Count</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($departments as $d)
                        <tr>
                            <td>
                                <b>{{ $d->department_name }}</b>
                                @if ($d->description)
                                    <div class="muted" style="font-size:12.5px;">{{ $d->description }}</div>
                                @endif
                            </td>
                            <td>{{ $d->users_count }}</td>
                            <td>
                                @if ($d->is_active)
                                    <span class="badge badge-resolved">Active</span>
                                @else
                                    <span class="badge badge-rejected">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex; gap:6px;">
                                    <button class="btn btn-outline btn-sm" onclick="openRosterModal('{{ $d->department_id }}')" title="View assigned personnel">
                                        👥 Roster ({{ $d->users->count() }})
                                    </button>
                                    <button class="btn btn-outline btn-sm" 
                                            data-id="{{ $d->department_id }}"
                                            data-name="{{ $d->department_name }}"
                                            data-desc="{{ $d->description }}"
                                            data-active="{{ $d->is_active ? '1' : '0' }}"
                                            onclick="openEditDeptModal(this)">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.departments.destroy', $d) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit" 
                                                data-confirm="Are you sure you want to delete or deactivate department '{{ $d->department_name }}'?" 
                                                data-confirm-title="Remove Department" 
                                                data-confirm-ok="Remove" 
                                                data-confirm-type="danger">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">No departments configured yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- AREAS / PUROKS SECTION -->
    <div>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3>Jurisdiction Areas & Puroks</h3>
                <button class="btn btn-primary btn-sm" onclick="document.getElementById('add-area-modal').classList.add('show')">
                    + Add Area
                </button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Area / Purok</th>
                            <th>Complaints</th>
                            <th>Residents</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($areas as $a)
                        <tr>
                            <td>
                                <b>{{ $a->area_name }}</b>
                                @if ($a->description)
                                    <div class="muted" style="font-size:12.5px;">{{ $a->description }}</div>
                                @endif
                            </td>
                            <td>{{ $a->complaints_count }}</td>
                            <td>{{ $a->users_count }}</td>
                            <td>
                                @if ($a->is_active)
                                    <span class="badge badge-resolved">Active</span>
                                @else
                                    <span class="badge badge-rejected">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex; gap:6px;">
                                    <button class="btn btn-outline btn-sm" 
                                            data-id="{{ $a->area_id }}"
                                            data-name="{{ $a->area_name }}"
                                            data-desc="{{ $a->description }}"
                                            data-active="{{ $a->is_active ? '1' : '0' }}"
                                            onclick="openEditAreaModal(this)">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.areas.destroy', $a) }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit" 
                                                data-confirm="Are you sure you want to delete or deactivate area '{{ $a->area_name }}'?" 
                                                data-confirm-title="Remove Area" 
                                                data-confirm-ok="Remove" 
                                                data-confirm-type="danger">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">No areas configured yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ADD DEPARTMENT MODAL -->
<div class="modal" id="add-dept-modal">
    <div class="modal-box">
        <h3>Add Department</h3>
        <p class="muted">Register a new office, desk, or barangay committee.</p>
        <form method="POST" action="{{ route('admin.departments.store') }}">
            @csrf
            <div class="field">
                <label for="dept_name">Department Name <span class="req">*</span></label>
                <input type="text" name="department_name" id="dept_name" required placeholder="e.g. Lupon Tagapamayapa">
            </div>
            <div class="field">
                <label for="dept_desc">Description</label>
                <input type="text" name="description" id="dept_desc" placeholder="Scope of responsibility">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Department</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT DEPARTMENT MODAL -->
<div class="modal" id="edit-dept-modal">
    <div class="modal-box">
        <h3>Edit Department</h3>
        <form method="POST" id="edit-dept-form">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="edit_dept_name">Department Name <span class="req">*</span></label>
                <input type="text" name="department_name" id="edit_dept_name" required>
            </div>
            <div class="field">
                <label for="edit_dept_desc">Description</label>
                <input type="text" name="description" id="edit_dept_desc">
            </div>
            <div class="field">
                <label class="check">
                    <input type="checkbox" name="is_active" id="edit_dept_active" value="1">
                    <span>Department is active</span>
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ADD AREA MODAL -->
<div class="modal" id="add-area-modal">
    <div class="modal-box">
        <h3>Add Jurisdiction Area</h3>
        <p class="muted">Add a new Purok, Sitio, or sector in Barangay Banlic.</p>
        <form method="POST" action="{{ route('admin.areas.store') }}">
            @csrf
            <div class="field">
                <label for="area_name">Area / Purok Name <span class="req">*</span></label>
                <input type="text" name="area_name" id="area_name" required placeholder="e.g. Purok 6">
            </div>
            <div class="field">
                <label for="area_desc">Description / Landmarks</label>
                <input type="text" name="description" id="area_desc" placeholder="Coverage or boundary note">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Area</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT AREA MODAL -->
<div class="modal" id="edit-area-modal">
    <div class="modal-box">
        <h3>Edit Area</h3>
        <form method="POST" id="edit-area-form">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="edit_area_name">Area / Purok Name <span class="req">*</span></label>
                <input type="text" name="area_name" id="edit_area_name" required>
            </div>
            <div class="field">
                <label for="edit_area_desc">Description / Landmarks</label>
                <input type="text" name="description" id="edit_area_desc">
            </div>
            <div class="field">
                <label class="check">
                    <input type="checkbox" name="is_active" id="edit_area_active" value="1">
                    <span>Area is active</span>
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ROSTER MODALS -->
@foreach ($departments as $d)
<div class="modal" id="roster-modal-{{ $d->department_id }}">
    <div class="modal-box" style="max-width: 640px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom: 14px;">
            <div>
                <h3 style="margin:0; font-size:18px;">{{ $d->department_name }}</h3>
                <div class="muted" style="font-size:12.5px;">Committee Personnel &amp; Officer Roster</div>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-outline btn-sm">+ Add Officer</a>
        </div>

        @if ($d->users->isEmpty())
            <div class="empty" style="padding: 24px 0;">
                <p>No active personnel assigned to this committee yet.</p>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm" style="margin-top: 8px;">Assign Personnel</a>
            </div>
        @else
            <div class="table-wrap" style="max-height: min(320px, 48vh); overflow-y: auto; -webkit-overflow-scrolling: touch;">
                <table>
                    <thead>
                        <tr>
                            <th>Personnel &amp; Position</th>
                            <th>Contact</th>
                            <th>Active Cases</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($d->users as $u)
                        <tr>
                            <td>
                                <b>{{ $u->full_name }}</b>
                                <div style="font-size:12px; color:var(--blue); font-weight:500;">
                                    {{ $u->committee_position ?: 'Committee Member' }}
                                </div>
                            </td>
                            <td style="font-size:12px;">
                                <div>{{ $u->contact_number ?: 'No phone' }}</div>
                                <div class="muted" style="font-size:11px;">{{ $u->email }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $u->active_assignments_count > 0 ? 'badge-in-progress' : 'badge-closed' }}" style="font-size:11.5px;">
                                    {{ $u->active_assignments_count }} active
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-outline btn-sm">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="modal-actions" style="margin-top: 16px;">
            <button type="button" class="btn btn-primary" onclick="this.closest('.modal').classList.remove('show')">Close</button>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
function openRosterModal(id) {
    document.getElementById('roster-modal-' + id).classList.add('show');
}

function openEditDeptModal(el) {
    var id = el.dataset.id;
    var name = el.dataset.name;
    var desc = el.dataset.desc || '';
    var active = el.dataset.active === '1';
    var form = document.getElementById('edit-dept-form');
    form.action = "{{ url('admin/departments') }}/" + id;
    document.getElementById('edit_dept_name').value = name;
    document.getElementById('edit_dept_desc').value = desc;
    document.getElementById('edit_dept_active').checked = active;
    document.getElementById('edit-dept-modal').classList.add('show');
}

function openEditAreaModal(el) {
    var id = el.dataset.id;
    var name = el.dataset.name;
    var desc = el.dataset.desc || '';
    var active = el.dataset.active === '1';
    var form = document.getElementById('edit-area-form');
    form.action = "{{ url('admin/areas') }}/" + id;
    document.getElementById('edit_area_name').value = name;
    document.getElementById('edit_area_desc').value = desc;
    document.getElementById('edit_area_active').checked = active;
    document.getElementById('edit-area-modal').classList.add('show');
}
</script>
@endpush
@endsection
