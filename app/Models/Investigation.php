<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investigation extends Model
{
    protected $primaryKey = 'investigation_id';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['investigation_date' => 'datetime', 'created_at' => 'datetime'];
    }

    public function complaint() { return $this->belongsTo(Complaint::class, 'complaint_id', 'complaint_id'); }
    public function investigator() { return $this->belongsTo(User::class, 'investigator_id', 'user_id'); }
}
