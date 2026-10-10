<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'area_id';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function users()
    {
        return $this->hasMany(User::class, 'area_id', 'area_id');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'area_id', 'area_id');
    }
}
