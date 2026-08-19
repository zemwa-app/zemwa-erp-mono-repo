# Clan HRMS Desktop Agent — Database Tables Reference

Documentation for the web/manager dashboard team. All agent telemetry tables are created by `Modules/RestAPI` migrations (`2026_05_13_*`).

---

## ID mapping (important)

| Concept | Source | Used in |
|--------|--------|---------|
| **`users.id`** | `users` table | All `agent_*` tables (`user_id`), manager detail APIs (`/api/manager/employee/{id}/...`) |
| **`employee_details.employee_id`** | e.g. `E001` | Login response, manager list `employees[].id` (display only) |
| **`companies.id`** | `companies` | All `agent_*` tables (`company_id`) |
| **`companies.hash`** | optional | Login `org_id` fallback: `ORG{company_id}` |

**Note:** Manager list returns **display** `employee_id` in the `id` field. Manager detail routes (`/api/manager/employee/{id}/timeline`, etc.) expect **`users.id`** (integer), not the display code.

---

## 1. `agent_configs`

**Purpose:** Org-level monitoring settings (screenshot, app tracking, keyboard, network).

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | |
| `company_id` | int FK → `companies` | One row per company (upsert) |
| `screenshot` | json | See structure below |
| `app_tracking` | json | |
| `keyboard` | json | |
| `network` | json | |
| `created_at`, `updated_at` | timestamps | |

**Default JSON** (when no row exists):

```json
{
  "screenshot": {
    "enabled": true,
    "interval_minutes": 5,
    "quality": 75,
    "pause_on_idle": true,
    "flagged_apps": []
  },
  "app_tracking": { "enabled": true, "poll_seconds": 5 },
  "keyboard": { "enabled": true, "idle_threshold_minutes": 10 },
  "network": { "enabled": true, "large_transfer_mb": 50 }
}
```

| Writer | Reader |
|--------|--------|
| Admin (`PUT /api/admin/config`) | Agent (`GET /api/agent/config`), Admin (`GET /api/admin/config`) |

**Volume:** ~1 row per company.

---

## 2. `agent_heartbeats`

**Purpose:** Online/offline status, idle/pause state, active app — one row per heartbeat (~every 60s per device).

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | |
| `company_id` | int FK | |
| `user_id` | int FK → `users` | |
| `agent_version` | varchar(20) | e.g. `1.0.0` |
| `os`, `os_version`, `hostname` | varchar | Device info |
| `is_idle`, `is_paused` | boolean | |
| `active_app` | varchar(255) | Foreground app name |
| `pending_sync_count` | int | Agent queue size |
| `event_timestamp` | timestamp | Agent-reported time |
| `created_at` | timestamp | Server receive time (= **last_seen**) |

**Online rule (dashboard):** Latest row for `user_id` where `created_at` is within **2 minutes** of now.

| Writer | Reader |
|--------|--------|
| Agent (`POST /api/agent/heartbeat`, ~60s) | Manager (`GET /api/manager/employees`), team report |

**Volume:** High (append-only; consider retention/archival).

---

## 3. `agent_screenshots`

**Purpose:** Screenshot metadata; binary files stored on disk (`public` disk).

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | Returned to agent after upload |
| `company_id`, `user_id` | int FK | |
| `task_id` | int FK → `tasks` nullable | Active task when captured (from agent metadata) |
| `captured_at` | timestamp | When screenshot was taken |
| `file_path` | varchar(500) | e.g. `screenshots/{company_id}/{user_id}/{date}/{file}.jpg` |
| `thumbnail_path` | varchar(500) nullable | `*_thumb.jpg` (~300px wide) |
| `active_app`, `window_title` | varchar | Context at capture time |
| `category` | varchar(50) | `productive` \| `unproductive` \| `neutral` |
| `display_idx` | tinyint | Monitor index (0 = primary) |
| `is_triggered` | boolean | Manual/triggered capture |
| `file_size` | int | Bytes |

**URLs:** Built via `Storage::disk('public')->url($path)` — not stored in DB.

| Writer | Reader |
|--------|--------|
| Agent (`POST /api/agent/screenshots`, multipart) | Employee/manager screenshot APIs |

**Volume:** High (per interval × displays).

---

## 4. `agent_activity_logs`

**Purpose:** App/URL usage segments for timeline views.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | |
| `company_id`, `user_id` | int FK | |
| `app_name` | varchar(255) | e.g. `Visual Studio Code` |
| `process_name` | varchar(255) | e.g. `code.exe` |
| `window_title` | varchar(500) | |
| `url` | varchar(2048) nullable | Browser URL if any |
| `category` | varchar(50) | `productive` \| `unproductive` \| `neutral` |
| `started_at`, `ended_at` | timestamp | |
| `duration_seconds` | int | Segment length |

