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
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Open: http://localhost

## Daily Commands

```bash
./vendor/bin/sail up -d
./vendor/bin/sail down
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan test
```

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
