# EasyFlex2GoPlus

Laravel 11 project running locally with Laravel Sail on WSL2.

## Prerequisites (WSL2)

1. Docker Desktop installed on Windows.
2. Docker Desktop WSL integration enabled for your distro.
3. Project stored inside Linux filesystem (recommended path: `/home/...`, not `/mnt/c/...`).
4. PHP and Composer installed in WSL.

## First-Time Setup

Run these commands from the project root:

```bash
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
```

Open: http://localhost

Use token access in URL, for example: `http://localhost/?token=token123`

## Daily Commands

```bash
./vendor/bin/sail up -d
./vendor/bin/sail down
```

## Schedule Regression Check

After running `./vendor/bin/sail artisan migrate`, run:

```bash
./vendor/bin/sail php tests/planning-schedules.php
```

This checks individual worker schedules, duplicates, overnight shifts, calendar
rendering, person-card statuses, custom events, incomplete expanded exports, and
older CSV exports. Fixture database changes are rolled back.

## Export Profile Compatibility

The `EF2GO Plus uitgebreid` profile must include `Ingeplande entiteiten - Status`
alongside the individual-time and absence columns. The previous release's profile
omitted this column. Add it in Easyflex and re-export/re-import affected weeks to
restore vacation and event statuses. Incomplete expanded exports are rejected
without replacing existing planning data. Individual schedules and colored
status labels remain enabled.

## If Docker Fails in WSL2

If you get `/usr/bin/docker: Input/output error`:

1. Restart Docker Desktop.
2. In Windows PowerShell, run:

```powershell
wsl --shutdown
```

3. Re-open WSL and verify:

```bash
docker --version
docker compose version
```

4. Start Sail again:

```bash
./vendor/bin/sail up -d
```
