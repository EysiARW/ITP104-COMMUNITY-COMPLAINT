<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $primaryKey = 'attachment_id';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['uploaded_at' => 'datetime'];
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->file_type, 'image/');
    }

    public function complaint() { return $this->belongsTo(Complaint::class, 'complaint_id', 'complaint_id'); }
}
