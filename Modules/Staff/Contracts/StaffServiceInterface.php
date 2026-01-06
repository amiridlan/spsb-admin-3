<?php

namespace Modules\Staff\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Staff\Models\Staff;

/**
 * Staff Service Interface
 *
 * Handles CRUD operations and business logic for staff members
 */
interface StaffServiceInterface
{
    /**
     * Get all staff members
     *
     * @param array $filters
     * @return Collection
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get staff member by ID
     *
     * @param int $id
     * @return Staff|null
     */
    public function getById(int $id): ?Staff;

    /**
     * Get staff member by user ID
     *
     * @param int $userId
     * @return Staff|null
     */
    public function getByUserId(int $userId): ?Staff;

    /**
     * Create a new staff member
     *
     * @param array $data
     * @return Staff
     */
    public function create(array $data): Staff;

    /**
     * Update an existing staff member
     *
     * @param int $id
     * @param array $data
     * @return Staff
     */
    public function update(int $id, array $data): Staff;

    /**
     * Delete a staff member
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get available staff members
     *
     * @return Collection
     */
    public function getAvailable(): Collection;

    /**
     * Toggle staff availability
     *
     * @param int $id
     * @return Staff
     */
    public function toggleAvailability(int $id): Staff;

    /**
     * Get staff members by position
     *
     * @param string $position
     * @return Collection
     */
    public function getByPosition(string $position): Collection;

    /**
     * Get staff members by specialization
     *
     * @param string $specialization
     * @return Collection
     */
    public function getBySpecialization(string $specialization): Collection;

    /**
     * Get staff assignments for a date range
     *
     * @param int $staffId
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return Collection
     */
    public function getAssignments(int $staffId, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): Collection;

    /**
     * Get upcoming assignments for a staff member
     *
     * @param int $staffId
     * @param int $limit
     * @return Collection
     */
    public function getUpcomingAssignments(int $staffId, int $limit = 10): Collection;
}
