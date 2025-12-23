<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    use ApiResponse;

    /**
     * Get staff assigned to a booking/event
     *
     * @param Event $event
     * @return JsonResponse
     */
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
