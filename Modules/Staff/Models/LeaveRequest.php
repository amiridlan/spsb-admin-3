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
     * Check if HR has reviewed (approved or rejected)
     */
    public function hasHrReviewed(): bool
    {
        return $this->hr_reviewed_by !== null;
    }

    /**
     * Check if Head has reviewed (approved or rejected)
     */
    public function hasHeadReviewed(): bool
    {
        return $this->head_reviewed_by !== null;
    }

    /**
     * Check if both HR and Head have approved
     */
    public function hasBothApprovals(): bool
    {
        return $this->status === self::STATUS_APPROVED
            && $this->hr_reviewed_by !== null
            && $this->head_reviewed_by !== null;
    }

    /**
     * Check if pending HR approval (pending status and no HR review)
     */
    public function isPendingHrApproval(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->hr_reviewed_by === null;
    }

    /**
     * Check if pending Head approval (pending status and no Head review)
     */
    public function isPendingHeadApproval(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->head_reviewed_by === null;
    }

    /**
     * Check if pending second approval (one reviewer approved, waiting for other)
     */
    public function isPendingSecondApproval(): bool
    {
        return $this->status === self::STATUS_PENDING
            && ($this->hr_reviewed_by !== null || $this->head_reviewed_by !== null);
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
     * Scope: Get requests pending HR approval (no HR review yet)
     */
    public function scopePendingHrApproval($query)
    {
        return $query->where('status', 'pending')->whereNull('hr_reviewed_by');
    }

    /**
     * Scope: Get requests pending head approval (no Head review yet)
     */
    public function scopePendingHeadApproval($query)
    {
        return $query->where('status', 'pending')->whereNull('head_reviewed_by');
    }

    /**
     * Scope: Get requests pending second approval (one approved, waiting for other)
     */
    public function scopePendingSecondApproval($query)
    {
        return $query->where('status', 'pending')
            ->where(function($q) {
                $q->whereNotNull('hr_reviewed_by')
                  ->orWhereNotNull('head_reviewed_by');
            });
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
