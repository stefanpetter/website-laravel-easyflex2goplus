#Requires -Version 5.1

[CmdletBinding()]
param(
    [string]$ScraperRoot = $PSScriptRoot,
    [string]$EnvFile = (Join-Path $PSScriptRoot ".env"),
    [string]$LoginUrl,
    [string]$ScraperUsername,
    [string]$Password,
    [string]$CsvUploadUrl,
    [string]$CsvUploadBearerToken,
    [switch]$SkipDependencyInstall,
    [switch]$SkipBrowserInstall,
    [string]$LogsDir = (Join-Path $PSScriptRoot "logs"),
    [int]$MaxLogFiles = 10
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$workflowDefaults = @{
    USE_PLANNING_CURRENT_WEEK    = "true"
    HEADLESS                     = "true"
    WAIT_AFTER_LOGIN_MS          = "5000"
    EXPORT_PROFILE_APPLY_DELAY_MS = "5000"
    SCREENSHOT_AFTER_DOWNLOAD    = "true"
    CSV_UPLOAD_FILE_FIELD        = "file"
    CSV_UPLOAD_MODE              = "multipart"
    CSV_UPLOAD_SUCCESS_STATUS    = "201"
}

function Write-Log {
    param(
        [string]$Level,
        [string]$Message
    )

    $timestamp = (Get-Date).ToString("yyyy-MM-dd HH:mm:ss.fff")
    $line = "[{0}] [{1}] {2}" -f $timestamp, $Level.ToUpperInvariant(), $Message

    if ($Level -eq "ERROR") {
        [Console]::Error.WriteLine($line)
    } else {
        Write-Host $line
    }

    if ($script:LogFile) {
        Add-Content -LiteralPath $script:LogFile -Value $line -Encoding UTF8
    }
}

function Resolve-CommandPath {
    param(
        [Parameter(Mandatory = $true)]
        [string[]]$Names
    )

    foreach ($name in $Names) {
        $command = Get-Command -Name $name -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($null -ne $command) {
            return $command.Source
        }
    }

    throw "Required command not found. Tried: $($Names -join ', ')"
}

function Import-DotEnvFile {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Path
    )

    if (-not (Test-Path -LiteralPath $Path)) {
        Write-Log "WARN" "No .env file found at $Path. Falling back to existing process environment."
        return
    }

    Write-Log "INFO" "Loading environment variables from $Path"

    foreach ($line in Get-Content -LiteralPath $Path) {
        $trimmed = $line.Trim()
        if ([string]::IsNullOrWhiteSpace($trimmed) -or $trimmed.StartsWith("#")) {
            continue
        }

        if ($trimmed.StartsWith("export ")) {
            $trimmed = $trimmed.Substring(7).Trim()
        }

        $separatorIndex = $trimmed.IndexOf("=")
        if ($separatorIndex -lt 0) {
            continue
        }

        $name = $trimmed.Substring(0, $separatorIndex).Trim()
        if ([string]::IsNullOrWhiteSpace($name)) {
            continue
        }
        $value = $trimmed.Substring($separatorIndex + 1)

        if ($value.Length -ge 2) {
            $quote = $value.Substring(0, 1)
            if (($quote -eq '"' -or $quote -eq "'") -and $value.EndsWith($quote)) {
                $value = $value.Substring(1, $value.Length - 2)
            }
        }

        [System.Environment]::SetEnvironmentVariable($name, $value, "Process")
    }
}

function Set-ProcessEnvIfProvided {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Name,
        [AllowEmptyString()]
        [string]$Value
    )

    if ([string]::IsNullOrEmpty($Value)) {
        return
    }

    [System.Environment]::SetEnvironmentVariable($Name, $Value, "Process")
}

function Set-DefaultEnvIfMissing {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Name,
        [Parameter(Mandatory = $true)]
        [string]$Value
    )

    $currentValue = [System.Environment]::GetEnvironmentVariable($Name, "Process")
    if ([string]::IsNullOrWhiteSpace($currentValue)) {
        [System.Environment]::SetEnvironmentVariable($Name, $Value, "Process")
    }
}

function Assert-RequiredEnv {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Name
    )

    $value = [System.Environment]::GetEnvironmentVariable($Name, "Process")
    if ([string]::IsNullOrWhiteSpace($value)) {
        throw "Missing required environment variable: $Name"
    }
}

