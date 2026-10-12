<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'department', 'area']);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");

                $words = array_values(array_filter(explode(' ', $search)));
                if (count($words) > 1) {
                    $q->orWhere(function ($sub) use ($words) {
                        foreach ($words as $w) {
                            $sub->where(function ($inner) use ($w) {
                                $inner->where('first_name', 'like', "%{$w}%")
                                    ->orWhere('last_name', 'like', "%{$w}%");
                            });
                        }
                    });
                }
            });
        }

        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        if ($vStatus = $request->input('verification_status')) {
            $query->where('verification_status', $vStatus);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === '1');
        }

        $users = $query->latest('user_id')->paginate(15)->withQueryString();
        $roles = Role::all();
        $pendingCount = User::where('verification_status', User::STATUS_PENDING)->count();

        return view('admin.users.index', compact('users', 'roles', 'pendingCount'));
    }

    public function pending(Request $request)
    {
        $query = User::with(['area'])
            ->where('verification_status', User::STATUS_PENDING);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");

                $words = array_values(array_filter(explode(' ', $search)));
                if (count($words) > 1) {
                    $q->orWhere(function ($sub) use ($words) {
                        foreach ($words as $w) {
                            $sub->where(function ($inner) use ($w) {
                                $inner->where('first_name', 'like', "%{$w}%")
                                    ->orWhere('last_name', 'like', "%{$w}%");
                            });
                        }
                    });
                }
            });
        }

        $pendingUsers = $query->latest('created_at')->paginate(15)->withQueryString();
        $pendingCount = $pendingUsers->total();

        return view('admin.users.pending', compact('pendingUsers', 'pendingCount'));
    }

    public function document(\Illuminate\Http\Request $request, User $user)
    {
        abort_unless($user->verification_doc_path, 404);

        $path = $user->verification_doc_path;
        $resolved = \App\Http\Controllers\AttachmentController::resolveFilePath($path);

        if ($resolved) {
            if (isset($resolved['disk']) && isset($resolved['relative'])) {
                try {
                    return $resolved['disk']->response(
                        $resolved['relative'],
                        $user->verification_doc_name ?? 'verification_document',
                        ['Content-Disposition' => 'inline']
                    );
                } catch (\Throwable) {
                }
            }

            if (isset($resolved['full_path']) && file_exists($resolved['full_path'])) {
                $mime = mime_content_type($resolved['full_path']) ?: 'application/octet-stream';
                return response()->file($resolved['full_path'], [
                    'Content-Type' => $mime,
                    'Content-Disposition' => 'inline; filename="' . addslashes($user->verification_doc_name ?? 'verification_document') . '"',
                ]);
            }
        }

        if ($request->header('Sec-Fetch-Dest') === 'image' || str_contains($request->header('Accept', ''), 'image/')) {
            return \App\Http\Controllers\AttachmentController::placeholderSvg($user->verification_doc_name ?? 'verification_document');
        }

        return response()->view('attachments.missing', [
            'attachment' => (object) [
                'file_name' => $user->verification_doc_name ?? 'verification_document',
                'file_type' => $user->verification_doc_type ?? 'Unknown',
                'formatted_size' => 'N/A',
                'uploaded_at' => $user->created_at,
            ],
            'complaint' => null,
            'returnUrl' => route('admin.users.pending'),
            'returnText' => 'Return to Pending Applicants',
        ], 404);
    }

    public function approve(User $user)
    {
        $user->update([
            'verification_status' => User::STATUS_APPROVED,
            'is_active' => true,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'rejection_reason' => null,
        ]);

        Audit::log('APPROVE_USER', "Approved registration and verified documents for {$user->full_name} ({$user->email})", 'users', $user->user_id);

        return back()->with('success', "Account for {$user->full_name} has been approved and activated. The resident can now log in.");
    }

    public function reject(Request $request, User $user)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
        ], [
            'rejection_reason.required' => 'Please provide a reason for rejecting the registration.',
        ]);

        $user->update([
            'verification_status' => User::STATUS_REJECTED,
            'is_active' => false,
            'rejection_reason' => $request->input('rejection_reason'),
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        Audit::log('REJECT_USER', "Rejected registration for {$user->full_name} ({$user->email}): {$request->input('rejection_reason')}", 'users', $user->user_id);

        return back()->with('warning', "Registration for {$user->full_name} has been rejected.");
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles' => Role::all(),
            'departments' => Department::where('is_active', true)->orderBy('department_name')->get(),
            'areas' => Area::where('is_active', true)->orderBy('area_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,role_id'],
            'first_name' => ['required', 'string', 'max:60'],
            'middle_name' => ['nullable', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'contact_number' => ['nullable', 'regex:/^(09|\+639)\d{9}$/'],
            'department_id' => ['nullable', Rule::exists('departments', 'department_id')->where('is_active', true)],
            'committee_position' => ['nullable', 'string', 'max:100'],
            'area_id' => ['nullable', Rule::exists('areas', 'area_id')->where('is_active', true)],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'department_id.exists' => 'The selected department is invalid or currently inactive.',
            'area_id.exists' => 'The selected area is invalid or currently inactive.',
            'contact_number.regex' => 'Please enter a valid Philippine mobile number (e.g., 09171234567).',
        ]);

        $isStaff = (int) $validated['role_id'] === User::ROLE_ADMIN;

        $user = User::create([
            'role_id' => $validated['role_id'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'contact_number' => $validated['contact_number'] ?? null,
            'department_id' => $isStaff ? ($validated['department_id'] ?? null) : null,
            'committee_position' => $isStaff ? ($validated['committee_position'] ?? null) : null,
            'area_id' => $validated['area_id'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $request->has('is_active'),
            'verification_status' => User::STATUS_APPROVED,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        Audit::log('CREATE', "Created user account for {$user->full_name} ({$user->email})", 'users', $user->user_id);

        return redirect()->route('admin.users.index')->with('success', "User account {$user->full_name} created successfully.");
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user->load(['role', 'department', 'area']),
            'roles' => Role::all(),
            'departments' => Department::orderBy('department_name')->get(),
            'areas' => Area::orderBy('area_name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,role_id'],
            'first_name' => ['required', 'string', 'max:60'],
            'middle_name' => ['nullable', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'password' => ['nullable', 'string', 'min:8'],
            'contact_number' => ['nullable', 'regex:/^(09|\+639)\d{9}$/'],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'department_id')->where(fn ($q) => $q->where('is_active', true)->orWhere('department_id', $user->department_id)),
            ],
            'committee_position' => ['nullable', 'string', 'max:100'],
            'area_id' => [
                'nullable',
                Rule::exists('areas', 'area_id')->where(fn ($q) => $q->where('is_active', true)->orWhere('area_id', $user->area_id)),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'contact_number.regex' => 'Please enter a valid Philippine mobile number (e.g., 09171234567).',
        ]);

        $isStaff = (int) $validated['role_id'] === User::ROLE_ADMIN;

        $updateData = [
            'role_id' => $validated['role_id'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'] ?? null,
            'department_id' => $isStaff ? ($validated['department_id'] ?? null) : null,
            'committee_position' => $isStaff ? ($validated['committee_position'] ?? null) : null,
            'area_id' => $validated['area_id'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $request->has('is_active'),
        ];

        if (!empty($validated['password'])) {
            $updateData['password_hash'] = Hash::make($validated['password']);
        }

        // Prevent self-demotion or self-deactivation if currently logged in user is editing themselves
        if ($user->user_id === auth()->id()) {
            $updateData['is_active'] = true;
            $updateData['role_id'] = User::ROLE_ADMIN;
        }

        $user->update($updateData);

        Audit::log('UPDATE', "Updated user account for {$user->full_name} ({$user->email})", 'users', $user->user_id);

        return redirect()->route('admin.users.index')->with('success', "User account {$user->full_name} updated successfully.");
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function destroy(User $user)
    {
        if ($user->user_id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account while logged in.');
        }

        // Check if user has associated complaints, status history, assignments, investigations, resolutions, or attachments
        $complaintsCount = $user->complaints()->count();
        $hasHistory = \App\Models\StatusHistory::where('changed_by', $user->user_id)->exists();
        $hasAssignments = \App\Models\ComplaintAssignment::where('assigned_to', $user->user_id)->orWhere('assigned_by', $user->user_id)->exists();
        $hasInvestigations = \App\Models\Investigation::where('investigator_id', $user->user_id)->exists();
        $hasResolutions = \App\Models\Resolution::where('resolved_by', $user->user_id)->exists();
        $hasAttachments = \App\Models\Attachment::where('uploaded_by', $user->user_id)->exists();

        if ($complaintsCount > 0 || $hasHistory || $hasAssignments || $hasInvestigations || $hasResolutions || $hasAttachments) {
            // Soft deactivate instead of hard delete to maintain foreign key integrity
            $user->update(['is_active' => false]);
            Audit::log('UPDATE', "Deactivated user account {$user->full_name} due to existing system records", 'users', $user->user_id);
            return back()->with('warning', "User {$user->full_name} has existing activity/complaint records and was deactivated instead of deleted.");
        }

        $fullName = $user->full_name;
        $userId = $user->user_id;

        try {
            $user->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            $user->update(['is_active' => false]);
            Audit::log('UPDATE', "Deactivated user account {$fullName} due to foreign key constraints", 'users', $userId);
            return back()->with('warning', "User {$fullName} is referenced by existing database records and was deactivated instead of deleted.");
        }

        Audit::log('DELETE', "Deleted user account {$fullName}", 'users', $userId);

        return redirect()->route('admin.users.index')->with('success', "User {$fullName} has been removed.");
    }
}
