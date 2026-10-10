<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Audit
{
    /** Write a row to audit_logs. Usage: Audit::log('LOGIN', 'User logged in', 'users', $id); */
    public static function log(string $action, ?string $description = null, ?string $table = null, ?int $recordId = null): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'description' => $description,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
