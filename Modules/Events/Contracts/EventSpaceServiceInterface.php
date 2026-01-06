<?php

namespace Modules\Events\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Events\Models\EventSpace;

/**
 * Event Space Service Interface
 *
 * Handles CRUD operations and business logic for event spaces
 */
interface EventSpaceServiceInterface
{
    /**
     * Get all event spaces
     *
     * @param bool $activeOnly
     * @return Collection
     */
    public function getAll(bool $activeOnly = true): Collection;

    /**
     * Get event space by ID
     *
     * @param int $id
     * @return EventSpace|null
     */
    public function getById(int $id): ?EventSpace;

    /**
     * Get active event spaces
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Create a new event space
     *
     * @param array $data
     * @return EventSpace
     */
    public function create(array $data): EventSpace;

    /**
     * Update an existing event space
     *
     * @param int $id
     * @param array $data
     * @return EventSpace
     */
    public function update(int $id, array $data): EventSpace;

    /**
     * Delete an event space
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Toggle event space active status
     *
     * @param int $id
     * @return EventSpace
     */
    public function toggleActive(int $id): EventSpace;

    /**
     * Get spaces with event counts
     *
     * @param \Carbon\Carbon|null $startDate
     * @param \Carbon\Carbon|null $endDate
     * @return Collection
     */
    public function getWithEventCounts(?\Carbon\Carbon $startDate = null, ?\Carbon\Carbon $endDate = null): Collection;

    /**
     * Get space utilization metrics
     *
     * @param int $limit Number of spaces to return
     * @return Collection
     */
    public function getSpaceUtilization(int $limit = 5): Collection;

    /**
     * Get detailed space metrics for a date range
     *
     * @param array $dateRange ['start' => Carbon, 'end' => Carbon]
     * @return array
     */
    public function getSpaceMetrics(array $dateRange): array;

    /**
     * Generate spaces report
     *
     * @param array $filters
     * @return array
     */
    public function getSpacesReport(array $filters): array;
}
