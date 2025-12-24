<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSpace;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ReportsController extends Controller
{
    /**
     * Display reports page
     */
    public function index(): InertiaResponse
    {
        $spaces = EventSpace::where('is_active', true)->get();
        $staff = Staff::with('user')->get();

        return Inertia::render('admin/reports/Index', [
            'spaces' => $spaces,
            'staff' => $staff,
        ]);
    }

    /**
     * Generate and preview report
     */
    public function generate(Request $request): InertiaResponse
    {
        $validated = $request->validate([
            'report_type' => ['required', 'in:bookings,spaces,staff,financial,custom'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'space_id' => ['nullable', 'exists:event_spaces,id'],
            'staff_id' => ['nullable', 'exists:staff,id'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled'],
            'include_cancelled' => ['nullable', 'boolean'],
        ]);

        $reportData = $this->buildReport($validated);

        return Inertia::render('admin/reports/Preview', [
            'report' => $reportData,
            'filters' => $validated,
        ]);
    }

    /**
     * Export report as CSV
     */
    public function exportCsv(Request $request)
    {
        $validated = $request->validate([
            'report_type' => ['required', 'in:bookings,spaces,staff,financial,custom'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'space_id' => ['nullable', 'exists:event_spaces,id'],
            'staff_id' => ['nullable', 'exists:staff,id'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled'],
            'include_cancelled' => ['nullable', 'boolean'],
        ]);

        $reportData = $this->buildReport($validated);
        $csv = $this->generateCsv($reportData);

        $filename = sprintf(
            '%s_report_%s_to_%s.csv',
            $validated['report_type'],
            $validated['start_date'],
            $validated['end_date']
        );

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export report as PDF
     */
    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'report_type' => ['required', 'in:bookings,spaces,staff,financial,custom'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'space_id' => ['nullable', 'exists:event_spaces,id'],
            'staff_id' => ['nullable', 'exists:staff,id'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled'],
            'include_cancelled' => ['nullable', 'boolean'],
        ]);

        $reportData = $this->buildReport($validated);

        // For now, return JSON with note about PDF generation
        // In production, you'd use a package like dompdf or wkhtmltopdf
        return response()->json([
            'message' => 'PDF generation coming soon. Use CSV export for now.',
            'data' => $reportData,
        ]);
    }

    /**
     * Build report data based on type and filters
     */
    protected function buildReport(array $filters): array
    {
        return match ($filters['report_type']) {
            'bookings' => $this->buildBookingsReport($filters),
            'spaces' => $this->buildSpacesReport($filters),
            'staff' => $this->buildStaffReport($filters),
            'financial' => $this->buildFinancialReport($filters),
            'custom' => $this->buildCustomReport($filters),
        };
    }

    /**
     * Build bookings report
     */
    protected function buildBookingsReport(array $filters): array
    {
        $query = Event::with(['eventSpace', 'creator', 'staff.user'])
            ->whereBetween('start_date', [$filters['start_date'], $filters['end_date']]);

        // Apply filters
        if (isset($filters['space_id'])) {
            $query->where('event_space_id', $filters['space_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } elseif (!($filters['include_cancelled'] ?? false)) {
            $query->where('status', '!=', 'cancelled');
        }

        $events = $query->orderBy('start_date')->get();

        $data = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'space' => $event->eventSpace->name,
                'client_name' => $event->client_name,
                'client_email' => $event->client_email,
                'client_phone' => $event->client_phone,
                'start_date' => $event->start_date->format('Y-m-d'),
                'end_date' => $event->end_date->format('Y-m-d'),
                'duration' => $event->start_date->diffInDays($event->end_date) + 1,
                'status' => $event->status,
                'staff_count' => $event->staff->count(),
                'created_by' => $event->creator->name,
                'created_at' => $event->created_at->format('Y-m-d H:i'),
            ];
        });

        return [
            'type' => 'bookings',
            'title' => 'Bookings Report',
            'period' => sprintf('%s to %s', $filters['start_date'], $filters['end_date']),
            'total_count' => $data->count(),
            'data' => $data->toArray(),
            'summary' => [
                'total_bookings' => $data->count(),
                'total_days' => $data->sum('duration'),
                'avg_duration' => $data->count() > 0 ? round($data->avg('duration'), 1) : 0,
                'by_status' => $events->groupBy('status')->map->count(),
            ],
        ];
    }

    /**
     * Build spaces report
     */
    protected function buildSpacesReport(array $filters): array
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
                fn($event) =>
                $event->start_date->diffInDays($event->end_date) + 1
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

    /**
     * Build staff report
     */
    protected function buildStaffReport(array $filters): array
    {
        $staff = Staff::with('user')->get();

        $data = $staff->map(function ($staffMember) use ($filters) {
            $assignments = $staffMember->events()
                ->whereBetween('start_date', [$filters['start_date'], $filters['end_date']])
                ->where('status', '!=', 'cancelled')
                ->get();

            $totalDays = $assignments->sum(
                fn($event) =>
                $event->start_date->diffInDays($event->end_date) + 1
            );

            return [
                'id' => $staffMember->id,
                'name' => $staffMember->user->name,
                'position' => $staffMember->position,
                'assignment_count' => $assignments->count(),
                'total_days' => $totalDays,
                'events_by_status' => $assignments->groupBy('status')->map->count(),
            ];
        })->sortByDesc('assignment_count');

        return [
            'type' => 'staff',
            'title' => 'Staff Assignments Report',
            'period' => sprintf('%s to %s', $filters['start_date'], $filters['end_date']),
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

    /**
     * Build financial report (placeholder)
     */
    protected function buildFinancialReport(array $filters): array
    {
        // Placeholder for financial reporting
        // This would include revenue, costs, etc.
        return [
            'type' => 'financial',
            'title' => 'Financial Report',
            'period' => sprintf('%s to %s', $filters['start_date'], $filters['end_date']),
            'data' => [],
            'summary' => [
                'note' => 'Financial reporting requires revenue tracking implementation',
            ],
        ];
    }

    /**
     * Build custom report
     */
    protected function buildCustomReport(array $filters): array
    {
        // Combines multiple report types
        return [
            'type' => 'custom',
            'title' => 'Custom Report',
            'period' => sprintf('%s to %s', $filters['start_date'], $filters['end_date']),
            'bookings' => $this->buildBookingsReport($filters),
            'spaces' => $this->buildSpacesReport($filters),
            'staff' => $this->buildStaffReport($filters),
        ];
    }

    /**
     * Generate CSV from report data
     */
    protected function generateCsv(array $reportData): string
    {
        $output = fopen('php://temp', 'r+');

        // Write header
        fputcsv($output, [
            'Report Type: ' . $reportData['title'],
            'Period: ' . $reportData['period'],
            'Generated: ' . now()->format('Y-m-d H:i:s'),
        ]);
        fputcsv($output, []); // Empty line

        // Write data based on report type
        if ($reportData['type'] === 'bookings') {
            fputcsv($output, ['ID', 'Title', 'Space', 'Client', 'Email', 'Phone', 'Start Date', 'End Date', 'Duration', 'Status', 'Staff Count', 'Created By']);
            foreach ($reportData['data'] as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['title'],
                    $row['space'],
                    $row['client_name'],
                    $row['client_email'],
                    $row['client_phone'] ?? '',
                    $row['start_date'],
                    $row['end_date'],
                    $row['duration'],
                    $row['status'],
                    $row['staff_count'],
                    $row['created_by'],
                ]);
            }
        } elseif ($reportData['type'] === 'spaces') {
            fputcsv($output, ['ID', 'Name', 'Location', 'Capacity', 'Bookings', 'Total Days', 'Avg Duration']);
            foreach ($reportData['data'] as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['location'],
                    $row['capacity'] ?? '',
                    $row['booking_count'],
                    $row['total_days'],
                    $row['avg_duration'],
                ]);
            }
        } elseif ($reportData['type'] === 'staff') {
            fputcsv($output, ['ID', 'Name', 'Position', 'Assignments', 'Total Days']);
            foreach ($reportData['data'] as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['position'] ?? '',
                    $row['assignment_count'],
                    $row['total_days'],
                ]);
            }
        }

        // Write summary
        fputcsv($output, []); // Empty line
        fputcsv($output, ['Summary']);
        foreach ($reportData['summary'] as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    fputcsv($output, [ucwords(str_replace('_', ' ', $key)) . ' - ' . ucwords($subKey), $subValue]);
                }
            } else {
                fputcsv($output, [ucwords(str_replace('_', ' ', $key)), $value]);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
