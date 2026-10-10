<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintAssignment extends Model
{
    protected $primaryKey = 'assignment_id';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime', 'is_current' => 'boolean'];
    }

    public function complaint() { return $this->belongsTo(Complaint::class, 'complaint_id', 'complaint_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to', 'user_id'); }
    public function assigner() { return $this->belongsTo(User::class, 'assigned_by', 'user_id'); }
}
