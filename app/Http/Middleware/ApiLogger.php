<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLogger
{
    /**
     * Handle an incoming request.
     *
     * Log API requests and responses for monitoring and debugging.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $this->logRequest($request, $response, $startTime);

        return $response;
    }

    /**
     * Log the API request details.
     */
    protected function logRequest(Request $request, Response $response, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $statusCode = $response->getStatusCode();

        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'user_agent' => $request->userAgent(),
            'status' => $statusCode,
            'duration_ms' => $duration,
        ];

        // Only log request body for non-GET requests (excluding sensitive data)
        if (!$request->isMethod('GET')) {
            $logData['request_body'] = $this->sanitizeRequestData($request->all());
        }

        // Determine log level based on status code
        $logLevel = $this->getLogLevel($statusCode);

        // Add response info for errors
        if ($statusCode >= 400) {
            $logData['response'] = $this->getResponseContent($response);
        }

        Log::channel('api')->log($logLevel, 'API Request', $logData);
    }

    /**
     * Sanitize request data by removing sensitive fields.
     */
    protected function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_token', 'secret'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Get the appropriate log level based on status code.
     */
    protected function getLogLevel(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'error';
        }

        if ($statusCode >= 400) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Get response content for logging.
     */
    protected function getResponseContent(Response $response): ?array
    {
        $content = $response->getContent();

        if (empty($content)) {
            return null;
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['raw' => substr($content, 0, 500)];
        }

        return $decoded;
    }
}
