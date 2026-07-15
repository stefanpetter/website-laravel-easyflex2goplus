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
