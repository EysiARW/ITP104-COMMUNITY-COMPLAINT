<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $primaryKey = 'complaint_id';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['incident_date' => 'datetime'];
    }

    // URLs use the reference number (e.g. /complaints/BRGY-2026-00012), not the numeric id.
    public function getRouteKeyName()
    {
        return 'reference_no';
    }

    public function complainant() { return $this->belongsTo(User::class, 'complainant_id', 'user_id'); }
    public function reportType() { return $this->belongsTo(ReportType::class, 'report_type_id', 'report_type_id'); }
    public function category() { return $this->belongsTo(ComplaintCategory::class, 'category_id', 'category_id'); }
    public function priority() { return $this->belongsTo(PriorityLevel::class, 'priority_id', 'priority_id'); }
    public function area() { return $this->belongsTo(Area::class, 'area_id', 'area_id'); }
    public function status() { return $this->belongsTo(ComplaintStatus::class, 'status_id', 'status_id'); }
    public function attachments() { return $this->hasMany(Attachment::class, 'complaint_id', 'complaint_id'); }
    public function history() { return $this->hasMany(StatusHistory::class, 'complaint_id', 'complaint_id')->orderBy('changed_at')->orderBy('history_id'); }
    public function assignments() { return $this->hasMany(ComplaintAssignment::class, 'complaint_id', 'complaint_id'); }
    public function currentAssignment() { return $this->hasOne(ComplaintAssignment::class, 'complaint_id', 'complaint_id')->where('is_current', true)->latestOfMany('assignment_id'); }
    public function investigations() { return $this->hasMany(Investigation::class, 'complaint_id', 'complaint_id'); }
    public function resolution() { return $this->hasOne(Resolution::class, 'complaint_id', 'complaint_id'); }

    public function getMaskedComplainantAttribute(): string
    {
        if (!$this->complainant) {
            return 'Guest / Anonymous';
        }

        $first = trim((string) $this->complainant->first_name);
        $last = trim((string) $this->complainant->last_name);

        $maskWord = function (string $word): string {
            $len = mb_strlen($word);
            if ($len === 0) {
                return '***';
            }
            if ($len === 1) {
                return $word . '***';
            }
            return mb_substr($word, 0, 1) . str_repeat('*', min(4, max(2, $len - 1)));
        };

        $parts = [];
        if ($first !== '') {
            $parts[] = $maskWord($first);
        }
        if ($last !== '') {
            $parts[] = $maskWord($last);
        }

        return !empty($parts) ? implode(' ', $parts) : 'Resident';
    }
}
