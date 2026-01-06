<?php

namespace Modules\Staff\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Staff\Contracts\StaffServiceInterface;
use Modules\Staff\Models\Staff;

class StaffService implements StaffServiceInterface
{
    public function getAll(array $filters = []): Collection
    {
        $query = Staff::query()->with('user');

        // Apply filters
        if (isset($filters['is_available'])) {
            $query->where('is_available', $filters['is_available']);
        }

        if (isset($filters['position'])) {
            $query->where('position', $filters['position']);
        }

        if (isset($filters['specialization'])) {
            $query->whereJsonContains('specializations', $filters['specialization']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getById(int $id): ?Staff
    {
        return Staff::with('user')->find($id);
    }

    public function getByUserId(int $userId): ?Staff
    {
        return Staff::with('user')->where('user_id', $userId)->first();
    }

    public function create(array $data): Staff
    {
        return Staff::create($data);
    }

    public function update(int $id, array $data): Staff
    {
        $staff = Staff::findOrFail($id);
        $staff->update($data);
        return $staff->fresh('user');
    }

    public function delete(int $id): bool
    {
        $staff = Staff::findOrFail($id);

        // Check if staff has future assignments
        $hasFutureAssignments = $staff->events()
            ->where('start_date', '>=', Carbon::today())
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($hasFutureAssignments) {
            throw new \Exception('Cannot delete staff member with future event assignments.');
        }

        return $staff->delete();
    }

    public function getAvailable(): Collection
    {
        return Staff::where('is_available', true)
            ->with('user')
            ->orderBy('created_at')
            ->get();
    }

    public function toggleAvailability(int $id): Staff
    {
        $staff = Staff::findOrFail($id);
        $staff->update(['is_available' => !$staff->is_available]);
        return $staff->fresh('user');
    }

    public function getByPosition(string $position): Collection
    {
        return Staff::where('position', $position)
            ->with('user')
            ->orderBy('created_at')
            ->get();
    }

    public function getBySpecialization(string $specialization): Collection
    {
        return Staff::whereJsonContains('specializations', $specialization)
            ->with('user')
            ->orderBy('created_at')
            ->get();
    }

    public function getAssignments(int $staffId, Carbon $startDate, Carbon $endDate): Collection
    {
        $staff = Staff::findOrFail($staffId);

        return $staff->events()
            ->with(['eventSpace', 'creator'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_date')
            ->get();
    }

    public function getUpcomingAssignments(int $staffId, int $limit = 10): Collection
    {
        $staff = Staff::findOrFail($staffId);

        return $staff->upcomingAssignments()
            ->limit($limit)
            ->get();
    }
}

