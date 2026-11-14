# set-token.ps1
#
# Run this yourself in your own PowerShell window (NOT through Claude Code).
# Stores the GitHub bot token DPAPI-encrypted for the current Windows user on this PC.
# The token is read as a hidden prompt; it is never written to disk in plain text.
# This file contains no secrets.

$ErrorActionPreference = 'Stop'

# --- Location (must match get-token.ps1) ----------------------------------------
$Repo     = 'habakuk007/evangtermine'
$StoreDir = Join-Path $env:LOCALAPPDATA 'claude\secrets'
$Name     = 'github-' + ($Repo -replace '/', '-')
$BlobPath = Join-Path $StoreDir "$Name.dat"
$ConfPath = Join-Path $StoreDir "$Name.json"

$botLogin = Read-Host 'Bot GitHub login [habakuk-ai]'
if (-not $botLogin) { $botLogin = 'habakuk-ai' }
$expires = Read-Host 'Token expiry date yyyy-MM-dd (empty = unknown)'
if ($expires) { [void][datetime]::Parse($expires) }

$secure = Read-Host 'Paste the bot token (hidden)' -AsSecureString
$bstr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
try { $plain = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($bstr) }
finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr) }
if (-not $plain) { throw 'No token entered.' }

Add-Type -AssemblyName System.Security
$bytes = [Text.Encoding]::UTF8.GetBytes($plain)
$plain = $null
try {
    $enc = [Security.Cryptography.ProtectedData]::Protect(
        $bytes, $null, [Security.Cryptography.DataProtectionScope]::CurrentUser)
} finally { [Array]::Clear($bytes, 0, $bytes.Length) }

New-Item -ItemType Directory -Force -Path $StoreDir | Out-Null
[IO.File]::WriteAllBytes($BlobPath, $enc)
@{ botLogin = $botLogin; expires = $expires } | ConvertTo-Json | Set-Content -LiteralPath $ConfPath -Encoding UTF8

Write-Host "Stored in $StoreDir. Now verify with: bash .agents/skills/gh-bot/gh-bot.sh check"
Write-Host 'Remember to remove GH_TOKEN from your Windows / Claude environment.'
