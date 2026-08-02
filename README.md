# Task Management API

A RESTful API for a simple Task Management System, built with **Laravel 11** and **Sanctum** token authentication. Users manage their own projects, each project holds many tasks, and a dashboard endpoint returns aggregated statistics.

The codebase applies a layered architecture — **Controllers → Services → Repositories → Eloquent** — with Form Request validation, API Resources, Policies for ownership, soft deletes, an overdue-task queue job, and OpenAPI (Swagger) documentation.

## Features

- Sanctum authentication: register, login, logout, current user
- Projects CRUD scoped to the authenticated user (name, description, status)
- Tasks CRUD nested under projects (title, description, priority, status, due date)
- Task filtering by status and priority, plus search by title
- Dashboard endpoint with project/task aggregates
- Pagination on all list endpoints
- Consistent JSON envelopes and proper HTTP status codes
- Soft deletes on projects and tasks

### Architecture / bonus features included

- **Service layer** (`app/Services`) keeps controllers thin
- **Repository pattern** (`app/Repositories`) with interfaces bound in `RepositoryServiceProvider`
- **Base controller** (`app/Http/Controllers/Controller.php`) with `success()` / `created()` / `error()` / `noContent()` response helpers
- **Form Request validation** (`app/Http/Requests`) and centralized error handling in `bootstrap/app.php`
- **API Resources** (`app/Http/Resources`) for every response
- **Queue job** — `tasks:check-overdue` scans overdue tasks hourly and dispatches `NotifyOverdueTaskJob`, which notifies the owner
- **Swagger / OpenAPI** via `darkaonline/l5-swagger` (PHP-attribute annotations)
- **Feature & unit tests** (`tests/`)

## Requirements

- PHP 8.2+
- Composer 2
- MySQL 8 (or SQLite for a zero-config local run)

## Installation

```bash
# 1. Install dependencies
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure the database in .env (see below), then run migrations + seeders
php artisan migrate --seed

# 4. Generate the Swagger docs
php artisan l5-swagger:generate

# 5. Serve
php artisan serve
# API base URL: http://localhost:8000/api
```

### Environment setup

Edit `.env` for MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=
```

Prefer a zero-setup run? Use SQLite instead:

```bash
touch database/database.sqlite
```

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

### Seeded demo account

After `migrate --seed` you can log in immediately:

- **Email:** `demo@example.com`
- **Password:** `password`

The seeder also creates two extra users, several projects across all statuses, and a mix of tasks including guaranteed overdue and completed ones so the dashboard has meaningful numbers.

## Running the queue and scheduler

The overdue-task notification is a queued job triggered by a scheduled command.

```bash
# Process queued jobs
php artisan queue:work

# Run the overdue scan once (normally scheduled hourly)
php artisan tasks:check-overdue

