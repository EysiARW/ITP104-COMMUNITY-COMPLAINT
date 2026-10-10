@extends('layouts.app')
@section('title', 'Complaint Categories')
@section('heading', 'Complaint Categories')
@section('subtitle', 'Manage classification categories for reports, incident types, and complaints.')
@section('actions')
    <button class="btn btn-primary" onclick="document.getElementById('add-cat-modal').classList.add('show')">
        <x-icon name="tag" /> Add Category
    </button>
@endsection

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Handling Committee / Department</th>
                    <th>Description</th>
                    <th>Complaints Filed</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($categories as $cat)
                <tr>
                    <td><b>{{ $cat->category_name }}</b></td>
                    <td>
                        @if ($cat->department)
                            <span class="badge badge-assigned" style="font-size:12px;">{{ $cat->department->department_name }}</span>
                        @else
                            <span class="muted" style="font-size:12px;">Unassigned</span>
                        @endif
                    </td>
                    <td>{{ $cat->description ?? '—' }}</td>
                    <td>
                        <strong>{{ $cat->complaints_count }}</strong>
                    </td>
                    <td>
                        @if ($cat->is_active)
                            <span class="badge badge-resolved">Active</span>
                        @else
                            <span class="badge badge-rejected">Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex; gap:6px;">
                            <button class="btn btn-outline btn-sm"
                                    data-id="{{ $cat->category_id }}"
                                    data-name="{{ $cat->category_name }}"
                                    data-desc="{{ $cat->description }}"
                                    data-active="{{ $cat->is_active ? '1' : '0' }}"
                                    data-dept="{{ $cat->department_id }}"
                                    onclick="openEditCategoryModal(this)">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit" 
                                        data-confirm="Are you sure you want to delete or deactivate category '{{ $cat->category_name }}'?" 
                                        data-confirm-title="Remove Category" 
                                        data-confirm-ok="Remove" 
                                        data-confirm-type="danger">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty">No complaint categories found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ADD CATEGORY MODAL -->
<div class="modal" id="add-cat-modal">
    <div class="modal-box">
        <h3>Add Complaint Category</h3>
        <p class="muted">Add a new category that complainants and staff can assign.</p>
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <div class="field">
                <label for="cat_name">Category Name <span class="req">*</span></label>
                <input type="text" name="category_name" id="cat_name" required placeholder="e.g. Stray Animals, Sanitation">
            </div>
            <div class="field">
                <label for="cat_dept">Handling Committee / Department</label>
                <select name="department_id" id="cat_dept">
                    <option value="">None / Unassigned</option>
                    @foreach ($departments as $d)
                        <option value="{{ $d->department_id }}">{{ $d->department_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="cat_desc">Description</label>
                <input type="text" name="description" id="cat_desc" placeholder="Brief description or examples">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT CATEGORY MODAL -->
<div class="modal" id="edit-cat-modal">
    <div class="modal-box">
        <h3>Edit Category</h3>
        <form method="POST" id="edit-cat-form">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="edit_cat_name">Category Name <span class="req">*</span></label>
                <input type="text" name="category_name" id="edit_cat_name" required>
            </div>
            <div class="field">
                <label for="edit_cat_dept">Handling Committee / Department</label>
                <select name="department_id" id="edit_cat_dept">
                    <option value="">None / Unassigned</option>
                    @foreach ($departments as $d)
                        <option value="{{ $d->department_id }}">{{ $d->department_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="edit_cat_desc">Description</label>
                <input type="text" name="description" id="edit_cat_desc">
            </div>
            <div class="field">
                <label class="check">
                    <input type="checkbox" name="is_active" id="edit_cat_active" value="1">
                    <span>Category is active</span>
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditCategoryModal(el) {
    var id = el.dataset.id;
    var name = el.dataset.name;
    var desc = el.dataset.desc || '';
    var active = el.dataset.active === '1';
    var deptId = el.dataset.dept || '';
    var form = document.getElementById('edit-cat-form');
    form.action = "{{ url('admin/categories') }}/" + id;
    document.getElementById('edit_cat_name').value = name;
    document.getElementById('edit_cat_desc').value = desc;
    document.getElementById('edit_cat_dept').value = deptId;
    document.getElementById('edit_cat_active').checked = active;
    document.getElementById('edit-cat-modal').classList.add('show');
}
</script>
@endpush
@endsection
