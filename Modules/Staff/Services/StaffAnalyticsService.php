<?php

namespace Modules\Staff\Services;

use Carbon\Carbon;
use Modules\Staff\Contracts\StaffAnalyticsServiceInterface;
use Modules\Staff\Models\Staff;

class StaffAnalyticsService implements StaffAnalyticsServiceInterface
{
    public function getStaffMetrics(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $staff = Staff::with('user')->get();

        $data = $staff->map(function ($staffMember) use ($start, $end) {
            $assignments = $staffMember->events()
                ->whereBetween('start_date', [$start, $end])
                ->where('status', '!=', 'cancelled')
                ->get();

            $totalDays = $assignments->sum(
                fn($event) => $event->start_date->diffInDays($event->end_date) + 1
            );

            return [
                'id' => $staffMember->id,
                'name' => $staffMember->user->name,
                'position' => $staffMember->position,
                'assignment_count' => $assignments->count(),
                'total_days' => $totalDays,
            ];
        })->sortByDesc('assignment_count');

        return $data->values()->toArray();
    }

    public function getStaffStatistics(int $staffId, array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $staff = Staff::with('user')->findOrFail($staffId);

        $assignments = $staff->events()
            ->whereBetween('start_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->get();

        $totalDays = $assignments->sum(
            fn($event) => $event->start_date->diffInDays($event->end_date) + 1
        );

        return [
            'staff_id' => $staff->id,
            'staff_name' => $staff->user->name,
            'position' => $staff->position,
            'is_available' => $staff->is_available,
            'assignment_count' => $assignments->count(),
            'total_days' => $totalDays,
            'events_by_status' => $assignments->groupBy('status')->map->count()->toArray(),
        ];
    }

    public function getWorkloadDistribution(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $staff = Staff::with('user')->get();

        $distribution = $staff->map(function ($staffMember) use ($start, $end) {
            $assignmentCount = $staffMember->events()
                ->whereBetween('start_date', [$start, $end])
                ->where('status', '!=', 'cancelled')
                ->count();

            return [
                'staff_id' => $staffMember->id,
                'staff_name' => $staffMember->user->name,
                'assignment_count' => $assignmentCount,
                'is_available' => $staffMember->is_available,
            ];
        })->sortByDesc('assignment_count');

        return $distribution->values()->toArray();
    }

    public function getStatistics(?int $staffId = null): array
    {
        if ($staffId) {
            $staff = Staff::with('user')->findOrFail($staffId);

            return [
                'total_assignments' => $staff->events()->count(),
                'upcoming_assignments' => $staff->upcomingAssignments()->count(),
                'current_assignments' => $staff->currentAssignments()->count(),
                'completed_assignments' => $staff->pastAssignments()->where('status', 'completed')->count(),
            ];
        }

        // Overall staff statistics
        return [
            'total_staff' => Staff::count(),
            'available_staff' => Staff::where('is_available', true)->count(),
            'unavailable_staff' => Staff::where('is_available', false)->count(),
        ];
    }

    public function generateReport(array $filters): array
    {
        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];

        $staff = Staff::with('user')->get();

        $data = $staff->map(function ($staffMember) use ($startDate, $endDate, $filters) {
            $assignments = $staffMember->events()
                ->whereBetween('start_date', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->get();

            $totalDays = $assignments->sum(
                fn($event) => $event->start_date->diffInDays($event->end_date) + 1
            );

            return [
                'id' => $staffMember->id,
                'name' => $staffMember->user->name,
                'position' => $staffMember->position ?? '',
                'assignment_count' => $assignments->count(),
                'total_days' => $totalDays,
                'events_by_status' => $assignments->groupBy('status')->map->count(),
            ];
        })->sortByDesc('assignment_count');

        return [
            'type' => 'staff',
            'title' => 'Staff Assignments Report',
            'period' => sprintf('%s to %s', $startDate, $endDate),
            'total_count' => $staff->count(),
            'data' => $data->values()->toArray(),
            'summary' => [
                'total_staff' => $staff->count(),
                'total_assignments' => $data->sum('assignment_count'),
                'avg_assignments' => $staff->count() > 0 ? round($data->avg('assignment_count'), 1) : 0,
                'most_active' => $data->first()['name'] ?? 'N/A',
            ],
        ];
    }

    public function getTopStaff(array $dateRange, int $limit = 10): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $staff = Staff::with('user')
            ->get()
            ->map(function ($staffMember) use ($start, $end) {
                $assignmentCount = $staffMember->events()
                    ->whereBetween('start_date', [$start, $end])
                    ->where('status', '!=', 'cancelled')
                    ->count();

                return [
                    'id' => $staffMember->id,
                    'name' => $staffMember->user->name,
                    'position' => $staffMember->position,
                    'assignment_count' => $assignmentCount,
                ];
            })
            ->sortByDesc('assignment_count')
            ->take($limit);

        return $staff->values()->toArray();
    }

    public function getUtilizationRates(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $periodDays = $start->diffInDays($end) + 1;

        $staff = Staff::with('user')->get();

        $utilization = $staff->map(function ($staffMember) use ($start, $end, $periodDays) {
            $assignments = $staffMember->events()
                ->whereBetween('start_date', [$start, $end])
                ->where('status', '!=', 'cancelled')
                ->get();

            $totalDays = $assignments->sum(
                fn($event) => $event->start_date->diffInDays($event->end_date) + 1
            );

            $utilizationRate = $periodDays > 0
                ? round(($totalDays / $periodDays) * 100, 1)
                : 0;

            return [
                'staff_id' => $staffMember->id,
                'staff_name' => $staffMember->user->name,
                'position' => $staffMember->position,
                'total_days_assigned' => $totalDays,
                'utilization_rate' => $utilizationRate,
            ];
        })->sortByDesc('utilization_rate');

        return $utilization->values()->toArray();
    }

    public function getStaffDashboardStats(int $staffId): array
    {
        $staff = Staff::findOrFail($staffId);
        $today = Carbon::today();

        return [
            'total_assignments' => $staff->events()->count(),
            'upcoming_assignments' => $staff->upcomingAssignments()->count(),
            'current_assignments' => $staff->currentAssignments()->count(),
            'completed_assignments' => $staff->pastAssignments()->where('status', 'completed')->count(),
        ];
    }
}

