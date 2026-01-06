<?php

namespace Modules\Staff\Contracts;

use Illuminate\Support\Collection;
use Modules\Staff\Models\Staff;

/**
 * Staff Availability Service Interface
 *
 * Handles staff availability checking and conflict detection
 */
interface StaffAvailabilityServiceInterface
{
    /**
     * Check if a staff member is available for a date range
     *
     * @param Staff|int $staff
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return bool
     */
    public function isAvailable(Staff|int $staff, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): bool;

    /**
     * Check if staff has conflicting event assignments
     *
     * @param Staff|int $staff
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @param int|null $excludeEventId Event to exclude from conflict check
     * @return bool
     */
    public function hasConflictingAssignments(
        Staff|int $staff,
        \Carbon\Carbon $startDate,
        \Carbon\Carbon $endDate,
        ?int $excludeEventId = null
    ): bool;

    /**
     * Get all available staff for a date range
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return Collection
     */
    public function getAvailableStaff(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): Collection;

    /**
     * Get available staff filtered by specialization
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @param string $specialization
     * @return Collection
     */
    public function getAvailableStaffBySpecialization(
        \Carbon\Carbon $startDate,
        \Carbon\Carbon $endDate,
        string $specialization
    ): Collection;

    /**
     * Get staff member's assigned events for a date range
     *
     * @param Staff|int $staff
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return Collection
     */
    public function getAssignedEvents(Staff|int $staff, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): Collection;

    /**
     * Get availability summary for a staff member
     *
     * @param Staff|int $staff
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array
     */
    public function getAvailabilitySummary(Staff|int $staff, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array;

    /**
     * Get all staff availability for a specific event
     *
     * @param int $eventId
     * @return Collection
     */
    public function getStaffAvailabilityForEvent(int $eventId): Collection;

    /**
     * Suggest staff for an event based on specialization and availability
     *
     * @param int $eventId
     * @param string|null $requiredSpecialization
     * @return Collection
     */
    public function suggestStaffForEvent(int $eventId, ?string $requiredSpecialization = null): Collection;
}
