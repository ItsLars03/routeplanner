# RoutePlanner — Project README

This document describes the RoutePlanner project, how to run it locally, key files, and API endpoints (including the webhook).

## Summary
- Laravel-based route and time-slot planner UI with a Leaflet map.
- Features:
  - Select stops on the map and assign them to vehicle time-slots
  - Create and manage drivers, vehicles, stops
  - Custom date-picker and per-day routing (`/test/{DD-MM-YYYY}`)
  - Webhook endpoint that logs incoming payloads to disk

## Quick setup (local)
1. Install PHP dependencies:

   ```bash
   composer install
   ```

2. Install frontend deps (if needed) and build assets:

   ```bash
   npm install
   npm run dev
   ```

3. Create `.env` (copy from `.env.example`) and set DB credentials.

4. Run migrations:

   ```bash
   php artisan migrate
   ```

5. Serve the app locally (default port 8000):

   ```bash
   php artisan serve --host=127.0.0.1 --port=8000
   ```

6. Visit the planner UI: `http://localhost:8000/test` (default is today) or `http://localhost:8000/test/12-05-2026` for a specific date.

## Key files
- Views/UI
  - `resources/views/route.blade.php` — main planner UI (Leaflet map, custom date-picker, assignment panel)
  - `resources/views/vehicles.blade.php` — vehicles page
- Controllers
  - `app/Http/Controllers/RoutePlannerController.php` — planner API (GET planner data, initialize slots, assign driver, sync stops)
  - `app/Http/Controllers/StopController.php` — CRUD for stops (`/api/stops`)
  - `app/Http/Controllers/WebhookController.php` — webhook receiver that appends payloads to a log file
  - `app/Http/Controllers/RoutePageController.php` — handles `/test` and `/test/{date}` routes and passes initial date to the view
- Models
  - `app/Models/Stop.php` — stop model (now includes `date` attribute)
  - `app/Models/Vehicles.php`, `app/Models/VehicleTimeSlot.php`, `app/Models/Drivers.php` — core planner models
- Routes
  - `routes/web.php` — application pages, including `/test` and `/test/{date}`
  - `routes/api.php` — API endpoints used by the UI

## Important API endpoints
All API endpoints are prefixed with `/api`.

- GET `/api/route-planner?date=YYYY-MM-DD`
  - Returns planner state for the given date (vehicles, slots, drivers, stops)
  - If you use the UI, the date is passed automatically.

- POST `/api/route-planner/initialize`
  - Body: `{ "date": "YYYY-MM-DD" }`
  - Creates time slots for all vehicles for that date.

- PUT `/api/route-planner/time-slots/{timeSlot}/driver`
  - Body: `{ "driver_id": 123 | null }`
  - Assign or remove a driver for a slot.

- PUT `/api/route-planner/time-slots/{timeSlot}/stops`
  - Body: `{ "stop_ids": [1,2,3] }`
  - Replace the stops assigned to a time slot.

- CRUD for stops (new)
  - GET `/api/stops` — list stops (includes those without date and those for the requested date in planner)
  - POST `/api/stops` — create stop
    - Body example:
      ```json
      {
        "name": "Albert Heijn",
        "address": "Main Street 10, City",
        "latitude": 52.37,
        "longitude": 4.90,
        "is_active": true,
        "date": "2026-05-12",
        "slot_key": "08_10"  // required: must be one of the allowed slot keys (e.g. 08_10)
      }
      ```
  - GET `/api/stops/{id}` — get one stop
  - PUT `/api/stops/{id}` — update stop
  - DELETE `/api/stops/{id}` — remove stop

- Webhook (no token required by default)
  - POST `/api/webhook`
  - The webhook receiver saves the raw request body into a server-side log file.
  - Example cURL (no auth):

    ```bash
    curl -X POST http://localhost:8000/api/webhook \
      -H "Content-Type: application/json" \
      -d '{"event":"pickup_complete","stop_id":123,"data":{}}'
    ```

  - Log location (local disk): `storage/app/webhook_log.txt` (each entry prefixed with a timestamp).

## Using Postman / testing
- For endpoints that require authentication, add `Authorization: Bearer <token>` header.
- The webhook endpoint intentionally accepts unauthenticated posts (useful for external services). If you want to protect it, add middleware and a signature check.

## Notes about dates and UI
- Stops can have an optional `date` attribute. When the planner loads for a date, it will show:
  - Stops where `date` is NULL (global) or `date` equals the requested date
  - Time slots are created and filtered per `slot_date` (so `/test/12-05-2026` shows only slots for 2026-05-12)
- The UI also supports `/test/{DD-MM-YYYY}` route; the custom date-picker updates the browser URL automatically when selecting a date.

## Troubleshooting
- If data doesn't appear, check API response in browser devtools network tab for `/api/route-planner`.
- Ensure `storage/` is writable by the web server (webhook writes to `storage/app/webhook_log.txt`).
- Run migrations if you get DB errors:

  ```bash
  php artisan migrate
  ```

## Development notes
- The main interactive code lives inside `resources/views/route.blade.php` — this file contains the inline JS for the map, custom date-picker, and assignment logic. Keep edits small and test in the browser.
- If you change model structure, remember to run `php artisan migrate` or create new migrations.

---

If you want, I can:
- Replace the project root `README.md` with this content (currently a generic Laravel README exists), or
- Add additional examples (Postman collection export) or security notes for webhook signatures.

Tell me if you want the README placed at the project root as `README.md` or if `README_PROJECT.md` is fine.