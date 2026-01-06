<?php

namespace Modules\Staff\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Events\Models\Event;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'position',
        'specializations',
        'is_available',
        'notes',
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
}
