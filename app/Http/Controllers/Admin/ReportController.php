<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintStatus;
use App\Models\PriorityLevel;
use App\Support\Audit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::with(['complainant', 'category', 'status', 'priority', 'area']);

        // Date range
        if (($from = $request->input('date_from')) && strtotime($from) !== false) {
            $query->whereDate('created_at', '>=', $from);
        }
        if (($to = $request->input('date_to')) && strtotime($to) !== false) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Filters
        if ($statusId = $request->input('status_id')) {
            $query->where('status_id', $statusId);
        }
        if ($priorityId = $request->input('priority_id')) {
            $query->where('priority_id', $priorityId);
        }
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($areaId = $request->input('area_id')) {
            $query->where('area_id', $areaId);
        }

        // Clone query for stats and charts before pagination
        $summaryQuery = clone $query;
        $totalCount = $summaryQuery->count();

        // Status breakdown
        $statusBreakdown = (clone $query)
            ->selectRaw('status_id, count(*) as count')
            ->groupBy('status_id')
            ->pluck('count', 'status_id')
            ->toArray();

        // Category breakdown
        $categoryBreakdown = (clone $query)
            ->selectRaw('category_id, count(*) as count')
            ->groupBy('category_id')
            ->pluck('count', 'category_id')
            ->toArray();

        // Area breakdown
        $areaBreakdown = (clone $query)
            ->selectRaw('area_id, count(*) as count')
            ->groupBy('area_id')
            ->pluck('count', 'area_id')
            ->toArray();

        // Monthly trend for past 6 months
        $trendMonths = [];
        $trendCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $key = $monthDate->format('M Y');
            $count = Complaint::whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->count();
            $trendMonths[] = $key;
            $trendCounts[] = $count;
        }

        // Paginated list
        $complaints = $query->latest('complaint_id')->paginate(15)->withQueryString();

        $statuses = ComplaintStatus::orderBy('sort_order')->get();
        $categories = ComplaintCategory::orderBy('category_name')->get();
        $priorities = PriorityLevel::orderBy('sort_order')->get();
        $areas = Area::orderBy('area_name')->get();

        return view('admin.reports.index', compact(
            'complaints',
            'totalCount',
            'statusBreakdown',
            'categoryBreakdown',
            'areaBreakdown',
            'trendMonths',
            'trendCounts',
            'statuses',
            'categories',
            'priorities',
            'areas'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Complaint::with(['complainant', 'category', 'status', 'priority', 'area']);

        if (($from = $request->input('date_from')) && strtotime($from) !== false) {
            $query->whereDate('created_at', '>=', $from);
        }
        if (($to = $request->input('date_to')) && strtotime($to) !== false) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($statusId = $request->input('status_id')) {
            $query->where('status_id', $statusId);
        }
        if ($priorityId = $request->input('priority_id')) {
            $query->where('priority_id', $priorityId);
        }
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($areaId = $request->input('area_id')) {
            $query->where('area_id', $areaId);
        }

        $records = $query->latest('complaint_id')->get();

        Audit::log('EXPORT', "Exported {$records->count()} complaints report as CSV", 'complaints');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="banlic_complaints_report_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM so Excel opens it with proper encoding
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'Reference No',
                'Title',
                'Complainant',
                'Email',
                'Contact',
                'Category',
                'Priority',
                'Status',
                'Area / Purok',
                'Incident Date',
                'Date Filed',
            ]);

            foreach ($records as $item) {
                fputcsv($handle, [
                    $item->reference_no,
                    $item->title,
                    $item->complainant ? $item->complainant->full_name : 'N/A',
                    $item->complainant ? $item->complainant->email : 'N/A',
                    $item->complainant ? $item->complainant->contact_number : 'N/A',
                    $item->category ? $item->category->category_name : 'N/A',
                    $item->priority ? $item->priority->priority_name : 'N/A',
                    $item->status ? $item->status->status_name : 'N/A',
                    $item->area ? $item->area->area_name : 'N/A',
                    $item->incident_date ? $item->incident_date->format('Y-m-d H:i') : 'N/A',
                    $item->created_at ? $item->created_at->format('Y-m-d H:i') : 'N/A',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function print(Request $request)
    {
        $query = Complaint::with(['complainant', 'category', 'status', 'priority', 'area']);

        if (($from = $request->input('date_from')) && strtotime($from) !== false) {
            $query->whereDate('created_at', '>=', $from);
        }
        if (($to = $request->input('date_to')) && strtotime($to) !== false) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($statusId = $request->input('status_id')) {
            $query->where('status_id', $statusId);
        }
        if ($priorityId = $request->input('priority_id')) {
            $query->where('priority_id', $priorityId);
        }
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($areaId = $request->input('area_id')) {
            $query->where('area_id', $areaId);
        }

        $complaints = $query->latest('complaint_id')->get();

        Audit::log('PRINT', "Generated printable summary report for {$complaints->count()} complaints", 'complaints');

        return view('admin.reports.print', [
            'complaints' => $complaints,
            'filters' => $request->all(),
            'generatedAt' => Carbon::now(),
        ]);
    }
}