# In production the scheduler runs it hourly:
php artisan schedule:work
```

## Testing

```bash
php artisan test
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`) and cover authentication, project & task CRUD, ownership authorization, filtering/search, the dashboard aggregates, and the overdue queue job.

## API Documentation

Interactive Swagger UI is available after generation at:

```
http://localhost:8000/api/documentation
```

Regenerate anytime with `php artisan l5-swagger:generate` (or set `L5_SWAGGER_GENERATE_ALWAYS=true` in `.env` during development).

### Authentication

All protected endpoints require a Bearer token obtained from `/register` or `/login`:

```
Authorization: Bearer {token}
Accept: application/json
```

### Endpoints

| Method | Endpoint | Auth | Description |
| --- | --- | --- | --- |
| POST | `/api/register` | No | Register and receive a token |
| POST | `/api/login` | No | Log in and receive a token |
| GET | `/api/me` | Yes | Current authenticated user |
| POST | `/api/logout` | Yes | Revoke the current token |
| GET | `/api/dashboard` | Yes | Aggregated statistics |
| GET | `/api/projects` | Yes | List projects (`?status=`, `?search=`, `?per_page=`) |
| POST | `/api/projects` | Yes | Create a project |
| GET | `/api/projects/{project}` | Yes | View a project |
| PUT | `/api/projects/{project}` | Yes | Update a project |
| DELETE | `/api/projects/{project}` | Yes | Soft-delete a project |
| GET | `/api/projects/{project}/tasks` | Yes | List tasks (`?status=`, `?priority=`, `?search=`, `?per_page=`) |
| POST | `/api/projects/{project}/tasks` | Yes | Create a task |
| GET | `/api/projects/{project}/tasks/{task}` | Yes | View a task |
| PUT | `/api/projects/{project}/tasks/{task}` | Yes | Update a task |
| DELETE | `/api/projects/{project}/tasks/{task}` | Yes | Soft-delete a task |

### Field reference

**Project**

- `name` — required, string
- `description` — optional, string
- `status` — one of `active`, `completed`, `archived` (default `active`)

**Task**

- `title` — required, string
- `description` — optional, string
- `priority` — one of `low`, `medium`, `high` (default `medium`)
- `status` — one of `todo`, `in_progress`, `done` (default `todo`)
- `due_date` — optional, date (`YYYY-MM-DD`)

### Response format

Success:

```json
{
  "success": true,
  "message": "Project created successfully.",
  "data": { "id": 1, "name": "Website Redesign", "status": "active" }
}
```

List (paginated) responses include `data`, `links`, and `meta` blocks. Validation errors return `422`:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": { "name": ["The name field is required."] }
}
```

Status codes used: `200` OK, `201` Created, `401` Unauthenticated, `403` Forbidden (not the owner), `404` Not Found, `422` Validation error.

## Dashboard response

`GET /api/dashboard` returns, for the authenticated user:

```json
{
  "success": true,
  "message": "Dashboard summary retrieved.",
  "data": {
    "total_projects": 6,
    "active_projects": 4,
    "total_tasks": 42,
    "completed_tasks": 10,
    "pending_tasks": 32,
    "overdue_tasks": 5
  }
}
```

## Database design

```
users
  └── hasMany projects
projects
  └── hasMany tasks
tasks
  └── belongsTo project, belongsTo user
```

Migrations live in `database/migrations`. To produce a SQL dump for submission:

```bash
mysqldump -u root -p task_management > task_management.sql
```

## Postman collection

Import `postman_collection.json` (in the project root). It defines a `base_url` variable (`http://localhost:8000/api`) and a `token` variable; the **Login** request automatically saves the returned token into `{{token}}` so subsequent requests are authenticated.

## Project structure

```
app/
├── Console/Commands/CheckOverdueTasks.php
├── Enums/                       ProjectStatus, TaskPriority, TaskStatus
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php        base controller w/ response helpers + OpenAPI info
│   │   └── Api/                  Auth, Project, Task, Dashboard controllers
│   ├── Requests/                 Form Request validation
│   └── Resources/                API Resources
├── Jobs/NotifyOverdueTaskJob.php
├── Models/                       User, Project, Task
├── Notifications/TaskOverdueNotification.php
├── Policies/                     ProjectPolicy, TaskPolicy (ownership)
├── Providers/RepositoryServiceProvider.php
├── Repositories/
│   ├── Contracts/                interfaces
│   └── Eloquent/                 implementations
└── Services/                     Auth, Project, Task, Dashboard
database/
├── factories/ · migrations/ · seeders/
routes/api.php · routes/console.php
tests/Feature · tests/Unit
```

## Suggested Git commit history

Development was structured into incremental commits, for example:

1. `chore: scaffold Laravel 11 project and configuration`
2. `feat: add User/Project/Task models, enums, migrations, factories`
3. `feat: implement Sanctum authentication (register/login/logout)`
4. `feat: add projects CRUD with service + repository layers`
5. `feat: add nested tasks CRUD with filtering and search`
6. `feat: add dashboard aggregates endpoint`
7. `feat: add overdue-task queue job and scheduler`
8. `docs: add Swagger annotations and README`
9. `test: add feature and unit tests`
10. `chore: add Postman collection and seeders`

## License

MIT.
