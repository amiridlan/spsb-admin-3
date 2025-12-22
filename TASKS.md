# Event Space Booking System — AI Task Checklist (Clean Renumbered)

<!--
AI RULES:
- Treat each checklist item as an atomic task
- Do NOT combine tasks
- Work top-to-bottom
- Do NOT mark tasks complete unless explicitly instructed
- Reference tasks by ID in responses
-->

---

## 0. Global Technical Constraints (Mandatory)

<!--
Hard constraints. Violations are not allowed.
-->

- [x] (G-01) Backend framework is Laravel 12
- [x] (G-02) Database is PostgreSQL
- [x] (G-03) API authentication uses Laravel Sanctum
- [x] (G-04) Admin authentication uses Laravel Fortify (session-based)
- [x] (G-05) API is stateless
- [x] (G-06) No JWT, Passport, or MySQL usage

---

## 1. Global API Standards

<!--
All API responses must follow the defined structure.
-->

```ts
{
  success: boolean,
  data: object | array,
  message?: string,
  meta?: {
    pagination,
    version
  }
}
```

- [x] (API-01) Create base API response trait
- [x] (API-02) Enforce response wrapping for all endpoints
- [ ] (API-03) Include pagination metadata on list endpoints
- [ ] (API-04) Include API version in response metadata
- [ ] (API-05) Validate controllers do not return raw models

---

## 2. Sprint 1 — Foundation, Authentication & API Setup (DONE)

### API & Infrastructure

- [x] (S1-01) Install and configure Laravel Sanctum
- [x] (S1-02) Run Sanctum migrations (`personal_access_tokens`)
- [x] (S1-03) Create API routes structure (`routes/api.php`)
- [x] (S1-04) Implement token-based API authentication
- [x] (S1-05) Build API token issuance and revocation
- [x] (S1-06) Implement API rate-limiting middleware
- [x] (S1-07) Configure API versioning (`/api/v1`)
- [x] (S1-08) Configure CORS for external domains

### Admin Authentication

- [x] (S1-09) Install and configure Laravel Fortify
- [x] (S1-10) Disable public registration
- [x] (S1-11) Build admin user management UI
- [x] (S1-12) Build admin API token management UI

### Validation

- [x] (S1-13) Verify API auth with external domain
- [x] (S1-14) Verify admin-only access enforcement

---

## 3. Sprint 2 — Event Spaces, Events & Bookings (DONE)

### Event Spaces

- [x] (S2-01) Create EventSpace model and migration
- [x] (S2-02) Build EventSpace CRUD (admin)
- [x] (S2-03) Create `GET /api/v1/event-spaces`
- [x] (S2-04) Create `GET /api/v1/event-spaces/{id}`
- [x] (S2-05) Create EventSpace API Resource

### Events & Availability

- [x] (S2-06) Create Event model with multi-day support
- [x] (S2-07) Build admin event management UI
- [x] (S2-08) Implement availability checking logic
- [x] (S2-09) Create `GET /api/v1/events/availability`

### Bookings

- [x] (S2-10) Create Booking model and migration
- [x] (S2-11) Implement booking status workflow
- [x] (S2-12) Create `POST /api/v1/bookings`
- [x] (S2-13) Create `GET /api/v1/bookings/{id}`
- [x] (S2-14) Create `PATCH /api/v1/bookings/{id}`
- [x] (S2-15) Add booking request validation
- [x] (S2-16) Create Booking API Resource

### Controllers

- [x] (S2-17) Create EventSpaceApiController
- [x] (S2-18) Create BookingApiController

---

## 4. Sprint 3 — Staff Assignment & API Extensions

### Staff System

- [ ] (S3-01) Create Staff model and migration
- [ ] (S3-02) Define staff–booking relationships
- [ ] (S3-03) Implement staff availability checker
- [ ] (S3-04) Build assignment management UI
- [ ] (S3-05) Build “My Assignments” staff view

### API Extensions

- [ ] (S3-06) Create `GET /api/v1/bookings/{id}/staff`
- [ ] (S3-07) Restrict staff data exposure
- [ ] (S3-08) Implement booking status webhook (optional)

---

## 5. Sprint 4 — Calendar Integration & Calendar API

### Calendar UI

- [ ] (S4-01) Install and configure FullCalendar
- [ ] (S4-02) Build admin calendar component
- [ ] (S4-03) Implement multi-day rendering
- [ ] (S4-04) Add status-based color coding
- [ ] (S4-05) Build event detail view
- [ ] (S4-06) Add calendar filters

### Calendar API

- [ ] (S4-07) Create `GET /api/v1/events/calendar`
- [ ] (S4-08) Create `GET /api/v1/events/calendar/month`
- [ ] (S4-09) Format output for FullCalendar compatibility

---

## 6. Sprint 5 — API Documentation & Testing

### Documentation

- [ ] (S5-01) Install `knuckleswtf/scribe`
- [ ] (S5-02) Configure Scribe for Sanctum auth
- [ ] (S5-03) Generate API documentation
- [ ] (S5-04) Restrict API docs to non-production

### Testing & Reliability

- [ ] (S5-05) Create API test suite (Pest/PHPUnit)
- [ ] (S5-06) Test booking workflow
- [ ] (S5-07) Implement global API rate limiting
- [ ] (S5-08) Add API logging and monitoring
- [ ] (S5-09) Finalize API versioning strategy

### Admin Tools

- [ ] (S5-10) Build API key management UI
- [ ] (S5-11) Create example API client code

---

## 7. Sprint 6 — Dashboard, Reporting & Polish

### Dashboards & Reports

- [ ] (S6-01) Build role-specific dashboards
- [ ] (S6-02) Add booking metrics and statistics
- [ ] (S6-03) Create booking reports
- [ ] (S6-04) Implement data export (CSV/PDF)

### System Polish

- [ ] (S6-05) Add notification system
- [ ] (S6-06) UI/UX refinements
- [ ] (S6-07) Performance optimization

### Final Validation

- [ ] (S6-08) Admin dashboards complete
- [ ] (S6-09) Reporting features operational
- [ ] (S6-10) System production-ready
