# Calendar API Documentation

## Overview

The Calendar API provides FullCalendar-compatible endpoints for retrieving event data. All responses are formatted to work seamlessly with FullCalendar.js.

**Base URL:** `/api/v1`

**Authentication:** Not required for read operations

---

## Endpoints

### 1. Get Calendar Events

Retrieve events in FullCalendar format with optional filtering.

**Endpoint:** `GET /api/v1/events/calendar`

**Query Parameters:**

| Parameter           | Type    | Required | Description                                                        |
| ------------------- | ------- | -------- | ------------------------------------------------------------------ |
| `start`             | date    | No       | Start date (YYYY-MM-DD) - filters events starting from this date   |
| `end`               | date    | No       | End date (YYYY-MM-DD) - filters events up to this date             |
| `space_id`          | integer | No       | Filter by event space ID                                           |
| `status`            | string  | No       | Filter by status: `pending`, `confirmed`, `completed`, `cancelled` |
| `include_cancelled` | boolean | No       | Include cancelled events (default: false)                          |

**Example Request:**

```bash
GET /api/v1/events/calendar?start=2025-01-01&end=2025-01-31&space_id=1
```

**Example Response:**

```json
{
    "success": true,
    "message": "Calendar events retrieved successfully",
    "data": [
        {
            "id": 1,
            "title": "Corporate Event",
            "start": "2025-01-15",
            "end": "2025-01-17",
            "allDay": true,
            "backgroundColor": "#10b981",
            "borderColor": "#059669",
            "textColor": "#ffffff",
            "extendedProps": {
                "status": "confirmed",
                "space": "Main Hall",
                "space_id": 1,
                "client": "John Doe",
                "description": "Annual corporate gathering",
                "start_date": "2025-01-15",
                "end_date": "2025-01-16",
                "duration_days": 2
            }
        }
    ]
}
```

---

### 2. Get Calendar Events by Month

Retrieve events for a specific month with metadata.

**Endpoint:** `GET /api/v1/events/calendar/month`

**Query Parameters:**

| Parameter           | Type    | Required | Description                               |
| ------------------- | ------- | -------- | ----------------------------------------- |
| `year`              | integer | Yes      | Year (2000-2100)                          |
| `month`             | integer | Yes      | Month (1-12)                              |
| `space_id`          | integer | No       | Filter by event space ID                  |
| `status`            | string  | No       | Filter by status                          |
| `include_cancelled` | boolean | No       | Include cancelled events (default: false) |

**Example Request:**

```bash
GET /api/v1/events/calendar/month?year=2025&month=1&space_id=1
```

**Example Response:**

```json
{
    "success": true,
    "message": "Calendar events for month retrieved successfully",
    "data": {
        "year": 2025,
        "month": 1,
        "month_name": "January",
        "start_date": "2025-01-01",
        "end_date": "2025-01-31",
        "total_events": 5,
        "events": [
            {
                "id": 1,
                "title": "Corporate Event",
                "start": "2025-01-15",
                "end": "2025-01-17",
                "allDay": true,
                "backgroundColor": "#10b981",
                "borderColor": "#059669",
                "textColor": "#ffffff",
                "extendedProps": {
                    "status": "confirmed",
                    "space": "Main Hall",
                    "space_id": 1,
                    "client": "John Doe",
                    "description": "Annual corporate gathering",
                    "start_date": "2025-01-15",
                    "end_date": "2025-01-16",
                    "duration_days": 2
                }
            }
        ]
    }
}
```

---

## FullCalendar Integration

### Using with FullCalendar.js

The API is designed to work seamlessly with FullCalendar. Here's how to integrate it:

**JavaScript Example:**

```javascript
import FullCalendar from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';

const calendarEl = document.getElementById('calendar');

const calendar = new FullCalendar.Calendar(calendarEl, {
    plugins: [dayGridPlugin],
    initialView: 'dayGridMonth',

    // Method 1: Fetch all events
    events: async function (info, successCallback, failureCallback) {
        try {
            const response = await fetch(
                `/api/v1/events/calendar?start=${info.startStr}&end=${info.endStr}`,
            );
            const data = await response.json();
            successCallback(data.data);
        } catch (error) {
            failureCallback(error);
        }
    },

    // Method 2: Using events as URL
    events: '/api/v1/events/calendar',

    // Event click handler
    eventClick: function (info) {
        console.log('Event clicked:', info.event.extendedProps);
    },
});

calendar.render();
```

