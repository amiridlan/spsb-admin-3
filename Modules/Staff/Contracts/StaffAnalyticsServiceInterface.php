<?php

namespace Modules\Staff\Contracts;

/**
 * Staff Analytics Service Interface
 *
 * Handles staff metrics, workload analysis, and reporting
 */
interface StaffAnalyticsServiceInterface
{
    /**
     * Get staff metrics for a date range
     *
     * @param array $dateRange ['start' => Carbon, 'end' => Carbon]
     * @return array
     */
    public function getStaffMetrics(array $dateRange): array;

    /**
     * Get statistics for a specific staff member
     *
     * @param int $staffId
     * @param array $dateRange
     * @return array
     */
    public function getStaffStatistics(int $staffId, array $dateRange): array;

    /**
     * Get workload distribution across all staff
     *
     * @param array $dateRange
     * @return array
     */
    public function getWorkloadDistribution(array $dateRange): array;

    /**
     * Get dashboard statistics for staff
     *
     * @param int|null $staffId If null, returns overall stats
     * @return array
     */
    public function getStatistics(?int $staffId = null): array;

    /**
     * Generate a report for staff assignments
     *
     * @param array $filters
     * @return array
     */
    public function generateReport(array $filters): array;

    /**
     * Get top performing staff by assignment count
     *
     * @param array $dateRange
     * @param int $limit
     * @return array
     */
    public function getTopStaff(array $dateRange, int $limit = 10): array;

    /**
     * Get staff utilization rates
     *
     * @param array $dateRange
     * @return array
     */
    public function getUtilizationRates(array $dateRange): array;

    /**
     * Get dashboard stats for a specific staff member
     *
     * @param int $staffId
     * @return array
     */
    public function getStaffDashboardStats(int $staffId): array;
}
