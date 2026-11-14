# get-token.ps1
#
# Writes one value of the GitHub bot setup to stdout (no trailing newline),
# so it can be captured into a shell variable for a single command.
#
#   -Value Token     decrypts the DPAPI blob (default) - never echo or log it
#   -Value BotLogin  GitHub login of the bot account
#   -Value Expires   token expiry date yyyy-MM-dd (empty if none)
#   -Value Check     verifies the token against the GitHub API, prints a status line
#
# Exit codes (values other than Check): 0 OK | 3 not set up on this PC | 4 cannot decrypt.
# Exit codes (Check): 0 OK | 1 setup problem (instructions printed) | 2 GitHub not reachable.
# This file contains no secrets.

[CmdletBinding()]
param(
    [ValidateSet('Token', 'BotLogin', 'Expires', 'Check')]
    [string]$Value = 'Token'
)

$ErrorActionPreference = 'Stop'

# --- Location (must match set-token.ps1) ----------------------------------------
$Repo     = 'habakuk007/evangtermine'
$StoreDir = Join-Path $env:LOCALAPPDATA 'claude\secrets'
$Name     = 'github-' + ($Repo -replace '/', '-')
$BlobPath = Join-Path $StoreDir "$Name.dat"
$ConfPath = Join-Path $StoreDir "$Name.json"

function Show-Instructions {
    Write-Output @"

How to set up or renew the GitHub bot token on this PC:

  1. Sign in to GitHub as the bot account and create a token
     (Settings -> Developer settings -> Personal access tokens):
       Classic token with scope 'repo' (add 'workflow' only if the bot must
       change .github/workflows), or a fine-grained token limited to $Repo
       with Contents, Pull requests and Issues = Read and write.
     Set an expiry date (e.g. 90 days). The bot account needs write access
     to $Repo (collaborator).

  2. In your own PowerShell window (NOT through Claude Code), from the repo root:
       powershell -ExecutionPolicy Bypass -File .agents\skills\gh-bot\set-token.ps1

  3. Make sure GH_TOKEN is NOT set in your Windows or Claude environment,
     otherwise plain gh/git calls also run as the bot.

  4. Continue in Claude Code (no restart needed).

"@
}

if (-not (Test-Path -LiteralPath $BlobPath) -or -not (Test-Path -LiteralPath $ConfPath)) {
    if ($Value -eq 'Check') {
        Write-Output 'ERROR: GitHub bot token is not set up on this PC.'
        Show-Instructions; exit 1
    }
    [Console]::Error.WriteLine("GitHub bot is not set up on this PC (missing $StoreDir\$Name.*).")
    exit 3
}

$conf = Get-Content -LiteralPath $ConfPath -Raw | ConvertFrom-Json

if ($Value -in 'BotLogin', 'Expires') {
    $v = if ($Value -eq 'BotLogin') { $conf.botLogin } else { $conf.expires }
    if ($v) { [Console]::Out.Write([string]$v) }
    exit 0
}

Add-Type -AssemblyName System.Security
$bytes = $null
try {
    $bytes = [Security.Cryptography.ProtectedData]::Unprotect(
        [IO.File]::ReadAllBytes($BlobPath), $null, [Security.Cryptography.DataProtectionScope]::CurrentUser)
} catch {
    if ($Value -eq 'Check') {
        Write-Output 'ERROR: GitHub bot token cannot be decrypted (set up by another Windows user or on another PC).'
        Show-Instructions; exit 1
    }
    [Console]::Error.WriteLine('Cannot decrypt the GitHub bot token (created by another Windows user or PC, or corrupted).')
    exit 4
}

if ($Value -eq 'Token') {
    try { [Console]::Out.Write([Text.Encoding]::UTF8.GetString($bytes)) }
    finally { [Array]::Clear($bytes, 0, $bytes.Length) }
    exit 0
}

# --- Check mode ------------------------------------------------------------------
$WarnDays = 14
$status = 0; $login = ''; $expHeader = ''; $canPush = $false
try {
    $token = [Text.Encoding]::UTF8.GetString($bytes)
    $h = @{ Authorization = "Bearer $token"; 'User-Agent' = 'gh-bot-skill' }
    try {
        $r = Invoke-WebRequest -UseBasicParsing -Uri 'https://api.github.com/user' -Headers $h
        $login = ($r.Content | ConvertFrom-Json).login
        $e = $r.Headers['github-authentication-token-expiration']
        if ($e) { $expHeader = [string]($e | Select-Object -First 1) }
        $status = 200
        try {
            $r2 = Invoke-WebRequest -UseBasicParsing -Uri "https://api.github.com/repos/$Repo" -Headers $h
            $canPush = [bool](($r2.Content | ConvertFrom-Json).permissions.push)
        } catch {
            # token is valid but cannot read the repository (403 or 404, as GitHub reports it)
            $status = if ($_.Exception.Response) { [int]$_.Exception.Response.StatusCode } else { 403 }
        }
    } catch {
        if ($_.Exception.Response) { $status = [int]$_.Exception.Response.StatusCode } else { $status = 0 }
    }
} finally {
    $token = $null
    $h = $null
    [Array]::Clear($bytes, 0, $bytes.Length)
}

switch ($status) {
    200 { }
    401 { Write-Output 'ERROR: GitHub bot token is expired, revoked or wrong (HTTP 401).'; Show-Instructions; exit 1 }
    { $_ -in 403, 404 } { Write-Output "ERROR: GitHub bot token cannot access $Repo (HTTP $status)."; Show-Instructions; exit 1 }
    default {
        $shown = if ($status) { $status } else { 'no response' }
        Write-Output "ERROR: Could not verify the token (HTTP $shown)."
        Write-Output 'Check your network connection and https://www.githubstatus.com, then retry.'
        exit 2
    }
}
if ($login -ne $conf.botLogin) {
    Write-Output "ERROR: Token belongs to '$login', but the setup expects '$($conf.botLogin)'."
    Show-Instructions; exit 1
}
if (-not $canPush) {
    Write-Output "ERROR: Bot '$login' has no write access to $Repo."
    Show-Instructions; exit 1
}

$expires = if ($expHeader) { ([datetime]::Parse($expHeader)).ToString('yyyy-MM-dd') } elseif ($conf.expires) { $conf.expires } else { '' }
if ($expires) {
    $days = [int](([datetime]::Parse($expires) - (Get-Date)).TotalDays)
    if ($days -le $WarnDays) {
        Write-Output "WARNING: GitHub bot token expires on $expires (in $days days). Renew it soon:"
        Show-Instructions
    }
}
if ($env:GH_TOKEN -or $env:GITHUB_TOKEN) {
    Write-Output 'WARNING: GH_TOKEN/GITHUB_TOKEN is set in the environment, so plain gh and git push may also run as a token identity. Remove it so only gh-bot.sh uses the bot.'
}
Write-Output "OK: GitHub bot token is valid (bot: $login)."
exit 0
