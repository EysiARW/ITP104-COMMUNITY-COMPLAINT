<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintStatus extends Model
{
    protected $table = 'complaint_statuses';
    protected $primaryKey = 'status_id';
    public $timestamps = false;
    protected $guarded = [];
}
