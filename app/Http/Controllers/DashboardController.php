<?php

namespace App\Http\Controllers;

use App\Models\Complaint;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $mine = Complaint::where('complainant_id', $user->user_id);
        $stats = [
            'total' => (clone $mine)->count(),
            'pending' => (clone $mine)->whereIn('status_id', [1, 2])->count(),
            'active' => (clone $mine)->whereIn('status_id', [3, 4])->count(),
            'resolved' => (clone $mine)->whereIn('status_id', [5, 6])->count(),
        ];
        $recent = (clone $mine)->with(['status', 'category'])->latest('created_at')->limit(5)->get();

        return view('complainant.dashboard', compact('stats', 'recent'));
    }
}
