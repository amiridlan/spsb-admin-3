<?php

namespace Modules\Staff\Models;

use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\StaffFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Events\Models\Event;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return StaffFactory::new();
    }

    protected $fillable = [
        'user_id',
        'department_id',
        'position',
        'specializations',
        'is_available',
        'notes',
        'annual_leave_total',
        'annual_leave_used',
        'sick_leave_total',
        'sick_leave_used',
        'emergency_leave_total',
        'emergency_leave_used',
        'leave_notes',
    ];

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'is_available' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_staff')
            ->withPivot('role', 'notes')
            ->withTimestamps();
    }

    public function isAvailable(): bool
    {
        return $this->is_available;
    }

    public function hasSpecialization(string $specialization): bool
    {
        return in_array($specialization, $this->specializations ?? []);
    }

    /**
     * Get upcoming assignments
     */
    public function upcomingAssignments()
    {
        return $this->events()
            ->where('status', '!=', 'cancelled')
            ->where('start_date', '>=', Carbon::today())
            ->orderBy('start_date')
            ->with(['eventSpace', 'creator']);
    }

    /**
     * Get past assignments
     */
    public function pastAssignments()
    {
        return $this->events()
            ->where('end_date', '<', Carbon::today())
            ->orderBy('start_date', 'desc')
            ->with(['eventSpace', 'creator']);
    }

    /**
     * Get current assignments (ongoing events)
     */
    public function currentAssignments()
    {
        $today = Carbon::today();

        return $this->events()
            ->where('status', '!=', 'cancelled')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->with(['eventSpace', 'creator']);
    }

    /**
     * Get remaining annual leave days
     */
    public function getAnnualLeaveRemainingAttribute(): int
    {
        return max(0, $this->annual_leave_total - $this->annual_leave_used);
    }

    /**
     * Get remaining sick leave days
     */
    public function getSickLeaveRemainingAttribute(): int
    {
        return max(0, $this->sick_leave_total - $this->sick_leave_used);
    }

    /**
     * Get remaining emergency leave days
     */
    public function getEmergencyLeaveRemainingAttribute(): int
    {
        return max(0, $this->emergency_leave_total - $this->emergency_leave_used);
    }

    /**
     * Get total remaining leave days across all types
     */
    public function getTotalLeaveRemainingAttribute(): int
    {
        return $this->annual_leave_remaining +
               $this->sick_leave_remaining +
               $this->emergency_leave_remaining;
    }

    /**
     * Get leave requests for this staff member
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
