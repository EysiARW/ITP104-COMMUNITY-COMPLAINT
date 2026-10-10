<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintCategory extends Model
{
    protected $table = 'complaint_categories';
    protected $primaryKey = 'category_id';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'category_id', 'category_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }
}
