<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Staff', 'View staff assignments for events')]
class BookingController extends Controller
{
    use ApiResponse;

    /**
     * Get Booking Staff
     *
     * Get the list of staff members assigned to a specific event/booking.
     * Returns limited staff information for privacy.
     *
     * @unauthenticated
     */
    #[UrlParam('event', 'integer', 'The ID of the event/booking', example: 1)]
    #[Response(['success' => true, 'message' => 'Staff retrieved successfully', 'data' => [['id' => 1, 'name' => 'Jane Doe', 'position' => 'Event Coordinator', 'role' => 'lead']]], 200, 'Staff list')]
    #[Response(['success' => false, 'message' => 'Booking not available'], 404, 'Booking cancelled or not found')]
    public function staff(Event $event): JsonResponse
    {
        // Check if event is accessible (not cancelled)
        if ($event->isCancelled()) {
            return $this->error('Booking not available', 404);
        }

        // Load staff with user relationship and pivot data
        $staff = $event->staff()->with('user')->get();

        // Map staff with assignment details but restrict sensitive data
        $staffData = $staff->map(function ($staffMember) {
            return [
                'id' => $staffMember->id,
                'name' => $staffMember->user->name,
                'position' => $staffMember->position,
                'role' => $staffMember->pivot->role,
                // Notes are excluded for privacy/security
                // Email and other contact info excluded
                // User ID excluded for security
            ];
        });

        return $this->success($staffData, 'Staff retrieved successfully');
    }
}
