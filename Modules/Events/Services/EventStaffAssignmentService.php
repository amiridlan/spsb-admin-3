<?php

namespace Modules\Events\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Events\Contracts\EventStaffAssignmentServiceInterface;
use Modules\Events\Models\Event;
use Modules\Staff\Contracts\StaffAvailabilityServiceInterface;

class EventStaffAssignmentService implements EventStaffAssignmentServiceInterface
{
    public function __construct(
        private StaffAvailabilityServiceInterface $staffAvailability
    ) {}

    public function assignStaff(int $eventId, int $staffId, ?string $role = null, ?string $notes = null): void
    {
        $event = Event::findOrFail($eventId);

        // Check if already assigned
        if ($event->hasStaff($staffId)) {
            throw new \Exception('This staff member is already assigned to this event.');
        }

        // Check availability using Staff module service
        if (!$this->staffAvailability->isAvailable($staffId, $event->start_date, $event->end_date)) {
            throw new \Exception('This staff member is not available for the selected dates.');
        }

        // Assign staff
        $event->assignStaff($staffId, $role, $notes);
    }

    public function removeStaff(int $eventId, int $staffId): void
    {
        $event = Event::findOrFail($eventId);
        $event->removeStaff($staffId);
    }

    public function updateAssignment(int $eventId, int $staffId, array $data): void
    {
        $event = Event::findOrFail($eventId);
        $event->staff()->updateExistingPivot($staffId, $data);
    }

    public function getAssignedStaff(int $eventId): Collection
    {
        $event = Event::with('staff.user')->findOrFail($eventId);
        return $event->staff;
    }

    public function getAvailableStaff(int $eventId): array
    {
        // Use Staff module's availability service
        return $this->staffAvailability->getStaffAvailabilityForEvent($eventId)->toArray();
    }

    public function isStaffAssigned(int $eventId, int $staffId): bool
    {
        $event = Event::findOrFail($eventId);
        return $event->hasStaff($staffId);
    }

    public function assignMultipleStaff(int $eventId, array $staffAssignments): void
    {
        foreach ($staffAssignments as $assignment) {
            $this->assignStaff(
                $eventId,
                $assignment['staff_id'],
                $assignment['role'] ?? null,
                $assignment['notes'] ?? null
            );
        }
    }

    public function removeAllStaff(int $eventId): void
    {
        $event = Event::findOrFail($eventId);
        $event->staff()->detach();
    }
}
