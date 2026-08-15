# Broomees User Relationship, Reputation & Access-Control System

Broomees is a secure **PHP 8.3 / Laravel 12** REST API for user accounts, mutual relationships, hobbies, calculated reputation scores, token-based access control, and system-wide reputation metrics. The optional React control-center dashboard is independent; this repository is the backend submission.

## Architecture

| Concern | Decision |
| --- | --- |
| Framework | Laravel 12, PHP 8.3, Eloquent, Form Requests, API Resources, Service Layer, and a small User Repository. |
| Database | PostgreSQL 16 in Docker; SQLite for fast local and test execution. |
| Identity | UUID primary keys and unique usernames. |
| Relationships | Two directed rows inserted/deleted atomically; composite primary key prevents duplicates. |
| Reputation | Authoritative SQL-backed calculation, persisted after related writes. |
| Tokens | Opaque 64-byte bearer token, SHA-256 hash only in storage, expiry and revocation support. |
| Rate limits | Per token: 120 reads/minute, 30 writes/minute. Redis is the production cache driver. |

The complete index, transaction, locking, and formula rationale is in [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

## Setup

Install dependencies, create a local configuration, and use SQLite for the fastest local workflow:

```bash
composer install
cp .env.example .env
php artisan key:generate

# For local SQLite, set DB_CONNECTION=sqlite in .env.
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

The demo seeder creates ten users, shared hobbies, relationships, and blocked relationships. Seeded users use password `DemoPassword123!`; use `alice` to issue a demo token.

## Docker

The production-like Docker Compose stack includes PHP-FPM 8.3, Nginx, PostgreSQL 16, and Redis 7.

```bash
cp .env.example .env
# Set a strong DB_PASSWORD in .env.
docker compose up --build -d
docker compose exec app php artisan migrate --seed --force
```

The API runs at `http://localhost:8000`. Stop it with `docker compose down`.

## Render Free deployment

The repository includes `render.yaml` and a root Dockerfile for Render’s free Docker web-service plan. Deploy the Blueprint from Render after connecting this GitHub repository. Enter `APP_KEY` and the generated Render service URL as `APP_URL` when prompted; Render provisionally manages the PostgreSQL and Key Value dependencies.

Render Free is suitable for an assignment demo, not a durable production workload: the web service spins down after 15 minutes idle, the database expires after 30 days, Key Value has no persistent storage, and free plans cannot run a managed cron service. The API still recalculates reputation after each relevant write, while the full scheduled recalculation remains available in the codebase for an upgraded scheduler later.

## API documentation and health

Swagger UI: `GET /api/documentation`  
OpenAPI document: `GET /api/openapi.yaml`  
Health check: `GET /api/health` returning `{"status":"ok"}`.

## Authentication

`POST /api/users` registers a user. `POST /api/auth/token` exchanges username/password for an expiring bearer token. All remaining required endpoints require `Authorization: Bearer <token>`.

```bash
curl -X POST http://localhost:8000/api/users \
  -H 'Content-Type: application/json' \
  -d '{"username":"john","age":25,"password":"StrongPassword123!"}'

curl -X POST http://localhost:8000/api/auth/token \
  -H 'Content-Type: application/json' \
  -d '{"username":"john","password":"StrongPassword123!"}'
```

Raw token values are returned only on issue, never stored, and never logged. Token storage records only a SHA-256 hash, expiry, revocation timestamp, and user association.

## Endpoint table

| Method | Path | Purpose |
| --- | --- | --- |
| POST | `/api/auth/token` | Issue an expiring bearer token. |
| GET | `/api/users` | Paginated and searchable user list. |
| GET | `/api/users/{id}` | User details with hobbies and friends. |
| POST | `/api/users` | Register a user. |
| PUT | `/api/users/{id}` | Optimistic-lock user update. |
| DELETE | `/api/users/{id}` | Guarded user deletion. |
| POST | `/api/users/{id}/relationships` | Create a mutual relationship. |
| DELETE | `/api/users/{id}/relationships` | Remove a mutual relationship. |
| POST | `/api/users/{id}/hobbies` | Assign a hobby. |
| DELETE | `/api/users/{id}/hobbies` | Remove a hobby. |
| GET | `/api/metrics/reputation` | System reputation metrics. |

## Protected API examples

```bash
TOKEN='<issued-token>'
USER_ID='<user-uuid>'
FRIEND_ID='<friend-uuid>'
HOBBY_ID='<hobby-uuid>'

curl "http://localhost:8000/api/users?per_page=20&search=john" -H "Authorization: Bearer $TOKEN"
curl "http://localhost:8000/api/users/$USER_ID" -H "Authorization: Bearer $TOKEN"
curl -X PUT "http://localhost:8000/api/users/$USER_ID" -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -d '{"age":26,"version":1}'
curl -X POST "http://localhost:8000/api/users/$USER_ID/relationships" -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -d "{\"friend_id\":\"$FRIEND_ID\"}"
curl -X DELETE "http://localhost:8000/api/users/$USER_ID/relationships" -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -d "{\"friend_id\":\"$FRIEND_ID\"}"
curl -X POST "http://localhost:8000/api/users/$USER_ID/hobbies" -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -d "{\"hobby_id\":\"$HOBBY_ID\"}"
curl -X DELETE "http://localhost:8000/api/users/$USER_ID/hobbies" -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -d "{\"hobby_id\":\"$HOBBY_ID\"}"
curl http://localhost:8000/api/metrics/reputation -H "Authorization: Bearer $TOKEN"
curl -X DELETE "http://localhost:8000/api/users/$USER_ID" -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' -d '{}'
```

## Reputation, concurrency, and deletion rules

```text
unique friends + (shared hobbies × 0.5) + min(account-age-days / 30, 3) − blocked relationships
```

Relationship and hobby writes use database transactions. Relevant users are locked in deterministic UUID order, the mutation runs, authoritative relational data is used to recalculate reputation, and all changes commit together. The database composite key is the final duplicate-protection authority. The test suite covers the deterministic competing-write scenario and asserts that exactly one mutual pair is stored.

User updates require the current `version`; stale writes return `409 OPTIMISTIC_LOCK_CONFLICT`. Deletion returns 409 if active relationships remain or the reputation exceeds `REPUTATION_DELETION_THRESHOLD`.

## Performance, rate limits, and response format

Important indexes cover usernames; relationship pairs and friend lookup; user-hobby pairs and hobby lookup; blocked relationships; and token lookup. User listings use stable username ordering and bounded pagination. Shared hobbies are calculated using SQL joins; full recalculation uses `chunkById`.

GET endpoints allow 120 requests/minute and protected write endpoints allow 30 requests/minute, keyed by bearer token. Laravel returns 429 with standard limit headers including `X-RateLimit-Limit`, `X-RateLimit-Remaining`, and `Retry-After`.

Success responses follow `{"success":true,"data":...,"meta":...}`. Errors use `{"success":false,"error":{"code":"...","message":"..."}}`. JSON body endpoints reject malformed JSON, invalid data, unsupported content type, and invalid UUIDs with consistent 400 responses.

## Testing and quality checks

```bash
composer test
composer lint
composer format
composer audit
```

Coverage includes tokens, pagination, malformed JSON, content-type handling, invalid UUIDs, optimistic locking, mutual relationships, duplicate protection, concurrency behavior, hobbies, reputation formula and age cap, deletion rules, and token-scoped rate limits.

## Scheduler and production deployment

Run the scheduler every minute:

```cron
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

Set `APP_ENV=production`, `APP_DEBUG=false`, a unique `APP_KEY`, secure PostgreSQL credentials, a Redis cache store, HTTPS at the reverse proxy, and appropriate token/deletion/rate-limit configuration. Do not commit `.env`, raw tokens, passwords, database credentials, or generated secrets.

## Known tradeoff

SQLite supports automated tests and a lightweight local workflow. PostgreSQL is the recommended production database because it supports the intended locking semantics and database check constraints. The schema supports blocked relationships for reputation; the assignment does not require a public block-management endpoint, so blocked rows are seeded or administratively maintained.
