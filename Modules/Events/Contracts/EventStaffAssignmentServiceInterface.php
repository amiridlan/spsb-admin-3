<?php

namespace Modules\Events\Contracts;

use Illuminate\Database\Eloquent\Collection;

/**
 * Event Staff Assignment Service Interface
 *
 * Handles staff assignment to events and coordination with Staff module
 */
interface EventStaffAssignmentServiceInterface
{
    /**
     * Assign staff to an event
     *
     * @param int $eventId
     * @param int $staffId
     * @param string|null $role
     * @param string|null $notes
     * @return void
     * @throws \Exception if staff not available or already assigned
     */
    public function assignStaff(int $eventId, int $staffId, ?string $role = null, ?string $notes = null): void;

    /**
     * Remove staff from an event
     *
     * @param int $eventId
     * @param int $staffId
     * @return void
     */
    public function removeStaff(int $eventId, int $staffId): void;

    /**
     * Update staff assignment details
     *
     * @param int $eventId
     * @param int $staffId
     * @param array $data
     * @return void
     */
    public function updateAssignment(int $eventId, int $staffId, array $data): void;

    /**
     * Get assigned staff for an event
     *
     * @param int $eventId
     * @return Collection
     */
    public function getAssignedStaff(int $eventId): Collection;

    /**
     * Get available staff for an event
     *
     * @param int $eventId
     * @return array
     */
    public function getAvailableStaff(int $eventId): array;

    /**
     * Check if staff is assigned to event
     *
     * @param int $eventId
     * @param int $staffId
     * @return bool
     */
    public function isStaffAssigned(int $eventId, int $staffId): bool;

    /**
     * Assign multiple staff to an event
     *
     * @param int $eventId
     * @param array $staffAssignments
     * @return void
     */
    public function assignMultipleStaff(int $eventId, array $staffAssignments): void;

    /**
     * Remove all staff from an event
     *
     * @param int $eventId
     * @return void
     */
    public function removeAllStaff(int $eventId): void;
}