function Invoke-Step {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Name,
        [Parameter(Mandatory = $true)]
        [string]$Executable,
        [Parameter(Mandatory = $true)]
        [string[]]$Arguments
    )

    Write-Log "INFO" "Starting step: $Name"
    & $Executable @Arguments 2>&1 | ForEach-Object {
        $line = $_.ToString()
        if (-not [string]::IsNullOrWhiteSpace($line)) {
            if ($script:LogFile) {
                Add-Content -LiteralPath $script:LogFile -Value $line -Encoding UTF8
            }
            Write-Host $line
        }
    }

    $stepExitCode = $LASTEXITCODE
    if ($stepExitCode -ne 0) {
        throw "Step failed: $Name (exit code $stepExitCode). Check the log output above for details."
    }
    Write-Log "INFO" "Completed step: $Name"
}

# Set up log file
if (-not (Test-Path -LiteralPath $LogsDir)) {
    New-Item -ItemType Directory -Path $LogsDir | Out-Null
}
$script:LogFile = Join-Path $LogsDir ("scraper-{0}.log" -f (Get-Date).ToString("yyyyMMdd_HHmmss"))
Add-Content -LiteralPath $script:LogFile -Value ("=== Scraper run started {0} ===" -f (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")) -Encoding UTF8

# Rotate: keep only the last $MaxLogFiles log files
$existingLogs = @(Get-ChildItem -LiteralPath $LogsDir -Filter "scraper-*.log" -File | Sort-Object Name)
if ($existingLogs.Count -gt $MaxLogFiles) {
    $existingLogs | Select-Object -First ($existingLogs.Count - $MaxLogFiles) | Remove-Item -Force
}

try {
    $resolvedScraperRoot = (Resolve-Path -LiteralPath $ScraperRoot).Path
    Import-DotEnvFile -Path $EnvFile

    Set-ProcessEnvIfProvided -Name "LOGIN_URL" -Value $LoginUrl
    Set-ProcessEnvIfProvided -Name "SCRAPER_USERNAME" -Value $ScraperUsername
    Set-ProcessEnvIfProvided -Name "PASSWORD" -Value $Password
    Set-ProcessEnvIfProvided -Name "CSV_UPLOAD_URL" -Value $CsvUploadUrl
    Set-ProcessEnvIfProvided -Name "CSV_UPLOAD_BEARER_TOKEN" -Value $CsvUploadBearerToken

    foreach ($entry in $workflowDefaults.GetEnumerator()) {
        Set-DefaultEnvIfMissing -Name $entry.Key -Value $entry.Value
    }

    Assert-RequiredEnv -Name "LOGIN_URL"
    Assert-RequiredEnv -Name "SCRAPER_USERNAME"
    Assert-RequiredEnv -Name "PASSWORD"

    $npm = Resolve-CommandPath -Names @("npm.cmd", "npm")
    $npx = Resolve-CommandPath -Names @("npx.cmd", "npx")
    $node = Resolve-CommandPath -Names @("node.exe", "node")

    Write-Log "INFO" "Using scraper root: $resolvedScraperRoot"
    Push-Location -LiteralPath $resolvedScraperRoot

    try {
        if (-not $SkipDependencyInstall) {
            Invoke-Step -Name "Install npm dependencies" -Executable $npm -Arguments @("ci")
        } else {
            Write-Log "INFO" "Skipping npm dependency install."
        }

        if (-not $SkipBrowserInstall) {
            Invoke-Step -Name "Install Playwright Chromium" -Executable $npx -Arguments @("playwright", "install", "chromium")
        } else {
            Write-Log "INFO" "Skipping Playwright browser install."
        }

        Invoke-Step -Name "Run download script" -Executable $node -Arguments @("src/download.js")
        Invoke-Step -Name "Convert XLSX to CSV" -Executable $npm -Arguments @("run", "convert:csv")

        $downloadsDir = Join-Path $resolvedScraperRoot "downloads"
        $csvFiles = @()
        if (Test-Path -LiteralPath $downloadsDir) {
            $csvFiles = @(Get-ChildItem -LiteralPath $downloadsDir -Filter "*.csv" -File)
        }

        if ($csvFiles.Count -eq 0) {
            throw "No CSV files were produced in downloads directory: $downloadsDir"
        }

        Write-Log "INFO" ("CSV files ready for upload: {0}" -f ($csvFiles.Name -join ", "))
        Invoke-Step -Name "Upload CSV to API" -Executable $npm -Arguments @("run", "upload:csv")
        Write-Log "INFO" "Scraper pipeline completed successfully."
    } finally {
        Pop-Location
    }
} catch {
    Write-Log "ERROR" $_.Exception.Message
    if ($script:LogFile) {
        Add-Content -LiteralPath $script:LogFile -Value ("=== Scraper run FAILED {0} ===" -f (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")) -Encoding UTF8
    }
    exit 1
}

if ($script:LogFile) {
    Add-Content -LiteralPath $script:LogFile -Value ("=== Scraper run completed {0} ===" -f (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")) -Encoding UTF8
}
