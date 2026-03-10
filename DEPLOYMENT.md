# Deploying Laravel on Vercel with Turso (libSQL) Database

A complete guide to deploying a Laravel 12 app on Vercel using Turso as the remote database, with database migrations run via GitHub Actions.

---

## Overview

| Concern | Tool |
|---------|------|
| Hosting | Vercel (via `vercel-php` runtime) |
| Database | Turso (libSQL, remote HTTP) |
| Migrations | GitHub Actions (on push to `master`) |

Vercel handles serving the app. GitHub Actions handles running migrations against Turso on every deployment. These happen in parallel on each push.

---

## 1. Install the Turso Laravel Package

`turso/libsql-laravel` v0.1.x only supports Laravel 11. v0.2.0 supports Laravel 12 but depends on `turso/libsql dev-master`, which requires `minimum-stability: dev`.

Change `minimum-stability` in `composer.json`:

```json
"minimum-stability": "dev",
"prefer-stable": true
```

`prefer-stable: true` ensures all other packages still resolve to stable releases.

Then install:

```bash
composer require turso/libsql-laravel
```

---

## 2. Configure the Database Connection

In `config/database.php`, add the `libsql` connection inside the `connections` array:

```php
'libsql' => [
    'driver'   => 'libsql',
    'url'      => env('TURSO_DATABASE_URL'),
    'password' => env('TURSO_AUTH_TOKEN'),
    'database' => null,   // must be null to force remote mode
    'prefix'   => '',
],
```

**Why `database => null`:** The package detects connection mode based on whether `database` (local path) and `url` are set:
- `database = null` + `url` set → **remote** mode (correct for Turso)
- Both set → **remote_replica** mode (tries to open the URL as a local file — wrong)

**Why hardcode `driver => 'libsql'`:** Using `env('DB_CONNECTION', 'libsql')` would resolve to your local `.env` value (e.g. `sqlite`) and load the wrong driver.

---

## 3. Fix Migration Compatibility with LibSQL

LibSQL is SQLite-based and has two limitations to account for in migrations.

### 3a. Remove `.after()` calls

`.after()` is MySQL-only. In LibSQL it generates an `Expression` object that cannot be converted to string, crashing the migration.

```php
// Wrong — crashes on LibSQL
$table->string('type')->default('invoice')->after('payment_id');

// Correct — column order doesn't matter functionally
$table->string('type')->default('invoice');
```

Search all migrations for these before deploying:

```bash
grep -rn "->after\(\|->first\(\)" database/migrations/
```

### 3b. Remove foreign key constraints from `Schema::table` migrations

LibSQL does not support adding foreign key constraints via `ALTER TABLE`. Using `constrained()` or `dropForeignIdFor()` inside `Schema::table(...)` will crash.

```php
// Wrong — crashes on LibSQL in ALTER TABLE context
$table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();

// Correct — plain column, no FK constraint
$table->unsignedBigInteger('payment_id')->nullable();
```

> **Note:** `constrained()` inside `Schema::create(...)` (i.e. new table creation) works fine.

---

## 4. Set Up Vercel

### 4a. Add Vercel config files

**`vercel.json`** — routes all requests through the PHP function:

```json
{
    "version": 2,
    "framework": null,
    "functions": {
        "api/index.php": { "runtime": "vercel-php@0.9.0" }
    },
    "routes": [{
        "src": "/(.*)",
        "dest": "/api/index.php"
    }],
    "env": {
        "APP_ENV": "production",
        "APP_DEBUG": "false",
        "APP_URL": "https://your-project.vercel.app",

        "APP_CONFIG_CACHE": "/tmp/config.php",
        "APP_EVENTS_CACHE": "/tmp/events.php",
        "APP_PACKAGES_CACHE": "/tmp/packages.php",
        "APP_ROUTES_CACHE": "/tmp/routes.php",
        "APP_SERVICES_CACHE": "/tmp/services.php",
        "VIEW_COMPILED_PATH": "/tmp",

        "DB_CONNECTION": "libsql",
        "CACHE_DRIVER": "array",
        "LOG_CHANNEL": "stderr",
        "SESSION_DRIVER": "cookie"
    }
}
```

**`api/index.php`** — entrypoint for the Vercel function:

```php
<?php
require __DIR__ . "/../public/index.php";
```

### 4b. Set sensitive environment variables in Vercel dashboard

Go to **Vercel → Project → Settings → Environment Variables** and add:

| Variable | Value |
|----------|-------|
| `APP_KEY` | your Laravel app key (`base64:...`) |
| `TURSO_DATABASE_URL` | `libsql://your-db.turso.io` |
| `TURSO_AUTH_TOKEN` | your Turso auth token |

> Do not put secrets in `vercel.json` — it is committed to source control.

### 4c. Why you cannot run migrations from Vercel's build step

`buildCommand` in `vercel.json` runs in a Node.js environment. PHP and Composer are only available inside the `vercel-php` function container at runtime, not during the top-level build. Running `php artisan migrate` there will exit with code 127 (command not found).

