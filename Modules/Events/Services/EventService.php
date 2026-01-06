<?php

namespace Modules\Events\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Events\Contracts\EventServiceInterface;
use Modules\Events\Models\Event;

class EventService implements EventServiceInterface
{
    public function getAll(array $filters = []): Collection
    {
        $query = Event::query()->with(['eventSpace', 'creator', 'staff.user']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['event_space_id'])) {
            $query->where('event_space_id', $filters['event_space_id']);
        }

        if (isset($filters['start_date'])) {
            $query->where('start_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('end_date', '<=', $filters['end_date']);
        }

        if (isset($filters['exclude_cancelled']) && $filters['exclude_cancelled']) {
            $query->where('status', '!=', 'cancelled');
        }

        return $query->orderBy('start_date')->get();
    }

    public function getById(int $id): ?Event
    {
        return Event::with(['eventSpace', 'creator', 'staff.user'])->find($id);
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(int $id, array $data): Event
    {
        $event = Event::findOrFail($id);
        $event->update($data);
        return $event->fresh(['eventSpace', 'creator', 'staff.user']);
    }

    public function delete(int $id): bool
    {
        $event = Event::findOrFail($id);
        return $event->delete();
    }

    public function getByStatus(string $status): Collection
    {
        return Event::where('status', $status)
            ->with(['eventSpace', 'creator'])
            ->orderBy('start_date')
            ->get();
    }

    public function getByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return Event::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        })
            ->with(['eventSpace', 'creator'])
            ->orderBy('start_date')
            ->get();
    }

    public function getUpcoming(int $limit = 10): Collection
    {
        return Event::where('start_date', '>=', Carbon::today())
            ->where('status', '!=', 'cancelled')
            ->with(['eventSpace', 'creator'])
            ->orderBy('start_date')
            ->limit($limit)
            ->get();
    }

    public function getBySpace(int $spaceId): Collection
    {
        return Event::where('event_space_id', $spaceId)
            ->with(['eventSpace', 'creator', 'staff.user'])
            ->orderBy('start_date')
            ->get();
    }

    public function updateStatus(int $id, string $status): Event
    {
        $event = Event::findOrFail($id);
        $event->update(['status' => $status]);
        return $event->fresh(['eventSpace', 'creator']);
    }

    public function getCalendarEvents(array $filters = []): array
    {
        $query = Event::query()->with(['eventSpace', 'creator']);

        // Apply filters
        if (isset($filters['event_space_id']) || isset($filters['space'])) {
            $spaceId = $filters['event_space_id'] ?? $filters['space'];
            $query->where('event_space_id', $spaceId);
        }

        if (isset($filters['staff_id'])) {
            $query->whereHas('staff', function ($q) use ($filters) {
                $q->where('staff.id', $filters['staff_id']);
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } elseif (isset($filters['exclude_cancelled']) && $filters['exclude_cancelled']) {
            $query->where('status', '!=', 'cancelled');
        } elseif (!isset($filters['show_cancelled']) || !$filters['show_cancelled']) {
            // By default, exclude cancelled unless explicitly requested
            $query->where('status', '!=', 'cancelled');
        }

        // Get events
        $events = $query->orderBy('start_date')->get();

        // Format for calendar (FullCalendar format)
        return $events->map(function ($event) {
            $statusColors = $this->getStatusColors($event->status);

            $startDate = $event->start_date;
            $endDate = $event->end_date;

            // FullCalendar uses exclusive end dates for all-day events
            $exclusiveEndDate = $endDate->copy()->addDay();

            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $startDate->format('Y-m-d'),
                'end' => $exclusiveEndDate->format('Y-m-d'),
                'allDay' => true,
                'backgroundColor' => $statusColors['background'],
                'borderColor' => $statusColors['border'],
                'textColor' => $statusColors['text'],
                'extendedProps' => [
                    'status' => $event->status,
                    'space' => $event->eventSpace->name,
                    'space_id' => $event->eventSpace->id,
                    'client' => $event->client_name,
                    'description' => $event->description,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'duration_days' => $startDate->diffInDays($endDate) + 1,
                ],
            ];
        })->toArray();
    }

    /**
     * Get status color mapping
     */
    private function getStatusColors(string $status): array
    {
        $colors = [
            'pending' => [
                'background' => '#f59e0b',
                'border' => '#d97706',
                'text' => '#ffffff',
            ],
            'confirmed' => [
                'background' => '#10b981',
                'border' => '#059669',
                'text' => '#ffffff',
            ],
            'completed' => [
                'background' => '#6b7280',
                'border' => '#4b5563',
                'text' => '#ffffff',
            ],
            'cancelled' => [
                'background' => '#ef4444',
                'border' => '#dc2626',
                'text' => '#ffffff',
            ],
        ];

        return $colors[$status] ?? [
            'background' => '#3b82f6',
            'border' => '#2563eb',
            'text' => '#ffffff',
        ];
    }

    public function getRecentBookings(int $limit = 10): Collection
    {
        return Event::with(['eventSpace', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
