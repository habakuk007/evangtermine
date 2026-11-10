#!/usr/bin/env bash
# gh-bot.sh
#
# Single entry point for GitHub operations as the bot identity (habakuk-ai).
# Plain gh/git keep using the developer's own login; only the subcommands below
# run as the bot. The token is read from the DPAPI store only inside the function
# that needs it, passed to gh via the environment of that one call, never echoed
# and never put on a command line. Contains no secrets; safe to commit.
#
# Usage:
#   gh-bot.sh check
#   gh-bot.sh pr <branch> "<title>" ["<body>"]   # hotfix/* -> master; else develop
#   gh-bot.sh comment <pr-number> "<text>"
#   gh-bot.sh list

# Git's bash.exe started from PowerShell/cmd has no /usr/bin on PATH (cygpath, tr, base64 missing).
PATH="/usr/bin:$PATH"

REPO="habakuk007/evangtermine"
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# Windows path for powershell.exe: Git Bash has cygpath, WSL has wslpath.
winpath() {
  if command -v cygpath >/dev/null 2>&1; then cygpath -w "$1"
  elif command -v wslpath >/dev/null 2>&1; then wslpath -w "$1"
  else printf '%s' "$1"
  fi
}
GET_TOKEN="$(winpath "$DIR/get-token.ps1")"

usage() {
  cat >&2 <<'EOF'
Usage:
  gh-bot.sh check                                verify the bot token
  gh-bot.sh pr <branch> "<title>" ["<body>"]     open a PR as the bot (hotfix/* -> master; else develop)
  gh-bot.sh comment <pr-number> "<text>"         comment on a PR as the bot
  gh-bot.sh list                                 list open PRs
EOF
  exit 64
}

get_value() {
  powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$GET_TOKEN" -Value "$1" | tr -d '\r'
}

# Runs get-token.ps1 -Value Check; exits with its code on failure, passes WARNING output through.
preflight() {
  local out rc
  out="$(powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$GET_TOKEN" -Value Check)"; rc=$?; out="${out//$'\r'/}"
  if [ "$rc" -ne 0 ]; then
    printf '%s\n' "$out"
    exit "$rc"
  fi
  case "$out" in
    *WARNING*) printf '%s\n' "$out" ;;
  esac
}

# Runs gh as the bot: the token lives only in the environment of this single call.
bot_gh() {
  local token rc
  token="$(get_value Token)" || return 1
  GH_TOKEN="$token" GITHUB_TOKEN= gh "$@"
  rc=$?
  unset token
  return $rc
}

cmd_pr() {
  { [ $# -eq 2 ] || [ $# -eq 3 ]; } && [ -n "$1" ] && [ -n "$2" ] || usage
  local branch="$1" title="$2" body="${3:-}" base
  case "$branch" in
    hotfix/*) base="master" ;;
    *) base="develop" ;;
  esac
  bot_gh pr create --repo "$REPO" --head "$branch" --base "$base" --title "$title" --body "$body"
}

cmd_comment() {
  [ $# -eq 2 ] && [ -n "$1" ] && [ -n "$2" ] || usage
  case "$1" in *[!0-9]*) usage ;; esac
  bot_gh pr comment "$1" --repo "$REPO" --body "$2"
}

cmd_list() {
  bot_gh pr list --repo "$REPO" --state open
}

[ $# -ge 1 ] || usage
sub="$1"; shift
case "$sub" in
  check)   [ $# -eq 0 ] || usage; powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$GET_TOKEN" -Value Check | tr -d '\r'; exit "${PIPESTATUS[0]}" ;;
  pr)      preflight; cmd_pr "$@" ;;
  comment) preflight; cmd_comment "$@" ;;
  list)    [ $# -eq 0 ] || usage; preflight; cmd_list ;;
  *)       usage ;;
esac
