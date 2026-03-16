<#
Create or update a Windows Scheduled Task to run Laravel's scheduler every minute.

Usage (PowerShell as Administrator):
  .\create_schtask.ps1 -PhpPath 'C:\path\to\php.exe' -AppPath 'C:\laragon\www\logistic'

If `-PhpPath` is omitted the script will attempt to find `php` on PATH.
#>

param(
    [string]$PhpPath = $null,
    [string]$AppPath = "C:\laragon\www\logistic",
    [string]$TaskName = "Logistic_Laravel_Scheduler"
)

function Write-Ok($s){ Write-Host $s -ForegroundColor Green }
function Write-Err($s){ Write-Host $s -ForegroundColor Red }

if (-not $PhpPath) {
    try { $phpResolve = Get-Command php -ErrorAction Stop; $PhpPath = $phpResolve.Source } catch { $PhpPath = $null }
}

if (-not $PhpPath -or -not (Test-Path $PhpPath)) {
    Write-Err "php.exe not found. Provide -PhpPath or ensure php is on PATH."; exit 2
}

if (-not (Test-Path $AppPath)) {
    Write-Err "App path '$AppPath' not found."; exit 2
}

$artisan = Join-Path $AppPath 'artisan'
if (-not (Test-Path $artisan)) { Write-Err "artisan not found at $artisan"; exit 2 }

$logDir = Join-Path $AppPath 'storage\logs'
if (-not (Test-Path $logDir)) { New-Item -ItemType Directory -Path $logDir -Force | Out-Null }
$logFile = Join-Path $logDir 'schedule.log'

# Build the command to run. Quote paths to handle spaces.
$tr = '"' + $PhpPath + '" "' + $artisan + '" schedule:run >> "' + $logFile + '" 2>&1'

Write-Host "Creating scheduled task '$TaskName' to run every minute..."

# Create or update the task using schtasks.exe
$cmd = "schtasks /Create /SC MINUTE /MO 1 /F /TN `"$TaskName`" /TR `$tr` /RL HIGHEST /RU SYSTEM"

Write-Host $cmd

$res = cmd /c $cmd
Write-Ok "Scheduled task created or updated. Logs will be appended to: $logFile"
