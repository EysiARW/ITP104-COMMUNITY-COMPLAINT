<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'department_id';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function users()
    {
        return $this->hasMany(User::class, 'department_id', 'department_id');
    }

    public function officers()
    {
        return $this->hasMany(User::class, 'department_id', 'department_id')
            ->where('role_id', User::ROLE_ADMIN)
            ->where('is_active', true);
    }

    public function categories()
    {
        return $this->hasMany(ComplaintCategory::class, 'department_id', 'department_id');
    }
}
