<?php

namespace Modules\Staff\Models;

use App\Models\User;
use Carbon\Carbon;
use Database\Factories\LeaveRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_HR_APPROVED = 'hr_approved';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return LeaveRequestFactory::new();
    }

    protected $fillable = [
        'staff_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'hr_reviewed_by',
        'hr_review_notes',
        'hr_reviewed_at',
        'head_reviewed_by',
        'head_review_notes',
        'head_reviewed_at',
        'conflict_events',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'hr_reviewed_at' => 'datetime',
            'head_reviewed_at' => 'datetime',
            'conflict_events' => 'array',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function hrReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_reviewed_by');
    }

    public function headReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_reviewed_by');
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if request is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if request is HR approved (pending head approval)
     */
    public function isHrApproved(): bool
    {
        return $this->status === 'hr_approved';
    }

    /**
     * Check if request is pending head approval (alias for isHrApproved)
     */
    public function isPendingHeadApproval(): bool
    {
        return $this->isHrApproved();
    }

    /**
     * Check if request has conflicts with events
     */
    public function hasConflicts(): bool
    {
        return !empty($this->conflict_events);
    }

    /**
     * Get leave type label
     */
    public function getLeaveTypeLabel(): string
    {
        return match($this->leave_type) {
            'annual' => 'Annual Leave',
            'sick' => 'Sick Leave',
            'emergency' => 'Emergency Leave',
            default => ucfirst($this->leave_type),
        };
    }

    /**
     * Scope: Get pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Get HR approved requests (pending head approval)
     */
    public function scopeHrApproved($query)
    {
        return $query->where('status', 'hr_approved');
    }

    /**
     * Scope: Get requests pending head approval (alias for hrApproved)
     */
    public function scopePendingHeadApproval($query)
    {
        return $query->where('status', 'hr_approved');
    }

    /**
     * Scope: Get requests for date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                       ->where('end_date', '>=', $endDate);
              });
        });
    }
}
