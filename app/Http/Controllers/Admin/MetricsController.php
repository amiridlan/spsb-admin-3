<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSpace;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Events\Contracts\EventAnalyticsServiceInterface;
use Modules\Events\Contracts\EventSpaceServiceInterface;
use Modules\Staff\Contracts\StaffAnalyticsServiceInterface;

class MetricsController extends Controller
{
    public function __construct(
        protected EventAnalyticsServiceInterface $eventAnalytics,
        protected EventSpaceServiceInterface $eventSpaceService,
        protected StaffAnalyticsServiceInterface $staffAnalytics
    ) {}
    /**
     * Display booking metrics and statistics
     */
    public function index(Request $request): Response
    {
        $dateRange = $this->getDateRange($request);

        return Inertia::render('admin/metrics/Index', [
            'dateRange' => [
                'start' => $dateRange['start']->format('Y-m-d'),
                'end' => $dateRange['end']->format('Y-m-d'),
            ],
            'overview' => $this->eventAnalytics->getStatistics($dateRange),
            'bookingTrends' => $this->eventAnalytics->getBookingTrends($dateRange),
            'spaceMetrics' => $this->eventSpaceService->getSpaceMetrics($dateRange),
            'statusMetrics' => $this->eventAnalytics->getStatusMetrics($dateRange),
            'staffMetrics' => $this->staffAnalytics->getStaffMetrics($dateRange),
            'timeMetrics' => $this->eventAnalytics->getTimeMetrics($dateRange),
            'clientMetrics' => $this->eventAnalytics->getClientMetrics($dateRange),
        ]);
    }

    /**
     * Get date range from request or default to current month
     */
    protected function getDateRange(Request $request): array
    {
        $start = $request->start
            ? Carbon::parse($request->start)
            : Carbon::now()->startOfMonth();

        $end = $request->end
            ? Carbon::parse($request->end)
            : Carbon::now()->endOfMonth();

        return ['start' => $start, 'end' => $end];
    }
}
