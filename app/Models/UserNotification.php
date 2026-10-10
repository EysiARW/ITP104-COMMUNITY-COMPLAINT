<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_read' => 'boolean', 'created_at' => 'datetime'];
    }

    public function complaint() { return $this->belongsTo(Complaint::class, 'complaint_id', 'complaint_id'); }

    /** Create the same notification for many users at once. */
    public static function notifyMany(iterable $userIds, string $message, ?int $complaintId = null): void
    {
        $rows = [];
        foreach ($userIds as $id) {
            $rows[] = ['user_id' => $id, 'complaint_id' => $complaintId, 'message' => $message, 'created_at' => now()];
        }
        if ($rows) {
            static::insert($rows);
        }
    }
}
