<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventSpaceResource;
use App\Models\EventSpace;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Event Spaces', 'Browse available event spaces')]
class EventSpaceController extends Controller
{
    use ApiResponse;

    /**
     * List Event Spaces
     *
     * Get a list of all active event spaces available for booking.
     *
     * @unauthenticated
     */
    #[Response(['success' => true, 'data' => [['id' => 1, 'name' => 'Grand Ballroom', 'description' => 'Large event hall', 'capacity' => 500, 'location' => 'Building A', 'amenities' => ['WiFi', 'Sound System'], 'is_active' => true]]], 200, 'List of event spaces')]
    public function index(): JsonResponse
    {
        $spaces = EventSpace::where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->success(EventSpaceResource::collection($spaces));
    }

    /**
     * Get Event Space Details
     *
     * Get detailed information about a specific event space.
     *
     * @unauthenticated
     */
    #[UrlParam('eventSpace', 'integer', 'The ID of the event space', example: 1)]
    #[Response(['success' => true, 'data' => ['id' => 1, 'name' => 'Grand Ballroom', 'description' => 'Large event hall with modern facilities', 'capacity' => 500, 'location' => 'Building A', 'amenities' => ['WiFi', 'Sound System', 'Projector'], 'is_active' => true]], 200, 'Event space details')]
    #[Response(['success' => false, 'message' => 'Event space not available'], 404, 'Space not found or inactive')]
    public function show(EventSpace $eventSpace): JsonResponse
    {
        if (!$eventSpace->is_active) {
            return $this->error('Event space not available', 404);
        }

        return $this->success(new EventSpaceResource($eventSpace));
    }
}
