<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Attachment;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintStatus;
use App\Models\ReportType;
use App\Models\StatusHistory;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Complainant side: list, file, and view own complaints. */
class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::with(['status', 'category'])
            ->where('complainant_id', auth()->id())
            ->latest('created_at');

        if ($request->filled('status')) {
            $query->where('status_id', $request->integer('status'));
        }
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('reference_no', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%"));
        }

        return view('complaints.index', [
            'complaints' => $query->paginate(10)->withQueryString(),
            'statuses' => ComplaintStatus::orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return view('complaints.create', [
            'types' => ReportType::all(),
            'categories' => ComplaintCategory::where('is_active', true)->orderBy('category_name')->get(),
            'areas' => Area::where('is_active', true)->orderBy('area_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'report_type_id' => ['required', 'exists:report_types,report_type_id'],
            'category_id' => ['required', Rule::exists('complaint_categories', 'category_id')->where('is_active', true)],
            'area_id' => ['required', Rule::exists('areas', 'area_id')->where('is_active', true)],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'location_details' => ['nullable', 'string', 'max:255'],
            'incident_date' => ['nullable', 'date', 'before_or_equal:now'],
            'evidence' => ['nullable', 'array', 'max:5'],
            'evidence.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'category_id.exists' => 'The selected category is invalid or currently inactive.',
            'area_id.exists' => 'The selected area is invalid or currently inactive.',
            'description.min' => 'Please describe what happened in at least 20 characters.',
            'evidence.max' => 'You can attach up to 5 files.',
            'evidence.*.max' => 'Each file must be 5 MB or smaller.',
            'evidence.*.mimes' => 'Only JPG, PNG, WEBP, or PDF files are allowed.',
        ]);

        $user = $request->user();

        $complaint = DB::transaction(function () use ($data, $request, $user) {
            $complaint = Complaint::create([
                'reference_no' => 'TMP-' . Str::random(20),
                'complainant_id' => $user->user_id,
                'report_type_id' => $data['report_type_id'],
                'category_id' => $data['category_id'],
                'priority_id' => 2,   // Medium by default; admins re-classify during review
                'area_id' => $data['area_id'],
                'status_id' => 1,     // Pending
                'title' => $data['title'],
                'description' => $data['description'],
                'location_details' => $data['location_details'] ?? null,
                'incident_date' => $data['incident_date'] ?? null,
            ]);

            $complaint->update(['reference_no' => sprintf('BRGY-%s-%05d', now()->year, $complaint->complaint_id)]);

            StatusHistory::create([
                'complaint_id' => $complaint->complaint_id, 'old_status_id' => null, 'new_status_id' => 1,
                'changed_by' => $user->user_id, 'remarks' => 'Complaint submitted',
            ]);

            foreach ($request->file('evidence', []) as $file) {
                Attachment::create([
                    'complaint_id' => $complaint->complaint_id,
                    'uploaded_by' => $user->user_id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'file_path' => $file->store('complaints/' . $complaint->complaint_id, 'local'),
                ]);
            }

            UserNotification::notifyMany(
                User::where('role_id', User::ROLE_ADMIN)->where('is_active', true)->pluck('user_id'),
                "New complaint {$complaint->reference_no} filed by {$user->full_name}",
                $complaint->complaint_id
            );

            return $complaint;
        });

        Audit::log('CREATE', "Filed complaint {$complaint->reference_no}", 'complaints', $complaint->complaint_id);

        return redirect()->route('complaints.show', $complaint)
            ->with('success', "Your complaint was submitted. Your reference number is {$complaint->reference_no}.");
    }

    public function show(Complaint $complaint)
    {
        abort_unless((int) $complaint->complainant_id === (int) auth()->id(), 403);

        $complaint->load([
            'status', 'category', 'reportType', 'priority', 'area', 'history.newStatus',
            'attachments' => fn ($q) => $q->whereNull('investigation_id'),
            'currentAssignment.assignee.department', 'resolution',
        ]);

        return view('complaints.show', compact('complaint'));
    }
}
