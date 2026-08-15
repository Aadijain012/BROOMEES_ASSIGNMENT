# Submission Verification

The Laravel backend was verified locally on 15 August 2026 with PHP 8.3 and SQLite for tests. The following checks passed:

| Check | Result |
| --- | --- |
| `composer validate --strict` | Passed. |
| `composer audit` | Passed with no security vulnerability advisories. |
| `composer lint` | Passed with Laravel Pint. |
| PHP syntax check across `app`, `database`, `routes`, `config`, and `tests` | Passed. |
| `composer test` | Passed: 16 tests and 237 assertions. |
| `php artisan migrate:fresh --seed --force` | Passed. |
| `php artisan reputation:recalculate` | Passed. |
| `php artisan schedule:list` | Confirmed the 02:00 daily recalculation. |
| `php artisan route:list --path=api` | Confirmed all 11 required API endpoints, plus health and Swagger endpoints. |
| HTTP smoke test | Health, token issuance, protected user list, metrics, and Swagger UI each returned HTTP 200. |

Docker configuration is included for PHP-FPM, Nginx, PostgreSQL 16, and Redis 7. Docker itself was not installed in the current sandbox, so `docker compose up` must be run once in a Docker-capable environment before external submission or deployment.

The optional static React dashboard remains in its separate project. This repository is the PHP backend required by the assignment.
