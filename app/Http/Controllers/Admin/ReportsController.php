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
use Modules\Events\Contracts\EventAnalyticsServiceInterface;
use Modules\Events\Contracts\EventSpaceServiceInterface;
use Modules\Staff\Contracts\StaffAnalyticsServiceInterface;

class ReportsController extends Controller
{
    public function __construct(
        protected EventAnalyticsServiceInterface $eventAnalytics,
        protected EventSpaceServiceInterface $eventSpaceService,
        protected StaffAnalyticsServiceInterface $staffAnalytics
    ) {}
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
            'bookings' => $this->eventAnalytics->generateReport('bookings', $filters),
            'spaces' => $this->eventSpaceService->getSpacesReport($filters),
            'staff' => $this->staffAnalytics->generateReport($filters),
            'financial' => $this->buildFinancialReport($filters),
            'custom' => $this->buildCustomReport($filters),
        };
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
        // Combines multiple report types using services
        return [
            'type' => 'custom',
            'title' => 'Custom Report',
            'period' => sprintf('%s to %s', $filters['start_date'], $filters['end_date']),
            'bookings' => $this->eventAnalytics->generateReport('bookings', $filters),
            'spaces' => $this->eventSpaceService->getSpacesReport($filters),
            'staff' => $this->staffAnalytics->generateReport($filters),
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
