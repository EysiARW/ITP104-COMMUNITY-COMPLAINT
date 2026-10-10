<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    protected $table = 'report_types';
    protected $primaryKey = 'report_type_id';
    public $timestamps = false;
    protected $guarded = [];
}
