# Broomees Backend Architecture

## Scope and technology

This repository implements the Broomees User Relationship, Reputation & Access-Control System as a PHP 8.3 / Laravel 12 JSON API. PostgreSQL is the production target; SQLite is supported for fast local and automated test execution. Redis is the production rate-limit and cache driver, with Laravel's database/array-compatible configuration available for local development.

## Domain model

| Entity | Primary responsibility | Integrity controls |
| --- | --- | --- |
| `users` | Account identity, password hash, reputation score, optimistic-lock version | UUID primary key, unique username, validation age range, unsigned version. |
| `relationships` | Directed half of a mutual friendship | Two rows are always created/deleted inside one transaction; unique `(user_id, friend_id)` and a `friend_id` index. |
| `hobbies` | Global hobby catalog | UUID primary key and unique name. |
| `user_hobbies` | User-to-hobby assignment | Unique `(user_id, hobby_id)` and hobby lookup index. |
| `blocked_relationships` | One-way blocked relationship used by the reputation penalty | Unique `(user_id, blocked_user_id)` and blocked-user lookup index. |
| `api_tokens` | Expiring and revocable bearer tokens | Raw token never persists; SHA-256 hash lookup, expiry, revocation timestamp, user foreign key, and lookup index. |

## API security

`POST /api/users` is the registration endpoint and `POST /api/auth/token` is the credential exchange endpoint. All remaining required API endpoints require an `Authorization: Bearer <token>` header. The custom token middleware derives a hash from the presented token, looks up only the hash, verifies with `hash_equals`, rejects expired/revoked tokens, and attaches the authenticated user and token to the request.

JSON-body endpoints require `Content-Type: application/json`. Invalid JSON or another content type returns the documented 400 error envelope. Form Request classes own validation and consistently return 400 errors rather than leaking framework exceptions. Domain exceptions are mapped centrally to 404, 409, or 401 responses.

## Reputation and transactions

`ReputationService` calculates the score only from authoritative relational data:

```text
unique friends + (shared hobbies × 0.5) + min(account-age-days / 30, 3) − blocked relationships
```

Shared hobbies count a user-hobby match for each direct friend that has the same hobby. The query is performed in SQL rather than by loading unbounded collections into PHP.

Relationship and hobby writes run inside a database transaction. Relevant users are locked in deterministic UUID order, the write occurs, affected users are recalculated, and the transaction commits only when every step succeeds. The database unique constraints remain the final duplicate-protection authority; unique-constraint exceptions become a domain 409 conflict.

## Optimistic locking and deletion

User updates issue a conditional update using both the UUID and supplied `version`, then increment the version atomically. Zero updated rows cause `OPTIMISTIC_LOCK_CONFLICT` (409). User deletion holds a transaction lock, rejects active relationships, rejects a reputation score above the config-driven threshold, and only then removes the user.

## Rate limiting and scheduling

`api-read` allows 120 requests per minute and `api-write` allows 30 requests per minute. The limiter key uses the authenticated API token UUID, never a source IP. Laravel's throttle middleware emits the standard rate-limit headers and 429 response; Redis is the production driver.

The `reputation:recalculate` Artisan command supports an optional `--user=<uuid>` and runs through Laravel's scheduler for cron-ready full recalculation.
