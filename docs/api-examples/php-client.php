<?php
/**
 * Event Space Booking System API Client (PHP)
 *
 * Example PHP client for the Event Space Booking System API.
 * Requires PHP 8.0+ with cURL extension.
 *
 * Usage:
 *   $client = new EventSpaceApiClient('https://your-app.com', 'your-api-token');
 *   $spaces = $client->getEventSpaces();
 */

class EventSpaceApiClient
{
    private string $baseUrl;
    private ?string $token;
    private array $defaultHeaders;

    public function __construct(string $baseUrl, ?string $token = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/') . '/api/v1';
        $this->token = $token;
        $this->defaultHeaders = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];
    }

    /**
     * Set the API token for authenticated requests
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Login and receive an API token
     */
    public function login(string $email, string $password): array
    {
        $response = $this->request('POST', '/login', [
            'email' => $email,
            'password' => $password,
        ], false);

        if ($response['success'] && isset($response['data']['token'])) {
            $this->token = $response['data']['token'];
        }

        return $response;
    }

    /**
     * Logout and revoke the current token
     */
    public function logout(): array
    {
        $response = $this->request('POST', '/logout', [], true);
        $this->token = null;
        return $response;
    }

    /**
     * Get the current user's profile
     */
    public function getCurrentUser(): array
    {
        return $this->request('GET', '/user', [], true);
    }

    /**
     * Get all active event spaces
     */
    public function getEventSpaces(): array
    {
        return $this->request('GET', '/event-spaces');
    }

    /**
     * Get a specific event space by ID
     */
    public function getEventSpace(int $id): array
    {
        return $this->request('GET', "/event-spaces/{$id}");
    }

    /**
     * Get all events with optional filters
     */
    public function getEvents(?int $spaceId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $params = array_filter([
            'space_id' => $spaceId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $queryString = http_build_query($params);
        $endpoint = '/events' . ($queryString ? "?{$queryString}" : '');

        return $this->request('GET', $endpoint);
    }

    /**
     * Get a specific event by ID
     */
    public function getEvent(int $id): array
    {
        return $this->request('GET', "/events/{$id}");
    }

    /**
     * Check availability for a date range in a specific space
     */
    public function checkAvailability(int $spaceId, string $startDate, string $endDate): array
    {
        return $this->request('POST', '/events/check-availability', [
            'event_space_id' => $spaceId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Create a new booking (requires authentication)
     */
    public function createBooking(array $data): array
    {
        return $this->request('POST', '/bookings', $data, true);
    }

    /**
     * Get calendar events in FullCalendar format
     */
    public function getCalendarEvents(?string $start = null, ?string $end = null, ?int $spaceId = null, ?string $status = null): array
    {
        $params = array_filter([
            'start' => $start,
            'end' => $end,
            'space_id' => $spaceId,
            'status' => $status,
        ]);

        $queryString = http_build_query($params);
        $endpoint = '/events/calendar' . ($queryString ? "?{$queryString}" : '');

        return $this->request('GET', $endpoint);
    }

    /**
     * Get calendar events for a specific month
     */
    public function getMonthlyCalendar(int $year, int $month, ?int $spaceId = null): array
    {
        $params = array_filter([
            'year' => $year,
            'month' => $month,
            'space_id' => $spaceId,
        ]);

        $queryString = http_build_query($params);
        return $this->request('GET', "/events/calendar/month?{$queryString}");
    }

    /**
     * Get staff assigned to a booking
     */
    public function getBookingStaff(int $eventId): array
    {
        return $this->request('GET', "/bookings/{$eventId}/staff");
    }

    /**
     * Make an HTTP request to the API
     */
    private function request(string $method, string $endpoint, array $data = [], bool $requiresAuth = false): array
    {
        $url = $this->baseUrl . $endpoint;

        $headers = $this->defaultHeaders;
        if ($requiresAuth && $this->token) {
            $headers[] = "Authorization: Bearer {$this->token}";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: {$error}");
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }

        return $decoded;
    }
}

// Example usage:
/*
$client = new EventSpaceApiClient('https://your-app.com');

// List event spaces (no auth required)
$spaces = $client->getEventSpaces();
print_r($spaces);

// Check availability (no auth required)
$availability = $client->checkAvailability(1, '2025-06-15', '2025-06-16');
print_r($availability);

// Login to get token
$loginResponse = $client->login('user@example.com', 'password123');

// Create a booking (auth required)
$booking = $client->createBooking([
    'event_space_id' => 1,
    'title' => 'Corporate Meeting',
    'client_name' => 'John Doe',
    'client_email' => 'john@example.com',
    'start_date' => '2025-06-15',
    'end_date' => '2025-06-16',
]);
print_r($booking);

// Logout
$client->logout();
*/
