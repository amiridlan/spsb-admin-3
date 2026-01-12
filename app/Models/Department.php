<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Staff\Models\Staff;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'head_user_id',
    ];

    /**
     * Get the staff members in this department
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Get the department head (User)
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }

    /**
     * Check if department has a head assigned
     */
    public function hasHead(): bool
    {
        return !is_null($this->head_user_id);
    }

    /**
     * Get count of staff in this department
     */
    public function getStaffCount(): int
    {
        return $this->staff()->count();
    }
}
