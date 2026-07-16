# web-download-bot

Automate login + file download from a website with no API using Playwright.

## 1) Prerequisites

Install Node.js 20+ (includes npm).

Ubuntu/Debian example:

```bash
sudo apt update
sudo apt install -y nodejs npm
```

Check:

```bash
node -v
npm -v
```

## 2) Install dependencies

```bash
npm install
npx playwright install
```

`playwright install` downloads browser binaries required by automation.

## 3) Configure environment

```bash
cp .env.example .env
```

Edit `.env` with your real URLs, credentials, and selectors.

For your case:

- `LOGIN_URL=https://uniepool.easyflex2go.nl/login`
- `SCRAPER_USERNAME=<your login username>` (use `SCRAPER_USERNAME` to avoid conflicts with the Windows built-in `USERNAME` environment variable)
- `LOGIN_ROLE=Relatie`
- `USE_PLANNING_CURRENT_WEEK=true`
- `EXPORT_PROFILE_LABEL=def export planning chauffeur`
- Keep `HEADLESS=false` while validating flow

When `USE_PLANNING_CURRENT_WEEK=true`, the script builds:

- `ps` = Monday of current week
- `pe` = Sunday of current week

on `https://uniepool.easyflex2go.nl/planning/assigner` with query params matching your Planning view.

## 4) Discover selectors quickly

Run recorder and inspect the page interactions:

```bash
npm run codegen -- https://uniepool.easyflex2go.nl/login
```

Or run with visual debugger:

```bash
npm run download:debug
```

## 5) Run download

```bash
npm run download
```

Downloaded files will be saved in `downloads/`.

## 6) Convert XLSX to CSV

If the downloaded file is an `.xlsx`, convert it to `.csv` with:

```bash
npm run convert:csv
```

The converter processes all `.xlsx` files in `downloads/` and writes a `.csv` file with the same base name using the first worksheet.

In GitHub Actions, this conversion now runs automatically right after the download step.

## 7) Upload CSV (POST)

To upload converted CSV files to the API endpoint:

```bash
npm run upload:csv
```

By default, files are posted to:

- `https://sentrex-acc.trics.online/api/planning/csv`

Upload behavior:

- Sends all `.csv` files from `downloads/`
- Uses `multipart/form-data` by default, with optional raw `text/csv` fallback
- File field name defaults to `file`
- Sends API-oriented headers (`Accept: application/json`)
- Does not treat redirects as success
- Requires HTTP `201` by default

Optional environment variables:

- `CSV_UPLOAD_URL` (override target URL)
- `CSV_UPLOAD_FILE_FIELD` (override multipart field name)
- `CSV_UPLOAD_BEARER_TOKEN` (adds `Authorization: Bearer <token>`)
- `CSV_UPLOAD_MODE` (`auto` | `multipart` | `raw`, default: `auto`)
- `CSV_UPLOAD_SUCCESS_STATUS` (expected HTTP status, default: `201`)

`CSV_UPLOAD_MODE=auto` tries multipart first and, if that fails, retries as raw `text/csv` body.

In GitHub Actions, upload runs automatically after the conversion step. If conversion fails, upload does not run.

## 8) Run the full pipeline on Windows

To mirror the GitHub Actions pipeline on Windows, use:

```powershell
.\run-scraper-windows.ps1
```

The PowerShell script runs the same end-to-end sequence as `.github/workflows/scraper-download.yml`:

1. `npm ci`
2. `npx playwright install chromium`
3. `node src/download.js`
4. `npm run convert:csv`
5. verify that `downloads\*.csv` exists
6. `npm run upload:csv`

The script is non-interactive by default, writes progress to stdout/stderr, and exits with code `1` if any step fails.

### Windows configuration

Create `scraper/.env` as usual:

```powershell
Copy-Item .env.example .env
```

Set the same values used by the workflow/local scraper, especially:

- `LOGIN_URL`
- `SCRAPER_USERNAME` (recommended on Windows instead of `USERNAME`)
- `PASSWORD`
- `LOGIN_ROLE`
- `USE_PLANNING_CURRENT_WEEK=true`
- `PLANNING_URL_BASE`
- `PLANNING_V`
- `PLANNING_P`

For upload, either set script parameters / machine environment variables, or add these to `.env` so the Windows wrapper can load them before `upload:csv` runs:

```dotenv
CSV_UPLOAD_URL=https://your-server.example.com/api/csv/upload
CSV_UPLOAD_FILE_FIELD=file
CSV_UPLOAD_BEARER_TOKEN=YOUR_TOKEN_HERE
CSV_UPLOAD_MODE=multipart
CSV_UPLOAD_SUCCESS_STATUS=201
```

Set `CSV_UPLOAD_URL` explicitly to the same endpoint you use in GitHub Actions or your local environment. Prefer `CSV_UPLOAD_BEARER_TOKEN` (or `-CsvUploadBearerToken`) for the credential itself. If your server still expects a query-string token, provide the full URL yourself via `CSV_UPLOAD_URL`.

If you leave the other values unset, the wrapper applies the current workflow defaults for:

- `USE_PLANNING_CURRENT_WEEK=true`
- `HEADLESS=true`
- `WAIT_AFTER_LOGIN_MS=5000`
- `EXPORT_PROFILE_APPLY_DELAY_MS=5000`
- `SCREENSHOT_AFTER_DOWNLOAD=true`
- `CSV_UPLOAD_FILE_FIELD=file`
- `CSV_UPLOAD_MODE=multipart`
- `CSV_UPLOAD_SUCCESS_STATUS=201`

Optional parameters:

- `-EnvFile <path>` to load a different `.env` file
- `-ScraperUsername <value>`
- `-Password <value>`
- `-LoginUrl <value>`
- `-CsvUploadUrl <value>`
- `-CsvUploadBearerToken <value>`
- `-SkipDependencyInstall` to skip `npm ci`
- `-SkipBrowserInstall` to skip `npx playwright install chromium`

Example:

```powershell
.\run-scraper-windows.ps1 -EnvFile "C:\EasyFlex\scraper\.env" -SkipDependencyInstall -SkipBrowserInstall
```

### Task Scheduler setup

You can schedule either the PowerShell script directly or the helper batch file.

Direct PowerShell action:

- **Program/script:** `powershell.exe`
- **Add arguments:** `-NoProfile -ExecutionPolicy Bypass -File "C:\path\to\website-laravel-easyflex2goplus\scraper\run-scraper-windows.ps1"`
- **Start in:** `C:\path\to\website-laravel-easyflex2goplus\scraper`

Batch file alternative:

- **Program/script:** `C:\path\to\website-laravel-easyflex2goplus\scraper\run-scraper-windows.cmd`
- **Start in:** `C:\path\to\website-laravel-easyflex2goplus\scraper`

For unattended runs, use a `.env` file with the required values and keep `WAIT_FOR_MANUAL_CONFIRM=false`.

## MFA / CAPTCHA notes

- If site has MFA, set `WAIT_FOR_MANUAL_CONFIRM=true` and `HEADLESS=false`.
- Complete MFA manually in browser, then press Enter in terminal.
- CAPTCHA may block full automation; manual step is often required.

## Troubleshooting

- Login fails: selectors likely changed.
- For Easyflex role switch, verify role radio text is exactly `Relatie` in `LOGIN_ROLE`.
- Wrong Planning week: check local machine date/timezone.
- Click does nothing: use a more specific `DOWNLOAD_BUTTON_SELECTOR`.
- No download event: site may open new tab or use XHR; adapt script to capture network or click target in popup.
- Bot detection: add realistic delays and keep `HEADLESS=false` for local debugging.
