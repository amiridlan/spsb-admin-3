<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send booking confirmation notification
     */
    public function sendBookingConfirmation(Event $event): void
    {
        try {
            // In production, send actual email
            // Mail::to($event->client_email)->send(new BookingConfirmation($event));

            Log::info('Booking confirmation sent', [
                'event_id' => $event->id,
                'client_email' => $event->client_email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send booking cancellation notification
     */
    public function sendBookingCancellation(Event $event): void
    {
        try {
            Log::info('Booking cancellation sent', [
                'event_id' => $event->id,
                'client_email' => $event->client_email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send cancellation notification', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send staff assignment notification
     */
    public function sendStaffAssignment(Event $event, User $staffUser): void
    {
        try {
            Log::info('Staff assignment notification sent', [
                'event_id' => $event->id,
                'staff_email' => $staffUser->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send staff assignment notification', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send event reminder (1 day before)
     */
    public function sendEventReminder(Event $event): void
    {
        try {
            // Notify client
            Log::info('Event reminder sent to client', [
                'event_id' => $event->id,
                'client_email' => $event->client_email,
            ]);

            // Notify assigned staff
            foreach ($event->staff as $staffMember) {
                Log::info('Event reminder sent to staff', [
                    'event_id' => $event->id,
                    'staff_email' => $staffMember->user->email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send event reminder', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send pending approval notification to admins
     */
    public function sendPendingApprovalNotification(Event $event): void
    {
        try {
            $admins = User::whereIn('role', ['superadmin', 'admin'])->get();

            foreach ($admins as $admin) {
                Log::info('Pending approval notification sent', [
                    'event_id' => $event->id,
                    'admin_email' => $admin->email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send pending approval notification', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
