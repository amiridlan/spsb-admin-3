"""
Event Space Booking System API Client (Python)

Example Python client for the Event Space Booking System API.
Requires Python 3.8+ with requests library.

Usage:
    client = EventSpaceApiClient('https://your-app.com', 'your-api-token')
    spaces = client.get_event_spaces()

Installation:
    pip install requests
"""

import requests
from typing import Optional, Dict, Any, List
from dataclasses import dataclass


@dataclass
class ApiResponse:
    """Standard API response wrapper"""
    success: bool
    data: Any
    message: Optional[str] = None
    meta: Optional[Dict] = None
    errors: Optional[Dict] = None


class EventSpaceApiClient:
    """Client for the Event Space Booking System API"""

    def __init__(self, base_url: str, token: Optional[str] = None):
        """
        Initialize the API client.

        Args:
            base_url: The base URL of the API (e.g., 'https://your-app.com')
            token: Optional API token for authenticated requests
        """
        self.base_url = base_url.rstrip('/') + '/api/v1'
        self.token = token
        self.session = requests.Session()
        self.session.headers.update({
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        })

    def set_token(self, token: str) -> None:
        """Set the API token for authenticated requests"""
        self.token = token

    def login(self, email: str, password: str) -> ApiResponse:
        """
        Login and receive an API token.

        Args:
            email: User email address
            password: User password

        Returns:
            ApiResponse with token and user data
        """
        response = self._request('POST', '/login', {
            'email': email,
            'password': password,
        }, requires_auth=False)

        if response.success and response.data and 'token' in response.data:
            self.token = response.data['token']

        return response

    def logout(self) -> ApiResponse:
        """Logout and revoke the current token"""
        response = self._request('POST', '/logout', requires_auth=True)
        self.token = None
        return response

    def get_current_user(self) -> ApiResponse:
        """Get the current user's profile"""
        return self._request('GET', '/user', requires_auth=True)

    def get_event_spaces(self) -> ApiResponse:
        """Get all active event spaces"""
        return self._request('GET', '/event-spaces')

    def get_event_space(self, space_id: int) -> ApiResponse:
        """Get a specific event space by ID"""
        return self._request('GET', f'/event-spaces/{space_id}')

    def get_events(
        self,
        space_id: Optional[int] = None,
        start_date: Optional[str] = None,
        end_date: Optional[str] = None
    ) -> ApiResponse:
        """
        Get all events with optional filters.

        Args:
            space_id: Filter by event space ID
            start_date: Filter events starting from this date (Y-m-d)
            end_date: Filter events ending before this date (Y-m-d)
        """
        params = {}
        if space_id:
            params['space_id'] = space_id
        if start_date:
            params['start_date'] = start_date
        if end_date:
            params['end_date'] = end_date

        endpoint = '/events'
        if params:
            query_string = '&'.join(f'{k}={v}' for k, v in params.items())
            endpoint = f'{endpoint}?{query_string}'

        return self._request('GET', endpoint)

    def get_event(self, event_id: int) -> ApiResponse:
        """Get a specific event by ID"""
        return self._request('GET', f'/events/{event_id}')

    def check_availability(
        self,
        space_id: int,
        start_date: str,
        end_date: str
    ) -> ApiResponse:
        """
        Check availability for a date range in a specific space.

        Args:
            space_id: The event space ID
            start_date: Start date (Y-m-d)
            end_date: End date (Y-m-d)

        Returns:
            ApiResponse with availability status
        """
        return self._request('POST', '/events/check-availability', {
            'event_space_id': space_id,
            'start_date': start_date,
            'end_date': end_date,
        })

    def create_booking(self, data: Dict[str, Any]) -> ApiResponse:
        """
        Create a new booking (requires authentication).

        Args:
            data: Booking data including:
                - event_space_id (required): The event space ID
                - title (required): Event title
                - client_name (required): Client name
                - client_email (required): Client email
                - start_date (required): Start date (Y-m-d)
                - end_date (required): End date (Y-m-d)
                - description (optional): Event description
                - client_phone (optional): Client phone

        Returns:
            ApiResponse with created booking data
        """
        return self._request('POST', '/bookings', data, requires_auth=True)

    def get_calendar_events(
        self,
        start: Optional[str] = None,
        end: Optional[str] = None,
        space_id: Optional[int] = None,
        status: Optional[str] = None,
        include_cancelled: bool = False
    ) -> ApiResponse:
        """
        Get calendar events in FullCalendar format.

        Args:
            start: Start date filter (Y-m-d)
            end: End date filter (Y-m-d)
            space_id: Filter by event space
            status: Filter by status (pending, confirmed, completed, cancelled)
            include_cancelled: Include cancelled events
        """
        params = {}
        if start:
            params['start'] = start
        if end:
            params['end'] = end
        if space_id:
            params['space_id'] = space_id
        if status:
            params['status'] = status
        if include_cancelled:
            params['include_cancelled'] = '1'

        endpoint = '/events/calendar'
        if params:
            query_string = '&'.join(f'{k}={v}' for k, v in params.items())
            endpoint = f'{endpoint}?{query_string}'

        return self._request('GET', endpoint)

    def get_monthly_calendar(
        self,
        year: int,
        month: int,
        space_id: Optional[int] = None
    ) -> ApiResponse:
        """
        Get calendar events for a specific month.

        Args:
            year: The year (2000-2100)
            month: The month (1-12)
            space_id: Optional filter by event space
        """
        params = {'year': year, 'month': month}
        if space_id:
            params['space_id'] = space_id

        query_string = '&'.join(f'{k}={v}' for k, v in params.items())
        return self._request('GET', f'/events/calendar/month?{query_string}')

    def get_booking_staff(self, event_id: int) -> ApiResponse:
        """Get staff assigned to a booking"""
        return self._request('GET', f'/bookings/{event_id}/staff')

    def _request(
        self,
        method: str,
        endpoint: str,
        data: Optional[Dict] = None,
        requires_auth: bool = False
    ) -> ApiResponse:
        """Make an HTTP request to the API"""
        url = self.base_url + endpoint

        headers = {}
        if requires_auth and self.token:
            headers['Authorization'] = f'Bearer {self.token}'

        try:
            if method == 'GET':
                response = self.session.get(url, headers=headers)
            elif method == 'POST':
                response = self.session.post(url, json=data, headers=headers)
            elif method == 'PATCH':
                response = self.session.patch(url, json=data, headers=headers)
            elif method == 'DELETE':
                response = self.session.delete(url, headers=headers)
            else:
                raise ValueError(f'Unsupported HTTP method: {method}')

            json_data = response.json()

            return ApiResponse(
                success=json_data.get('success', False),
                data=json_data.get('data'),
                message=json_data.get('message'),
                meta=json_data.get('meta'),
                errors=json_data.get('errors'),
            )
        except requests.RequestException as e:
            return ApiResponse(
                success=False,
                data=None,
                message=f'Request failed: {str(e)}',
                errors={'request': [str(e)]},
            )


# Example usage:
if __name__ == '__main__':
    # Initialize client
    client = EventSpaceApiClient('https://your-app.com')

    # List event spaces (no auth required)
    spaces = client.get_event_spaces()
    print('Event Spaces:', spaces)

    # Check availability (no auth required)
    availability = client.check_availability(1, '2025-06-15', '2025-06-16')
    print('Availability:', availability)

    # Login to get token
    login_response = client.login('user@example.com', 'password123')
    print('Login:', login_response)

    if login_response.success:
        # Create a booking (auth required)
        booking = client.create_booking({
            'event_space_id': 1,
            'title': 'Corporate Meeting',
            'client_name': 'John Doe',
            'client_email': 'john@example.com',
            'start_date': '2025-06-15',
            'end_date': '2025-06-16',
        })
        print('Booking:', booking)

        # Logout
        client.logout()