**Vue 3 Example:**

```vue
<script setup>
import { ref } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';

const calendarOptions = ref({
    plugins: [dayGridPlugin],
    initialView: 'dayGridMonth',
    events: async (info) => {
        const response = await fetch(
            `/api/v1/events/calendar?start=${info.startStr}&end=${info.endStr}`,
        );
        const data = await response.json();
        return data.data;
    },
});
</script>

<template>
    <FullCalendar :options="calendarOptions" />
</template>
```

---

## Event Object Structure

### Standard Fields (FullCalendar Compatible)

| Field             | Type    | Description                       |
| ----------------- | ------- | --------------------------------- |
| `id`              | integer | Unique event identifier           |
| `title`           | string  | Event title                       |
| `start`           | string  | Start date (YYYY-MM-DD)           |
| `end`             | string  | End date (YYYY-MM-DD) - Exclusive |
| `allDay`          | boolean | Always true for this API          |
| `backgroundColor` | string  | Event background color (hex)      |
| `borderColor`     | string  | Event border color (hex)          |
| `textColor`       | string  | Event text color (hex)            |

### Extended Properties

Additional data available in `extendedProps`:

| Field           | Type    | Description            |
| --------------- | ------- | ---------------------- |
| `status`        | string  | Event status           |
| `space`         | string  | Event space name       |
| `space_id`      | integer | Event space ID         |
| `client`        | string  | Client name            |
| `description`   | string  | Event description      |
| `start_date`    | string  | Inclusive start date   |
| `end_date`      | string  | Inclusive end date     |
| `duration_days` | integer | Event duration in days |

---

## Status Colors

Events are automatically color-coded based on status:

| Status      | Background      | Border  | Description            |
| ----------- | --------------- | ------- | ---------------------- |
| `pending`   | #f59e0b (amber) | #d97706 | Event pending approval |
| `confirmed` | #10b981 (green) | #059669 | Event confirmed        |
| `completed` | #6b7280 (gray)  | #4b5563 | Event completed        |
| `cancelled` | #ef4444 (red)   | #dc2626 | Event cancelled        |

---

## Date Handling

### Important Notes

1. **Exclusive End Dates**: The API returns end dates in FullCalendar's exclusive format. For a 2-day event (Jan 15-16), the API returns:
    - `start`: "2025-01-15"
    - `end`: "2025-01-17" (exclusive)

2. **Inclusive Dates**: For your own calculations, use `extendedProps.start_date` and `extendedProps.end_date`, which are inclusive.

3. **Duration**: Use `extendedProps.duration_days` for the actual event duration.

---

## Filtering Examples

### Filter by Space

```bash
GET /api/v1/events/calendar?space_id=1
```

### Filter by Status

```bash
GET /api/v1/events/calendar?status=confirmed
```

### Include Cancelled Events

```bash
GET /api/v1/events/calendar?include_cancelled=1
```

### Filter by Date Range

```bash
GET /api/v1/events/calendar?start=2025-01-01&end=2025-12-31
```

### Combine Filters

```bash
GET /api/v1/events/calendar?start=2025-01-01&end=2025-12-31&space_id=1&status=confirmed
```

---

## Error Handling

All endpoints follow the standard API response format:

**Success Response:**

```json
{
    "success": true,
    "message": "Success message",
    "data": []
}
```

**Error Response:**

```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": ["Validation error"]
    }
}
```

### Common Errors

| Status Code | Description                |
| ----------- | -------------------------- |
| 400         | Invalid request parameters |
| 404         | Event space not found      |
| 422         | Validation error           |
| 500         | Server error               |

---

## Rate Limiting

API requests are rate-limited to prevent abuse. Current limits:

- **60 requests per minute** for unauthenticated requests
- **120 requests per minute** for authenticated requests

---

## Best Practices

1. **Use Date Ranges**: Always specify `start` and `end` dates to limit data transfer
2. **Cache Responses**: Cache calendar data client-side to reduce API calls
3. **Filter Early**: Apply filters at the API level rather than client-side
4. **Handle Errors**: Always implement proper error handling
5. **Respect Rate Limits**: Implement exponential backoff for retries

---

## Support

For API support or questions, contact your system administrator.
