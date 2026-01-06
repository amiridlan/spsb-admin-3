<?php

namespace Modules\Events\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Events\Models\Event;

/**
 * Event Service Interface
 *
 * Handles CRUD operations and business logic for events
 */
interface EventServiceInterface
{
    /**
     * Get all events with optional filters
     *
     * @param array $filters
     * @return Collection
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get event by ID
     *
     * @param int $id
     * @return Event|null
     */
    public function getById(int $id): ?Event;

    /**
     * Create a new event
     *
     * @param array $data
     * @return Event
     */
    public function create(array $data): Event;

    /**
     * Update an existing event
     *
     * @param int $id
     * @param array $data
     * @return Event
     */
    public function update(int $id, array $data): Event;

    /**
     * Delete an event
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get events by status
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get events by date range
     *
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return Collection
     */
    public function getByDateRange(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): Collection;

    /**
     * Get upcoming events
     *
     * @param int $limit
     * @return Collection
     */
    public function getUpcoming(int $limit = 10): Collection;

    /**
     * Get events for a specific space
     *
     * @param int $spaceId
     * @return Collection
     */
    public function getBySpace(int $spaceId): Collection;

    /**
     * Update event status
     *
     * @param int $id
     * @param string $status
     * @return Event
     */
    public function updateStatus(int $id, string $status): Event;

    /**
     * Get calendar events with formatting
     *
     * @param array $filters
     * @return array
     */
    public function getCalendarEvents(array $filters = []): array;

    /**
     * Get recent bookings
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecentBookings(int $limit = 10): Collection;
}
