<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSpace;
use App\Models\Staff;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {}

    /**
     * Export events to CSV
     */
    public function events(Request $request)
    {
        $query = Event::with(['eventSpace', 'creator', 'staff'])
            ->orderBy('start_date', 'desc');

        // Apply filters if provided
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->space_id) {
            $query->where('event_space_id', $request->space_id);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        $events = $query->get();
        $csv = $this->exportService->exportEventsToCsv($events);
        $filename = $this->exportService->generateFilename('events');

        return Response::make($csv, 200, $this->exportService->getDownloadHeaders($filename));
    }

    /**
     * Export event spaces to CSV
     */
    public function spaces(Request $request)
    {
        $spaces = EventSpace::withCount('events')
            ->orderBy('name')
            ->get();

        $csv = $this->exportService->exportSpacesToCsv($spaces);
        $filename = $this->exportService->generateFilename('event_spaces');

        return Response::make($csv, 200, $this->exportService->getDownloadHeaders($filename));
    }

    /**
     * Export staff to CSV
     */
    public function staff(Request $request)
    {
        $staff = Staff::with('user')
            ->withCount('events')
            ->orderBy('id')
            ->get();

        $csv = $this->exportService->exportStaffToCsv($staff);
        $filename = $this->exportService->generateFilename('staff');

        return Response::make($csv, 200, $this->exportService->getDownloadHeaders($filename));
    }

    /**
     * Export calendar data to CSV
     */
    public function calendar(Request $request)
    {
        $start = $request->start ?? now()->startOfMonth()->format('Y-m-d');
        $end = $request->end ?? now()->endOfMonth()->format('Y-m-d');

        $events = Event::with(['eventSpace', 'creator'])
            ->whereBetween('start_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_date')
            ->get();

        $csv = $this->exportService->exportEventsToCsv($events);
        $filename = $this->exportService->generateFilename('calendar_' . $start . '_to_' . $end);

        return Response::make($csv, 200, $this->exportService->getDownloadHeaders($filename));
    }

    /**
     * Export data to JSON format
     */
    public function json(Request $request)
    {
        $type = $request->type ?? 'events';

        $data = match ($type) {
            'events' => Event::with(['eventSpace', 'creator'])->get(),
            'spaces' => EventSpace::withCount('events')->get(),
            'staff' => Staff::with('user')->get(),
            default => [],
        };

        $json = $this->exportService->toJson($data->toArray(), $type);
        $filename = $this->exportService->generateFilename($type, 'json');

        return Response::make($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
