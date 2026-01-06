<?php

namespace Modules\Events\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Events\Contracts\EventSpaceServiceInterface;
use Modules\Events\Models\EventSpace;

class EventSpaceService implements EventSpaceServiceInterface
{
    public function getAll(bool $activeOnly = true): Collection
    {
        $query = EventSpace::query();

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('name')->get();
    }

    public function getById(int $id): ?EventSpace
    {
        return EventSpace::find($id);
    }

    public function getActive(): Collection
    {
        return EventSpace::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): EventSpace
    {
        return EventSpace::create($data);
    }

    public function update(int $id, array $data): EventSpace
    {
        $space = EventSpace::findOrFail($id);
        $space->update($data);
        return $space->fresh();
    }

    public function delete(int $id): bool
    {
        $space = EventSpace::findOrFail($id);

        // Check if space has events
        if ($space->events()->exists()) {
            throw new \Exception('Cannot delete event space with existing events. Deactivate it instead.');
        }

        return $space->delete();
    }

    public function toggleActive(int $id): EventSpace
    {
        $space = EventSpace::findOrFail($id);
        $space->update(['is_active' => !$space->is_active]);
        return $space->fresh();
    }

    public function getWithEventCounts(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $query = EventSpace::query();

        if ($startDate && $endDate) {
            $query->withCount([
                'events' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                        ->where('status', '!=', 'cancelled');
                }
            ]);
        } else {
            $query->withCount([
                'events' => function ($q) {
                    $q->where('status', '!=', 'cancelled');
                }
            ]);
        }

        return $query->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getSpaceUtilization(int $limit = 5): Collection
    {
        return EventSpace::withCount([
            'events' => fn($query) => $query->where('status', '!=', 'cancelled')
        ])
            ->where('is_active', true)
            ->orderBy('events_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getSpaceMetrics(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        $spaceStats = EventSpace::withCount([
            'events' => fn($query) => $query
                ->whereBetween('start_date', [$start, $end])
                ->where('status', '!=', 'cancelled')
        ])
            ->where('is_active', true)
            ->get()
            ->map(function ($space) use ($start, $end) {
                $events = $space->events()
                    ->whereBetween('start_date', [$start, $end])
                    ->where('status', '!=', 'cancelled')
                    ->get();

                $totalDays = $events->sum(
                    fn($event) => $event->start_date->diffInDays($event->end_date) + 1
                );

                $periodDays = $start->diffInDays($end) + 1;
                $utilization = $periodDays > 0
                    ? round(($totalDays / $periodDays) * 100, 1)
                    : 0;

                return [
                    'id' => $space->id,
                    'name' => $space->name,
                    'booking_count' => $space->events_count,
                    'total_days' => $totalDays,
                    'utilization_rate' => $utilization,
                ];
            })
            ->sortByDesc('booking_count')
            ->values();

        return $spaceStats->toArray();
    }

    public function getSpacesReport(array $filters): array
    {
        $spaces = EventSpace::withCount([
            'events' => function ($query) use ($filters) {
                $query->whereBetween('start_date', [$filters['start_date'], $filters['end_date']]);
                if (!($filters['include_cancelled'] ?? false)) {
                    $query->where('status', '!=', 'cancelled');
                }
            }
        ])->where('is_active', true)->get();

        $data = $spaces->map(function ($space) use ($filters) {
            $events = $space->events()
                ->whereBetween('start_date', [$filters['start_date'], $filters['end_date']])
                ->where('status', '!=', 'cancelled')
                ->get();

            $totalDays = $events->sum(
                fn($event) => $event->start_date->diffInDays($event->end_date) + 1
            );

            return [
                'id' => $space->id,
                'name' => $space->name,
                'location' => $space->location,
                'capacity' => $space->capacity,
                'booking_count' => $space->events_count,
                'total_days' => $totalDays,
                'avg_duration' => $space->events_count > 0 ? round($totalDays / $space->events_count, 1) : 0,
            ];
        })->sortByDesc('booking_count');

        return [
            'type' => 'spaces',
            'title' => 'Event Spaces Report',
            'period' => sprintf('%s to %s', $filters['start_date'], $filters['end_date']),
            'total_count' => $spaces->count(),
            'data' => $data->values()->toArray(),
            'summary' => [
                'total_spaces' => $spaces->count(),
                'total_bookings' => $data->sum('booking_count'),
                'most_booked' => $data->first()['name'] ?? 'N/A',
                'least_booked' => $data->last()['name'] ?? 'N/A',
            ],
        ];
    }
}
