<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Enable query logging if configured
        if (config('performance.query.log_queries')) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage() - $startMemory;

        // Log slow requests
        if (
            config('performance.monitoring.log_slow_requests') &&
            $executionTime > config('performance.monitoring.slow_request_threshold')
        ) {

            $this->logSlowRequest($request, $executionTime, $memoryUsed);
        }

        // Check query count
        if (config('performance.query.log_queries')) {
            $queryCount = count(DB::getQueryLog());

            if ($queryCount > config('performance.query.max_queries_per_request')) {
                Log::warning('High query count detected', [
                    'url' => $request->fullUrl(),
                    'query_count' => $queryCount,
                ]);
            }
        }

        return $response;
    }

    /**
     * Log slow request details
     */
    protected function logSlowRequest(Request $request, float $executionTime, int $memoryUsed): void
    {
        $data = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'execution_time' => round($executionTime, 3),
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'user_id' => $request->user()?->id,
        ];

        if (config('performance.query.log_queries')) {
            $queries = DB::getQueryLog();
            $data['query_count'] = count($queries);
            $data['slow_queries'] = collect($queries)
                ->filter(fn($query) => $query['time'] > config('performance.query.slow_query_threshold'))
                ->count();
        }

        Log::warning('Slow request detected', $data);
    }
}
