/**
 * Event Space Booking System API Client (JavaScript)
 *
 * Example JavaScript/TypeScript client for the Event Space Booking System API.
 * Works in both Node.js and browser environments.
 *
 * Usage:
 *   const client = new EventSpaceApiClient('https://your-app.com', 'your-api-token');
 *   const spaces = await client.getEventSpaces();
 */

class EventSpaceApiClient {
    constructor(baseUrl, token = null) {
        this.baseUrl = baseUrl.replace(/\/$/, '') + '/api/v1';
        this.token = token;
    }

    /**
     * Set the API token for authenticated requests
     */
    setToken(token) {
        this.token = token;
    }

    /**
     * Login and receive an API token
     */
    async login(email, password) {
        const response = await this.request('POST', '/login', { email, password }, false);
        if (response.success && response.data?.token) {
            this.token = response.data.token;
        }
        return response;
    }

    /**
     * Logout and revoke the current token
     */
    async logout() {
        const response = await this.request('POST', '/logout', {}, true);
        this.token = null;
        return response;
    }

    /**
     * Get the current user's profile
     */
    async getCurrentUser() {
        return this.request('GET', '/user', {}, true);
    }

    /**
     * Get all active event spaces
     */
    async getEventSpaces() {
        return this.request('GET', '/event-spaces');
    }

    /**
     * Get a specific event space by ID
     */
    async getEventSpace(id) {
        return this.request('GET', `/event-spaces/${id}`);
    }

    /**
     * Get all events with optional filters
     */
    async getEvents({ spaceId, startDate, endDate } = {}) {
        const params = new URLSearchParams();
        if (spaceId) params.append('space_id', spaceId);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        const queryString = params.toString();
        return this.request('GET', `/events${queryString ? `?${queryString}` : ''}`);
    }

    /**
     * Get a specific event by ID
     */
    async getEvent(id) {
        return this.request('GET', `/events/${id}`);
    }

    /**
     * Check availability for a date range in a specific space
     */
    async checkAvailability(spaceId, startDate, endDate) {
        return this.request('POST', '/events/check-availability', {
            event_space_id: spaceId,
            start_date: startDate,
            end_date: endDate,
        });
    }

    /**
     * Create a new booking (requires authentication)
     */
    async createBooking(data) {
        return this.request('POST', '/bookings', data, true);
    }

    /**
     * Get calendar events in FullCalendar format
     */
    async getCalendarEvents({ start, end, spaceId, status, includeCancelled } = {}) {
        const params = new URLSearchParams();
        if (start) params.append('start', start);
        if (end) params.append('end', end);
        if (spaceId) params.append('space_id', spaceId);
        if (status) params.append('status', status);
        if (includeCancelled) params.append('include_cancelled', '1');

        const queryString = params.toString();
        return this.request('GET', `/events/calendar${queryString ? `?${queryString}` : ''}`);
    }

    /**
     * Get calendar events for a specific month
     */
    async getMonthlyCalendar(year, month, spaceId = null) {
        const params = new URLSearchParams({
            year: year.toString(),
            month: month.toString(),
        });
        if (spaceId) params.append('space_id', spaceId);

        return this.request('GET', `/events/calendar/month?${params.toString()}`);
    }

    /**
     * Get staff assigned to a booking
     */
    async getBookingStaff(eventId) {
        return this.request('GET', `/bookings/${eventId}/staff`);
    }

    /**
     * Make an HTTP request to the API
     */
    async request(method, endpoint, data = {}, requiresAuth = false) {
        const url = this.baseUrl + endpoint;

        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };

        if (requiresAuth && this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        const options = {
            method,
            headers,
        };

        if (method === 'POST' || method === 'PATCH' || method === 'PUT') {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const json = await response.json();
            return json;
        } catch (error) {
            throw new Error(`API request failed: ${error.message}`);
        }
    }
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { EventSpaceApiClient };
}

// Example usage:
/*
const client = new EventSpaceApiClient('https://your-app.com');

// List event spaces (no auth required)
const spaces = await client.getEventSpaces();
console.log(spaces);

// Check availability (no auth required)
const availability = await client.checkAvailability(1, '2025-06-15', '2025-06-16');
console.log(availability);

// Login to get token
await client.login('user@example.com', 'password123');

// Create a booking (auth required)
const booking = await client.createBooking({
    event_space_id: 1,
    title: 'Corporate Meeting',
    client_name: 'John Doe',
    client_email: 'john@example.com',
    start_date: '2025-06-15',
    end_date: '2025-06-16',
});
console.log(booking);

// Logout
await client.logout();
*/
