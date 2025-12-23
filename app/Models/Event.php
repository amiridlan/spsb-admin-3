<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_space_id',
        'title',
        'description',
        'client_name',
        'client_email',
        'client_phone',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'status',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function eventSpace(): BelongsTo
    {
        return $this->belongsTo(EventSpace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // NEW: Staff relationship
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'event_staff')
            ->withPivot('role', 'notes')
            ->withTimestamps();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // NEW: Check if staff member is assigned
    public function hasStaff(int $staffId): bool
    {
        return $this->staff()->where('staff_id', $staffId)->exists();
    }

    // NEW: Assign staff to event
    public function assignStaff(int $staffId, ?string $role = null, ?string $notes = null): void
    {
        $this->staff()->attach($staffId, [
            'role' => $role,
            'notes' => $notes,
        ]);
    }

    // NEW: Remove staff from event
    public function removeStaff(int $staffId): void
    {
        $this->staff()->detach($staffId);
    }
}
