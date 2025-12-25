<?php

// config/performance.php

return [
    /*
    |--------------------------------------------------------------------------
    | Query Optimization
    |--------------------------------------------------------------------------
    */
    'query' => [
        // Enable query logging in development
        'log_queries' => env('LOG_QUERIES', false),

        // Warn on slow queries (milliseconds)
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000),

        // Maximum number of queries per request
        'max_queries_per_request' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Strategy
    |--------------------------------------------------------------------------
    */
    'cache' => [
        // Cache event spaces list (rarely changes)
        'event_spaces_ttl' => 3600, // 1 hour

        // Cache dashboard statistics
        'dashboard_stats_ttl' => 300, // 5 minutes

        // Cache calendar events
        'calendar_events_ttl' => 600, // 10 minutes

        // Cache metrics
        'metrics_ttl' => 900, // 15 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        // Log slow requests
        'log_slow_requests' => env('LOG_SLOW_REQUESTS', true),

        // Slow request threshold (seconds)
        'slow_request_threshold' => env('SLOW_REQUEST_THRESHOLD', 2.0),
    ],
];
