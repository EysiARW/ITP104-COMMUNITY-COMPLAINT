<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $total = Complaint::count();
        $stats = [
            'submitted' => $total,
            'resolved' => Complaint::whereIn('status_id', [5, 6])->count(),
            'reviewed' => $total ? round(Complaint::where('status_id', '>', 1)->count() / $total * 100) : 0,
        ];

        return view('home', compact('stats'));
    }

    /** Public tracking by reference number. Shows progress only, never personal details. */
    public function track(Request $request)
    {
        $complaint = null;
        $ref = trim((string) $request->query('reference'));

        if ($ref !== '') {
            $complaint = Complaint::with(['status', 'category', 'area', 'history', 'complainant'])
                ->where('reference_no', strtoupper($ref))->first();
        }

        return view('track', compact('complaint', 'ref'));
    }
}
