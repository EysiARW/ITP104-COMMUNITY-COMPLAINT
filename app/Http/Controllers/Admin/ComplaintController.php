<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Complaint;
use App\Models\ComplaintAssignment;
use App\Models\ComplaintCategory;
use App\Models\Department;
use App\Models\ComplaintStatus;
use App\Models\PriorityLevel;
use App\Models\StatusHistory;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::with(['complainant', 'category', 'status', 'priority', 'area', 'currentAssignment.assignee'])
            ->latest('created_at');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('reference_no', 'like', "%{$q}%")
                    ->orWhereHas('complainant', fn ($c) => $c->where('first_name', 'like', "%{$q}%")->orWhere('last_name', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->integer('status_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('priority_id')) {
            $query->where('priority_id', $request->integer('priority_id'));
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->integer('area_id'));
        }

        if ($request->input('assigned') === 'unassigned') {
            $query->whereDoesntHave('currentAssignment');
        } elseif ($request->input('assigned') === 'me') {
            $query->whereHas('currentAssignment', fn ($a) => $a->where('assigned_to', auth()->id()));
        }

        $tab = $request->query('tab', 'all');
        if ($tab === 'needs_review') {
            $query->where('status_id', 1);
        } elseif ($tab === 'unassigned') {
            $query->whereDoesntHave('currentAssignment');
        } elseif ($tab === 'in_progress') {
            $query->whereIn('status_id', [3, 4]);
        } elseif ($tab === 'resolved') {
            $query->where('status_id', 5);
        }

        $complaints = $query->paginate(15)->withQueryString();

        $statuses = ComplaintStatus::orderBy('sort_order')->get();
        $categories = ComplaintCategory::orderBy('category_name')->get();
        $priorities = PriorityLevel::orderBy('sort_order')->get();
        $areas = Area::orderBy('area_name')->get();

        // Counts for quick-filter pills
        $pillCounts = [
            'all' => Complaint::count(),
            'needs_review' => Complaint::where('status_id', 1)->count(),
            'unassigned' => Complaint::whereDoesntHave('currentAssignment')->count(),
            'in_progress' => Complaint::whereIn('status_id', [3, 4])->count(),
            'resolved' => Complaint::where('status_id', 5)->count(),
        ];

        return view('admin.complaints.index', compact(
            'complaints', 'statuses', 'categories', 'priorities', 'areas', 'pillCounts', 'tab'
        ));
    }

    public function show(Complaint $complaint)
    {
        $complaint->load([
            'complainant', 'category.department', 'reportType', 'priority', 'area', 'status',
            'attachments' => fn ($q) => $q->whereNull('investigation_id'),
            'currentAssignment.assignee.department',
            'assignments.assignee.department', 'assignments.assigner',
            'history.newStatus', 'history.changedBy',
            'investigations.investigator',
            'resolution.resolver',
        ]);

        $statuses = ComplaintStatus::orderBy('sort_order')->get();
        $priorities = PriorityLevel::orderBy('sort_order')->get();
        $departments = Department::where('is_active', true)
            ->with(['officers' => fn ($q) => $q->withCount('activeAssignments')->orderBy('first_name')])
            ->orderBy('department_name')
            ->get();
        $officers = User::where('role_id', User::ROLE_ADMIN)
            ->where('is_active', true)
            ->with('department')
            ->withCount('activeAssignments')
            ->orderBy('first_name')
            ->get();

        return view('admin.complaints.show', compact(
            'complaint', 'statuses', 'priorities', 'officers', 'departments'
        ));
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'status_id' => ['required', 'exists:complaint_statuses,status_id'],
            'priority_id' => ['required', 'exists:priority_levels,priority_id'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatusId = $complaint->status_id;
        $newStatusId = (int) $validated['status_id'];
        $statusChanged = $oldStatusId !== $newStatusId;

        DB::transaction(function () use ($complaint, $validated, $oldStatusId, $newStatusId, $statusChanged) {
            $complaint->update([
                'status_id' => $newStatusId,
                'priority_id' => $validated['priority_id'],
            ]);

            if ($statusChanged || !empty($validated['remarks'])) {
                StatusHistory::create([
                    'complaint_id' => $complaint->complaint_id,
                    'old_status_id' => $oldStatusId,
                    'new_status_id' => $newStatusId,
                    'changed_by' => auth()->id(),
                    'remarks' => $validated['remarks'] ?? 'Status updated by administrator',
                ]);
            }

            // Notify resident complainant of progress update
            if ($statusChanged && $complaint->complainant_id) {
                $statusName = ComplaintStatus::find($newStatusId)?->status_name ?? 'Updated';
                UserNotification::create([
                    'user_id' => $complaint->complainant_id,
                    'complaint_id' => $complaint->complaint_id,
                    'message' => "Your report {$complaint->reference_no} status changed to {$statusName}.",
                ]);
            }
        });

        Audit::log('UPDATE_STATUS', "Changed status of {$complaint->reference_no} to status ID {$newStatusId}", 'complaints', $complaint->complaint_id);

        return back()->with('success', 'Complaint status & priority updated successfully.');
    }

    public function assign(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'assigned_to' => [
                'required',
                Rule::exists('users', 'user_id')->where(fn ($q) => $q->where('role_id', User::ROLE_ADMIN)->where('is_active', true)),
            ],
            'remarks' => ['nullable', 'string', 'max:500'],
        ], [
            'assigned_to.exists' => 'The selected officer must be an active administrator/staff member.',
        ]);

        $officer = User::with('department')->findOrFail($validated['assigned_to']);

        DB::transaction(function () use ($complaint, $validated, $officer) {
            // Close any existing active assignment
            ComplaintAssignment::where('complaint_id', $complaint->complaint_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            // Create new active assignment
            ComplaintAssignment::create([
                'complaint_id' => $complaint->complaint_id,
                'assigned_to' => $officer->user_id,
                'assigned_by' => auth()->id(),
                'remarks' => $validated['remarks'] ?? null,
                'is_current' => true,
            ]);

            $deptStr = $officer->department ? " ({$officer->department->department_name})" : '';

            // Automatically move status to "Assigned" (ID: 3) if still Pending or Under Review
            if (in_array((int) $complaint->status_id, [1, 2])) {
                $oldStatus = $complaint->status_id;
                $complaint->update(['status_id' => 3]); // Assigned

                StatusHistory::create([
                    'complaint_id' => $complaint->complaint_id,
                    'old_status_id' => $oldStatus,
                    'new_status_id' => 3,
                    'changed_by' => auth()->id(),
                    'remarks' => "Assigned to {$officer->full_name}{$deptStr}",
                ]);
            }

            // Notify assigned officer
            if ($officer->user_id !== auth()->id()) {
                UserNotification::create([
                    'user_id' => $officer->user_id,
                    'complaint_id' => $complaint->complaint_id,
                    'message' => "You have been assigned to handle report {$complaint->reference_no} ({$complaint->title}).",
                ]);
            }

            // Notify complainant
            if ($complaint->complainant_id) {
                UserNotification::create([
                    'user_id' => $complaint->complainant_id,
                    'complaint_id' => $complaint->complaint_id,
                    'message' => "Your report {$complaint->reference_no} has been assigned to Officer {$officer->full_name}{$deptStr}.",
                ]);
            }
        });

        $deptStr = $officer->department ? " ({$officer->department->department_name})" : '';
        Audit::log('ASSIGN', "Assigned report {$complaint->reference_no} to officer {$officer->full_name}{$deptStr}", 'complaint_assignments', $complaint->complaint_id);

        return back()->with('success', "Complaint has been assigned to {$officer->full_name}{$deptStr}.");
    }
}
