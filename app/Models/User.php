<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\RoutesNotifications;

class User extends Authenticatable
{
    use HasFactory, RoutesNotifications; // lets the password-reset email be sent (we don't use Laravel's notifications table)

    public const ROLE_ADMIN = 1;
    public const ROLE_COMPLAINANT = 2;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'role_id', 'department_id', 'committee_position', 'area_id', 'first_name', 'middle_name', 'last_name',
        'email', 'password_hash', 'contact_number', 'address', 'is_active',
        'verification_doc_path', 'verification_doc_type', 'verification_doc_name',
        'verification_status', 'rejection_reason', 'verified_at', 'verified_by',
    ];

    protected $hidden = ['password_hash', 'remember_token'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    // Our password column is `password_hash`, not `password`.
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function isAdmin(): bool
    {
        return (int) $this->role_id === self::ROLE_ADMIN;
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getInitialsAttribute(): string
    {
        $first = (string) $this->first_name;
        $last = (string) $this->last_name;
        $in = strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));
        return $in !== '' ? $in : 'U';
    }

    public function role() { return $this->belongsTo(Role::class, 'role_id', 'role_id'); }
    public function area() { return $this->belongsTo(Area::class, 'area_id', 'area_id'); }
    public function department() { return $this->belongsTo(Department::class, 'department_id', 'department_id'); }
    public function complaints() { return $this->hasMany(Complaint::class, 'complainant_id', 'user_id'); }
    public function activeAssignments() { return $this->hasMany(ComplaintAssignment::class, 'assigned_to', 'user_id')->where('is_current', true); }
    public function notifications() { return $this->hasMany(UserNotification::class, 'user_id', 'user_id'); }
    public function verifier() { return $this->belongsTo(User::class, 'verified_by', 'user_id'); }

    public function isPendingVerification(): bool
    {
        return $this->verification_status === self::STATUS_PENDING;
    }

    public function isVerificationRejected(): bool
    {
        return $this->verification_status === self::STATUS_REJECTED;
    }
}
