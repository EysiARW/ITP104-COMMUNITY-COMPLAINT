<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintCategory;
use App\Models\Department;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = ComplaintCategory::with(['department'])
            ->withCount('complaints')
            ->orderBy('category_name')
            ->get();

        $departments = Department::where('is_active', true)->orderBy('department_name')->get();

        return view('admin.categories.index', compact('categories', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:100', 'unique:complaint_categories,category_name'],
            'department_id' => ['nullable', Rule::exists('departments', 'department_id')->where('is_active', true)],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $category = ComplaintCategory::create([
            'category_name' => $validated['category_name'],
            'department_id' => $validated['department_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        Audit::log('CREATE', "Created complaint category '{$category->category_name}'", 'complaint_categories', $category->category_id);

        return back()->with('success', "Category '{$category->category_name}' created successfully.");
    }

    public function update(Request $request, ComplaintCategory $category)
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:100', Rule::unique('complaint_categories', 'category_name')->ignore($category->category_id, 'category_id')],
            'department_id' => ['nullable', 'exists:departments,department_id'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'category_name' => $validated['category_name'],
            'department_id' => $validated['department_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        Audit::log('UPDATE', "Updated complaint category '{$category->category_name}'", 'complaint_categories', $category->category_id);

        return back()->with('success', "Category '{$category->category_name}' updated successfully.");
    }

    public function destroy(ComplaintCategory $category)
    {
        $complaintsCount = $category->complaints()->count();

        if ($complaintsCount > 0) {
            $category->update(['is_active' => false]);
            Audit::log('UPDATE', "Deactivated category '{$category->category_name}' due to existing complaints", 'complaint_categories', $category->category_id);
            return back()->with('warning', "Category '{$category->category_name}' is linked to {$complaintsCount} complaint(s) and was deactivated instead of deleted.");
        }

        $name = $category->category_name;
        $id = $category->category_id;

        try {
            $category->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            $category->update(['is_active' => false]);
            Audit::log('UPDATE', "Deactivated category '{$name}' due to database constraints", 'complaint_categories', $id);
            return back()->with('warning', "Category '{$name}' is referenced by other records and was deactivated instead of deleted.");
        }

        Audit::log('DELETE', "Deleted category '{$name}'", 'complaint_categories', $id);

        return back()->with('success', "Category '{$name}' deleted successfully.");
    }
}
