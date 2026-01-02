<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventSpace;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Event::query()
            ->with(['eventSpace', 'staff.user'])
            ->where('status', '!=', 'cancelled');

        if ($request->space_id) {
            $query->where('event_space_id', $request->space_id);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        $events = $query->orderBy('start_date')->get();

        return $this->success(EventResource::collection($events));
    }

    public function show(Event $event): JsonResponse
    {
        if ($event->isCancelled()) {
            return $this->error('Event not available', 404);
        }

        return $this->success(new EventResource($event->load(['eventSpace', 'staff.user'])));
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'event_space_id' => ['required', 'exists:event_spaces,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $conflicts = Event::where('event_space_id', $request->event_space_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();

        return $this->success([
            'available' => !$conflicts,
            'message' => $conflicts
                ? 'Space is not available for the selected dates'
                : 'Space is available',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_space_id' => ['required', 'exists:event_spaces,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
        ]);

        // Check availability
        $conflicts = Event::where('event_space_id', $validated['event_space_id'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                    });
            })
            ->exists();

        if ($conflicts) {
            return $this->error('Space is not available for the selected dates', 422);
        }

        $validated['status'] = 'pending';
        $validated['created_by'] = auth()->id() ?? 1; // Fallback for API users

        $event = Event::create($validated);

        return $this->success(
            new EventResource($event->load(['eventSpace', 'staff.user'])),
            'Booking request created successfully',
            201
        );
    }
}