| Writer | Reader |
|--------|--------|
| Agent (`POST /api/agent/activity`, batch ~60s) | Timeline APIs, team report (top apps) |

**Volume:** High (many rows per day per user).

---

## 5. `agent_activity_windows`

**Purpose:** 10-minute keyboard/mouse activity buckets; **daily score** is derived from this table.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | |
| `company_id`, `user_id` | int FK | |
| `window_start`, `window_end` | timestamp | 10-min window |
| `keystrokes`, `mouse_clicks`, `mouse_distance`, `scroll_events` | int | |
| `activity_pct` | decimal(5,2) | 0–100 |
| `is_idle` | boolean | |

**Score:** Daily productivity score is computed from this table. See [Score calculation](#score-calculation) below.

| Writer | Reader |
|--------|--------|
| Agent (`POST /api/agent/activity-windows`, batch ~10 min) | Scores APIs, manager dashboard, team avg |

**Volume:** Medium–high (~6 rows/hour/user when active).

---

## Score calculation

Productivity **score** is aggregated on the server from `agent_activity_windows`. The backend does **not** compute `activity_pct` — the desktop agent sends it every ~10 minutes via `POST /api/agent/activity-windows`.

### What the agent sends

Each 10-minute bucket includes:

| Field | Description |
|-------|-------------|
| `activity_pct` | 0–100, computed by the Go agent from keyboard/mouse activity in that window |
| `is_idle` | `true` when the user had no meaningful input in that window |
| `window_start`, `window_end` | Bucket boundaries (typically 10 minutes apart) |
| `keystrokes`, `mouse_clicks`, `mouse_distance`, `scroll_events` | Raw input metrics (stored but not used in score formula) |

### Daily score (per employee, per date)

Used by:

- `GET /api/agent/employee/scores?days=30`
- `GET /api/manager/employee/{id}/scores?days=30`

**Algorithm:**

1. Select all rows for `user_id` where `DATE(window_start)` equals the target date.
2. Filter to rows where **`is_idle = false`**.
3. **Score** = arithmetic mean of `activity_pct` for those rows, rounded to **1 decimal place**.

```
score = round( avg(activity_pct) WHERE is_idle = false, 1 )
```

**Edge cases:**

| Condition | Result |
|-----------|--------|
| No windows for that day | `score = 0`, `active_seconds = 0`, `idle_seconds = 0` |
| All windows are idle | `score = 0` (no non-idle rows to average) |
| Missing days in range | API still returns an entry with zeros (padded to exactly `days` entries) |

**Example:**

| Window | `activity_pct` | `is_idle` |
|--------|----------------|-----------|
| 09:00–09:10 | 78.3 | false |
| 09:10–09:20 | 65.0 | false |
| 09:20–09:30 | 0 | true |

**Score** = (78.3 + 65.0) ÷ 2 = **71.7** (idle window excluded).

### `active_seconds` and `idle_seconds`

Returned alongside `score` but **not** used in the score formula:

| Field | Calculation |
|-------|-------------|
| `active_seconds` | Sum of `(window_end − window_start)` for windows where `is_idle = false` |
| `idle_seconds` | Sum of `(window_end − window_start)` for windows where `is_idle = true` |

### Manager: activity today (`activity_pct_today`)

Used by `GET /api/manager/employees` for each employee:

```
activity_pct_today = round( avg(activity_pct) WHERE user_id = ? AND DATE(window_start) = today AND is_idle = false, 1 )
```

Same formula as daily score, scoped to today only.

### Team average (`team_avg_score`)

Used by `GET /api/manager/reports/team?date=YYYY-MM-DD`:

```
team_avg_score = round( avg(activity_pct) WHERE company employees AND DATE(window_start) = date AND is_idle = false, 1 )
```

This is a **single average across all non-idle windows for all employees** on that date — not the average of each employee’s daily score. Employees with more active windows weigh more on the team number.

### API response example

`GET /api/agent/employee/scores?days=30`:

```json
{
  "scores": [
    { "date": "2026-05-12", "score": 82.1, "active_seconds": 25920, "idle_seconds": 1800 },
    { "date": "2026-05-13", "score": 0,    "active_seconds": 0,     "idle_seconds": 0 }
  ]
}
```

### Implementation reference

| Endpoint | Controller |
|----------|------------|
| Employee scores | `EmployeeScoreController@index` |
| Manager employee scores | `ManagerController@employeeScores` |
| Manager list (today) | `ManagerController@employees` |
| Team report | `ManagerController@teamReport` |

### What score does **not** include

- App/URL **category** (`productive` / `unproductive`) — that lives in `agent_activity_logs` and is not factored into score today.
- Screenshot or network data.
- Paused monitoring periods (agent may send idle windows or stop uploading while paused).

---

## 6. `agent_network_logs`

**Purpose:** Hourly network usage summary per user.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | |
| `company_id`, `user_id` | int FK | |
| `hour` | timestamp | Hour bucket start |
| `total_bytes_sent`, `total_bytes_received` | bigint | |
| `top_processes` | json | `[{ "process", "bytes_sent", "bytes_received" }, ...]` |
| `cloud_uploads_detected` | json | e.g. `["drive.google.com"]` |
| `vpn_active`, `large_transfer_alert` | boolean | |

| Writer | Reader |
|--------|--------|
| Agent (`POST /api/agent/network`, ~hourly) | *(no dedicated read API yet — web can query directly)* |

**Volume:** ~24 rows/user/day if enabled.

---

## 7. `agent_events`

**Purpose:** Alerts and lifecycle events (tamper, pause, session, errors).

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | |
| `company_id`, `user_id` | int FK | |
| `event_type` | varchar(50) | See enum below |
| `payload` | json | Event-specific body |
| `created_at` | timestamp | |

**`event_type` values:**

| Value | Meaning |
|-------|---------|
| `tamper_detected` | Watchdog / agent process killed |
| `pause_started` | Employee paused monitoring |
| `pause_ended` | Monitoring resumed |
| `session_started` | Agent started after login |
| `session_ended` | Employee logged out |
| `agent_error` | Module crashed or failed |
| `usb_connected` | USB storage device plugged in |
| `large_upload_detected` | >50MB uploaded in 10 min |
| `cloud_upload_detected` | File sent to Dropbox/Drive etc. |

| Writer | Reader |
|--------|--------|
| Agent (`POST /api/agent/events`), pause/logout controllers | *(no dedicated read API yet)* |

**Volume:** Low–medium (event-driven).

---

## 8. `agent_pauses`

**Purpose:** Structured pause records when employee pauses monitoring.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | Returned as `pause_id` |
| `company_id`, `user_id` | int FK | |
| `reason` | varchar(255) nullable | e.g. `Lunch` |
| `duration_minutes` | int | Planned length |
| `started_at`, `ends_at` | timestamp | Planned end |
| `resumed_at` | timestamp nullable | Actual resume (null = still paused) |

| Writer | Reader |
|--------|--------|
| Agent (`POST /api/agent/pause`, `/resume`) | Manager (via `is_paused` on latest heartbeat) |

**Volume:** Low (few per day per user).

---

## 9. `agent_productivity_categories`

**Purpose:** Org-level overrides for app/URL → productivity category (pattern matching).

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | |
| `company_id` | int FK | |
| `pattern` | varchar(255) | e.g. `youtube.com`, `figma.com` |
| `category` | varchar(50) | `productive` \| `unproductive` \| `neutral` |
| `note` | varchar(500) nullable | Admin note |

| Writer | Reader |
|--------|--------|
| Admin (`PUT /api/admin/productivity-categories`) | Admin GET; agent may apply locally after sync |

**Volume:** Tens of rows per company typical.

---

## Related tables (existing app — not agent-specific)

| Table | Role for monitoring |
|-------|---------------------|
| **`users`** | Employee identity; all `user_id` foreign keys |
| **`employee_details`** | `employee_id` display code; department via `department_id` → `teams` |
| **`companies`** | Tenant boundary; `company_name`, optional `hash` for `org_id` |
| **`user_auth`** | Login email/password (agent login authenticates here, then resolves `users`) |
| **`personal_access_tokens`** | Sanctum tokens; agent tokens named `desktop-agent-*`, ability `agent`, ~30-day expiry; optional `claims` JSON column |

---

## Monitor module (UI shell — not agent telemetry)

| Table | Purpose |
|-------|---------|
| **`monitor_settings`** | Module license/purchase metadata only |

Monitoring configuration for the web UI should use **`agent_configs`** and **`agent_productivity_categories`**, not `monitor_settings`.

---

## API ↔ table quick map

| Endpoint group | Primary tables |
|----------------|----------------|
| `POST /api/agent/login` | `user_auth`, `users`, `employee_details`, `companies`, `personal_access_tokens` |
| `GET /api/agent/config` | `agent_configs` |
| `POST /api/agent/heartbeat` | `agent_heartbeats` |
| `POST /api/agent/screenshots` | `agent_screenshots` + filesystem |
| `POST /api/agent/activity` | `agent_activity_logs` |
| `POST /api/agent/activity-windows` | `agent_activity_windows` |
| `POST /api/agent/network` | `agent_network_logs` |
| `POST /api/agent/events` | `agent_events` |
| `POST /api/agent/pause` \| `resume` | `agent_pauses`, `agent_events` |
| `GET /api/agent/employee/*` | `agent_activity_logs`, `agent_activity_windows`, `agent_screenshots` |
| `GET /api/manager/*` | Above + `users`, `employee_details`, `agent_heartbeats` |
| `GET/PUT /api/admin/*` | `agent_configs`, `agent_productivity_categories` |

---

## Suggested web dashboard queries

| Screen | Tables / logic |
|--------|----------------|
| Live team grid | Latest `agent_heartbeats` per `user_id`; online if `created_at` < 2 min ago |
| Employee timeline | `agent_activity_logs` WHERE `user_id` AND `DATE(started_at) = ?` |
| Daily score chart | `agent_activity_windows` grouped by date; avg `activity_pct` WHERE `is_idle = 0` |
| Screenshots gallery | `agent_screenshots` + public URLs from `file_path` / `thumbnail_path` |
| Alerts feed | `agent_events` ORDER BY `created_at` DESC |
| Pauses | `agent_pauses` WHERE `resumed_at` IS NULL OR recent |
| Settings | `agent_configs`, `agent_productivity_categories` |

---

## Example SQL snippets

### Latest heartbeat per employee (online status)

```sql
SELECT h.*
FROM agent_heartbeats h
INNER JOIN (
    SELECT user_id, MAX(created_at) AS last_at
    FROM agent_heartbeats
    WHERE company_id = ?
    GROUP BY user_id
) latest ON h.user_id = latest.user_id AND h.created_at = latest.last_at;
```

### Daily productivity score

Matches [Score calculation](#score-calculation) (daily `score`, `active_seconds`, `idle_seconds`):

```sql
SELECT
    DATE(window_start) AS date,
    AVG(activity_pct) AS score,
    SUM(CASE WHEN is_idle = 0 THEN TIMESTAMPDIFF(SECOND, window_start, window_end) ELSE 0 END) AS active_seconds,
    SUM(CASE WHEN is_idle = 1 THEN TIMESTAMPDIFF(SECOND, window_start, window_end) ELSE 0 END) AS idle_seconds
FROM agent_activity_windows
WHERE user_id = ? AND window_start >= ? AND window_start < ?
GROUP BY DATE(window_start);
```

### Top apps for a day (team report)

```sql
SELECT app_name, category, SUM(duration_seconds) / 60 AS total_minutes
FROM agent_activity_logs
WHERE company_id = ? AND DATE(started_at) = ?
GROUP BY app_name, category
ORDER BY total_minutes DESC;
```

---

## ER diagram (simplified)

```
companies
    ├── agent_configs (1 per company)
    ├── agent_productivity_categories (many)
    └── users
            ├── employee_details (employee_id display)
            ├── agent_heartbeats
            ├── agent_screenshots
            ├── agent_activity_logs
            ├── agent_activity_windows
            ├── agent_network_logs
            ├── agent_events
            └── agent_pauses
```

---

## Migration files

| Migration | Table |
|-----------|-------|
| `2026_05_13_000001_create_agent_configs_table.php` | `agent_configs` |
| `2026_05_13_000002_create_agent_heartbeats_table.php` | `agent_heartbeats` |
| `2026_05_13_000003_create_agent_screenshots_table.php` | `agent_screenshots` |
| `2026_05_13_000004_create_agent_activity_logs_table.php` | `agent_activity_logs` |
| `2026_05_13_000005_create_agent_activity_windows_table.php` | `agent_activity_windows` |
| `2026_05_13_000006_create_agent_network_logs_table.php` | `agent_network_logs` |
| `2026_05_13_000007_create_agent_events_table.php` | `agent_events` |
| `2026_05_13_000008_create_agent_pauses_table.php` | `agent_pauses` |
| `2026_05_13_000009_create_agent_productivity_categories_table.php` | `agent_productivity_categories` |
| `2026_05_19_000001_add_task_id_to_agent_screenshots_table.php` | adds `task_id` to `agent_screenshots` |

Run migrations: `php artisan migrate`
