<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintStatus;
use App\Models\User;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $complaintsCount = Complaint::count();
        $pendingCount = Complaint::where('status_id', 1)->count();
        $assignedCount = Complaint::whereIn('status_id', [3, 4])->count();
        $resolvedCount = Complaint::whereIn('status_id', [5, 6])->count();
        $residentsCount = User::where('role_id', User::ROLE_COMPLAINANT)->count();
        $pendingRegistrations = User::where('verification_status', User::STATUS_PENDING)->count();

        // 6-Month filing trend
        $months = [];
        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $months[] = $m->format('M Y');
            $trendData[] = Complaint::whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->count();
        }

        // Status distribution
        $statuses = ComplaintStatus::orderBy('sort_order')->get();
        $statusCounts = Complaint::selectRaw('status_id, count(*) as total')
            ->groupBy('status_id')
            ->pluck('total', 'status_id');

        // Top categories
        $categories = ComplaintCategory::withCount('complaints')
            ->orderByDesc('complaints_count')
            ->take(5)
            ->get();

        // Recent complaints requiring review
        $recentComplaints = Complaint::with(['complainant', 'category', 'status', 'priority', 'currentAssignment.assignee'])
            ->latest('created_at')
            ->take(6)
            ->get();

        // Unassigned complaints count
        $unassignedCount = Complaint::whereDoesntHave('currentAssignment')->where('status_id', 1)->count();

        return view('admin.dashboard', [
            'complaints' => $complaintsCount,
            'pending' => $pendingCount,
            'assigned' => $assignedCount,
            'resolved' => $resolvedCount,
            'users' => $residentsCount,
            'pendingRegistrations' => $pendingRegistrations,
            'unassignedCount' => $unassignedCount,
            'months' => $months,
            'trendData' => $trendData,
            'statuses' => $statuses,
            'statusCounts' => $statusCounts,
            'categories' => $categories,
            'recentComplaints' => $recentComplaints,
        ]);
    }
}
