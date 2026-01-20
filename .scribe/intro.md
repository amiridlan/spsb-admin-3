# Introduction

API for managing event space bookings, calendar events, and staff assignments.

<aside>
    <strong>Base URL</strong>: <code>http://localhost</code>
</aside>

Welcome to the Event Space Booking System API documentation.

This API allows you to:
- Browse and book event spaces
- Manage events and bookings
- View calendar data in FullCalendar-compatible format
- Access staff assignments

## Base URL

All API requests should be made to: `{(money_sign)base_url}/api/v1`

## Authentication

Most endpoints are public for reading. Creating bookings requires authentication using Laravel Sanctum.

To authenticate:
1. Login via `POST /api/v1/login` with email and password
2. Receive an API token in the response
3. Include the token in subsequent requests: `Authorization: Bearer {token}`

## Rate Limiting

- **Unauthenticated**: 60 requests per minute
- **Authenticated**: 120 requests per minute

## Response Format

All responses follow this structure:

```json
{
  "success": true,
  "message": "Success message",
  "data": {}
}
```

Error responses include an `errors` object with validation details.

