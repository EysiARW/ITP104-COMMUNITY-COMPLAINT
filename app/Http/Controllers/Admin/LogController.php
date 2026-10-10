<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->orderByDesc('log_id')->paginate(20)->withQueryString();

        $actions = AuditLog::select('action')->distinct()->pluck('action');
        $users = User::orderBy('first_name')->get();

        return view('admin.logs.index', compact('logs', 'actions', 'users'));
    }
}