Migrations must be run from outside Vercel — GitHub Actions is the right place.

---

## 5. Set Up GitHub Actions for Migrations

### 5a. How `turso/libsql` works

The `turso/libsql` package uses **PHP FFI** to load a pre-compiled native shared library (`liblibsql.so`) bundled inside `vendor/turso/libsql/lib/`. This means:

- `ext-ffi` must be enabled in PHP
- `ffi.enable = true` must be set in `php.ini`
- PHP **8.3+** is required (`turso/libsql` declares `"php": ">=8.3"`)

### 5b. Create the workflow

**`.github/workflows/deploy.yml`:**

```yaml
name: Migrate Database

on:
  push:
    branches: [master]

jobs:
  migrate:
    name: Run Migrations
    runs-on: ubuntu-latest

    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, curl, zip, pdo, ffi
          ini-values: ffi.enable=true
          coverage: none

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: vendor
          key: composer-${{ hashFiles('composer.lock') }}
          restore-keys: composer-

      - name: Install Composer dependencies
        run: composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --ignore-platform-reqs

      - name: Verify database connection target
        env:
          TURSO_DATABASE_URL: ${{ secrets.TURSO_DATABASE_URL }}
          TURSO_AUTH_TOKEN: ${{ secrets.TURSO_AUTH_TOKEN }}
        run: |
          if [ -z "$TURSO_DATABASE_URL" ]; then
            echo "ERROR: TURSO_DATABASE_URL secret is not set"
            exit 1
          fi
          if [ -z "$TURSO_AUTH_TOKEN" ]; then
            echo "ERROR: TURSO_AUTH_TOKEN secret is not set"
            exit 1
          fi
          echo "DB_CONNECTION: libsql"
          echo "TURSO_DATABASE_URL: ${TURSO_DATABASE_URL:0:30}..."

      - name: Run migrations
        env:
          APP_KEY: ${{ secrets.APP_KEY }}
          APP_ENV: production
          DB_CONNECTION: libsql
          TURSO_DATABASE_URL: ${{ secrets.TURSO_DATABASE_URL }}
          TURSO_AUTH_TOKEN: ${{ secrets.TURSO_AUTH_TOKEN }}
          SESSION_DRIVER: cookie
          CACHE_STORE: array
          LOG_CHANNEL: stderr
          APP_CONFIG_CACHE: /tmp/config.php
          APP_EVENTS_CACHE: /tmp/events.php
          APP_PACKAGES_CACHE: /tmp/packages.php
          APP_ROUTES_CACHE: /tmp/routes.php
          APP_SERVICES_CACHE: /tmp/services.php
        run: php artisan migrate --force
```

**Why `--ignore-platform-reqs`:** The `composer.lock` is generated locally and may have different platform extension requirements than the CI runner. This flag allows installation to proceed regardless.

### 5c. Add GitHub repository secrets

Go to **GitHub → Repository → Settings → Secrets and variables → Actions → New repository secret** and add:

| Secret | Where to get it |
|--------|----------------|
| `APP_KEY` | Local `.env` — the `APP_KEY=base64:...` value |
| `TURSO_DATABASE_URL` | Turso dashboard → your database → Connect tab |
| `TURSO_AUTH_TOKEN` | Turso dashboard → your database → Generate Token |

---

## 6. Deployment Flow

On every `git push` to `master`:

```
push to master
├── GitHub Actions
│   ├── install PHP 8.3 with FFI enabled
│   ├── composer install
│   ├── verify secrets are set
│   └── php artisan migrate --force  →  Turso
│
└── Vercel (triggered by GitHub integration)
    ├── composer install (via vercel-php runtime)
    └── serve the app
```

Both run in parallel. Since Turso migrations are additive (new columns/tables), the app can serve safely while migrations are running.

---

## Troubleshooting

| Error | Cause | Fix |
|-------|-------|-----|
| `Your lock file does not contain a compatible set of packages` | Platform mismatch between local and CI | Add `--ignore-platform-reqs` to `composer install` |
| `Undefined array key "prefix"` | Missing `prefix` key in libsql connection config | Add `'prefix' => ''` to the `libsql` connection |
| `Object of class Expression could not be converted to string` | `.after()` or `constrained()` used in `Schema::table()` | Remove `.after()` calls; use plain columns without FK constraints in alter migrations |
| `Unable to open local database libsql://...` | `database` key set alongside `url`, triggering `remote_replica` mode | Set `'database' => null` in the libsql config |
| `Command exited with 127` on Vercel build | `php`/`composer` not available in Vercel's build environment | Do not use `buildCommand` for PHP — use GitHub Actions instead |
| `TURSO_DATABASE_URL secret is not set` | GitHub secrets not configured | Add secrets in repo Settings → Secrets and variables → Actions |
| Migrations run but don't reach Turso | `ext-ffi` not enabled, FFI falls back or fails silently | Add `ffi` to extensions and `ffi.enable=true` to `ini-values` in the workflow |
