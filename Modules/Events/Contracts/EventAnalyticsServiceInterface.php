<?php

namespace Modules\Events\Contracts;

/**
 * Event Analytics Service Interface
 *
 * Handles event metrics, statistics, trends, and reporting
 */
interface EventAnalyticsServiceInterface
{
    /**
     * Get overview statistics for a date range
     *
     * @param array $dateRange ['start' => Carbon, 'end' => Carbon]
     * @return array
     */
    public function getStatistics(array $dateRange = []): array;

    /**
     * Get booking trends over time
     *
     * @param array $dateRange
     * @return array
     */
    public function getBookingTrends(array $dateRange): array;

    /**
     * Get status distribution metrics
     *
     * @param array $dateRange
     * @return array
     */
    public function getStatusMetrics(array $dateRange): array;

    /**
     * Get time-based metrics (day of week, lead time, etc.)
     *
     * @param array $dateRange
     * @return array
     */
    public function getTimeMetrics(array $dateRange): array;

    /**
     * Get client metrics and analysis
     *
     * @param array $dateRange
     * @return array
     */
    public function getClientMetrics(array $dateRange): array;

    /**
     * Generate a report for events
     *
     * @param string $type
     * @param array $filters
     * @return array
     */
    public function generateReport(string $type, array $filters): array;

    /**
     * Get dashboard statistics
     *
     * @param string $role User role (admin, staff, etc.)
     * @param int|null $userId
     * @return array
     */
    public function getDashboardStats(string $role, ?int $userId = null): array;

    /**
     * Get pending actions for admin dashboard
     *
     * @return array
     */
    public function getPendingActions(): array;

    /**
     * Get events by month for charts
     *
     * @param int $months Number of months to look back
     * @return array
     */
    public function getEventsByMonth(int $months = 6): array;
}
