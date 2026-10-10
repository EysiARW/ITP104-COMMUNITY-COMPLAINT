<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Department;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AreaDepartmentController extends Controller
{
    public function index()
    {
        $areas = Area::withCount(['users', 'complaints'])->orderBy('area_name')->get();
        $departments = Department::with([
            'users' => fn ($q) => $q->where('is_active', true)
                ->withCount('activeAssignments')
                ->orderBy('first_name')
        ])->withCount('users')->orderBy('department_name')->get();

        return view('admin.areas.index', compact('areas', 'departments'));
    }

    /* ================= AREAS ================= */

    public function storeArea(Request $request)
    {
        $validated = $request->validate([
            'area_name' => ['required', 'string', 'max:100', 'unique:areas,area_name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $area = Area::create([
            'area_name' => $validated['area_name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        Audit::log('CREATE', "Created area {$area->area_name}", 'areas', $area->area_id);

        return back()->with('success', "Area '{$area->area_name}' created successfully.");
    }

    public function updateArea(Request $request, Area $area)
    {
        $validated = $request->validate([
            'area_name' => ['required', 'string', 'max:100', Rule::unique('areas', 'area_name')->ignore($area->area_id, 'area_id')],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $area->update([
            'area_name' => $validated['area_name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        Audit::log('UPDATE', "Updated area {$area->area_name}", 'areas', $area->area_id);

        return back()->with('success', "Area '{$area->area_name}' updated successfully.");
    }

    public function destroyArea(Area $area)
    {
        $complaintsCount = $area->complaints()->count();
        $usersCount = $area->users()->count();

        if ($complaintsCount > 0 || $usersCount > 0) {
            $area->update(['is_active' => false]);
            Audit::log('UPDATE', "Deactivated area {$area->area_name} due to existing references", 'areas', $area->area_id);
            return back()->with('warning', "Area '{$area->area_name}' has associated records ({$complaintsCount} complaints, {$usersCount} users) and was deactivated instead of deleted.");
        }

        $name = $area->area_name;
        $id = $area->area_id;

        try {
            $area->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            $area->update(['is_active' => false]);
            Audit::log('UPDATE', "Deactivated area {$name} due to database constraints", 'areas', $id);
            return back()->with('warning', "Area '{$name}' is referenced by other records and was deactivated instead of deleted.");
        }

        Audit::log('DELETE', "Deleted area {$name}", 'areas', $id);

        return back()->with('success', "Area '{$name}' deleted successfully.");
    }

    /* ================= DEPARTMENTS ================= */

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'department_name' => ['required', 'string', 'max:100', 'unique:departments,department_name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $department = Department::create([
            'department_name' => $validated['department_name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        Audit::log('CREATE', "Created department {$department->department_name}", 'departments', $department->department_id);

        return back()->with('success', "Department '{$department->department_name}' created successfully.");
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $validated = $request->validate([
            'department_name' => ['required', 'string', 'max:100', Rule::unique('departments', 'department_name')->ignore($department->department_id, 'department_id')],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $department->update([
            'department_name' => $validated['department_name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        Audit::log('UPDATE', "Updated department {$department->department_name}", 'departments', $department->department_id);

        return back()->with('success', "Department '{$department->department_name}' updated successfully.");
    }

    public function destroyDepartment(Department $department)
    {
        $usersCount = $department->users()->count();
        $categoriesCount = $department->categories()->count();

        if ($usersCount > 0 || $categoriesCount > 0) {
            $department->update(['is_active' => false]);
            Audit::log('UPDATE', "Deactivated department {$department->department_name} due to existing staff assignments or categories", 'departments', $department->department_id);
            return back()->with('warning', "Department '{$department->department_name}' has associated records ({$usersCount} staff, {$categoriesCount} categories) and was deactivated instead of deleted.");
        }

        $name = $department->department_name;
        $id = $department->department_id;

        try {
            $department->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            $department->update(['is_active' => false]);
            Audit::log('UPDATE', "Deactivated department {$name} due to database constraints", 'departments', $id);
            return back()->with('warning', "Department '{$name}' is referenced by other records and was deactivated instead of deleted.");
        }

        Audit::log('DELETE', "Deleted department {$name}", 'departments', $id);

        return back()->with('success', "Department '{$name}' deleted successfully.");
    }
}
