<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isHeadOfDepartment(): bool
    {
        return $this->role === 'head_of_department';
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    // NEW: Staff profile relationship
    public function staffProfile(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    // NEW: Check if user has staff profile
    public function hasStaffProfile(): bool
    {
        return $this->staffProfile()->exists();
    }

    // Department where this user is the head
    public function headOfDepartment(): HasOne
    {
        return $this->hasOne(Department::class, 'head_user_id');
    }
}
