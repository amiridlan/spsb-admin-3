<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
