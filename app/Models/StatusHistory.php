<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    protected $table = 'complaint_status_history';
    protected $primaryKey = 'history_id';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function newStatus() { return $this->belongsTo(ComplaintStatus::class, 'new_status_id', 'status_id'); }
    public function changedBy() { return $this->belongsTo(User::class, 'changed_by', 'user_id'); }
}
