<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resolution extends Model
{
    protected $primaryKey = 'resolution_id';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function complaint() { return $this->belongsTo(Complaint::class, 'complaint_id', 'complaint_id'); }
    public function resolver() { return $this->belongsTo(User::class, 'resolved_by', 'user_id'); }
}
