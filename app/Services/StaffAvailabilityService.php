<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StaffAvailabilityService
{
    /**
     * Check if a staff member is available for a given date range
     */
    public function isAvailable(Staff $staff, Carbon $startDate, Carbon $endDate): bool
    {
        // Check if staff is marked as available
        if (!$staff->is_available) {
            return false;
        }

        // Check for conflicting event assignments
        return !$this->hasConflictingAssignments($staff, $startDate, $endDate);
    }

    /**
     * Check if staff has conflicting event assignments
     */
    public function hasConflictingAssignments(Staff $staff, Carbon $startDate, Carbon $endDate): bool
    {
        return $staff->events()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                // Check for date overlap
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
    }

    /**
     * Get all available staff for a given date range
     */
    public function getAvailableStaff(Carbon $startDate, Carbon $endDate): Collection
    {
        return Staff::where('is_available', true)
            ->with('user')
            ->get()
            ->filter(function ($staff) use ($startDate, $endDate) {
                return $this->isAvailable($staff, $startDate, $endDate);
            });
    }

    /**
     * Get available staff filtered by specialization
     */
    public function getAvailableStaffBySpecialization(
        Carbon $startDate,
        Carbon $endDate,
        string $specialization
    ): Collection {
        return $this->getAvailableStaff($startDate, $endDate)
            ->filter(function ($staff) use ($specialization) {
                return $staff->hasSpecialization($specialization);
            });
    }

    /**
     * Get staff member's assigned events for a date range
     */
    public function getAssignedEvents(Staff $staff, Carbon $startDate, Carbon $endDate): Collection
    {
        return $staff->events()
            ->with(['eventSpace', 'creator'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->orderBy('start_date')
            ->get();
    }

    /**
     * Get availability summary for a staff member
     */
    public function getAvailabilitySummary(Staff $staff, Carbon $startDate, Carbon $endDate): array
    {
        $assignedEvents = $this->getAssignedEvents($staff, $startDate, $endDate);

        return [
            'staff_id' => $staff->id,
            'staff_name' => $staff->user->name,
            'is_available' => $staff->is_available,
            'has_conflicts' => $this->hasConflictingAssignments($staff, $startDate, $endDate),
            'assigned_events_count' => $assignedEvents->count(),
            'assigned_events' => $assignedEvents->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start_date' => $event->start_date->toDateString(),
                    'end_date' => $event->end_date->toDateString(),
                    'status' => $event->status,
                ];
            }),
        ];
    }

    /**
     * Get all staff availability for a specific event
     */
    public function getStaffAvailabilityForEvent(Event $event): Collection
    {
        return Staff::where('is_available', true)
            ->with('user')
            ->get()
            ->map(function ($staff) use ($event) {
                return [
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->user->name,
                    'position' => $staff->position,
                    'specializations' => $staff->specializations,
                    'is_available' => $this->isAvailable(
                        $staff,
                        $event->start_date,
                        $event->end_date
                    ),
                    'is_assigned' => $event->hasStaff($staff->id),
                ];
            });
    }

    /**
     * Suggest staff for an event based on specialization and availability
     */
    public function suggestStaffForEvent(Event $event, ?string $requiredSpecialization = null): Collection
    {
        $availableStaff = $this->getAvailableStaff($event->start_date, $event->end_date);

        if ($requiredSpecialization) {
            $availableStaff = $availableStaff->filter(function ($staff) use ($requiredSpecialization) {
                return $staff->hasSpecialization($requiredSpecialization);
            });
        }

        return $availableStaff->map(function ($staff) {
            return [
                'id' => $staff->id,
                'name' => $staff->user->name,
                'email' => $staff->user->email,
                'position' => $staff->position,
                'specializations' => $staff->specializations,
            ];
        })->values();
    }
}
