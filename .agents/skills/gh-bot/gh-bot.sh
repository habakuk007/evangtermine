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
#   gh-bot.sh comment <pr-number> --body-file <path>
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
  gh-bot.sh pr <branch> "<title>" ["<body>" | --body-file <path>]  open a PR as the bot (base develop; hotfix/* -> master)
  gh-bot.sh comment <pr-number> "<text>"         comment on a PR as the bot
  gh-bot.sh comment <pr-number> --body-file <path>  comment on a PR as the bot (body from a file)
  gh-bot.sh list                                 list open PRs
EOF
  exit 64
}

# Prints one stored value; returns get-token.ps1's exit code (a pipe through tr would mask it).
get_value() {
  local out rc
  out="$(powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$GET_TOKEN" -Value "$1")"; rc=$?
  printf '%s' "${out//$'\r'/}"
  return "$rc"
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

# Quote a value as a PowerShell double-quoted literal. Escapes backtick, $,
# double-quote and newlines so any argument (title, body, path with spaces)
# survives the round-trip through `powershell.exe -Command`.
ps_dquote() {
  local s="$1" BT
  BT=$(printf '\140')   # a backtick, without a literal backtick in this source
  s="${s//$BT/$BT$BT}"  # `  -> ``
  s="${s//\$/$BT\$}"    # $  -> `$
  s="${s//\"/$BT\"}"    # "  -> `"`
  s="${s//$'\n'/$BT n}" # newline -> `n
  printf '"%s"' "$s"
}

# Join the given arguments into a PowerShell-safe command fragment.
ps_args() {
  local out="" a
  for a in "$@"; do out+=" $(ps_dquote "$a")"; done
  printf '%s' "$out"
}

# Runs gh as the bot: the token lives only in the environment of this single
# call. WSL does not propagate GH_TOKEN to the Windows gh.exe (it then falls
# back to the keyring/credential-manager login), so gh is run through
# powershell.exe, which honors GH_TOKEN. Works under WSL and Git Bash alike.
bot_gh() {
  local token rc cmd
  token="$(get_value Token)" || return 1
  [ -n "$token" ] || { echo "ERROR: empty bot token" >&2; return 1; }
  cmd=""
  cmd+="\$env:GH_TOKEN = $(ps_dquote "$token");"
  cmd+=" if (Test-Path Env:GITHUB_TOKEN) { Remove-Item Env:GITHUB_TOKEN };"
  cmd+=" gh$(ps_args "$@")"
  rc=0
  powershell.exe -NoProfile -Command "$cmd" || rc=$?
  unset token
  return $rc
}

cmd_pr() {
  [ -n "$1" ] && [ -n "$2" ] || usage
  local branch="$1" title="$2" base
  case "$branch" in
    hotfix/*) base="master" ;;
    *) base="develop" ;;
  esac
  # Optional body: a 3rd positional arg, or `--body-file <path>` (4th).
  if [ $# -eq 4 ] && [ "$3" = "--body-file" ]; then
    # Resolve the body file for this (WSL / Git-Bash) shell and the Windows
    # gh.exe, mirroring the comment --body-file handling.
    local check_path win_path drv rest
    case "$4" in
      [A-Za-z]:\\*|[A-Za-z]:/*)
        drv="${4:0:1}"; rest="${4:2}"
        rest="${rest//\\//}"
        rest="${rest#/}"
        check_path="/mnt/$(printf '%s' "$drv" | tr '[:upper:]' '[:lower:]')/$rest"
        win_path="$4"
        ;;
      *)
        check_path="$4"
        win_path="$(winpath "$4")"
        ;;
    esac
    [ -f "$check_path" ] || { printf 'ERROR: --body-file: %s not found (pass a relative path such as ./body.md, or a path this shell can reach)\n' "$4" >&2; exit 64; }
    bot_gh pr create --repo "$REPO" --head "$branch" --base "$base" --title "$title" --body-file "$win_path"
    return
  fi
  if [ $# -eq 3 ]; then
    bot_gh pr create --repo "$REPO" --head "$branch" --base "$base" --title "$title" --body "$3"
  else
    bot_gh pr create --repo "$REPO" --head "$branch" --base "$base" --title "$title"
  fi
}

cmd_comment() {
  [ -n "$1" ] || usage
  case "$1" in *[!0-9]*) usage ;; esac
  # comment <pr> --body-file <path>  -> body read from a file (avoids arg mangling)
  if [ $# -eq 3 ] && [ "$2" = "--body-file" ]; then
    # Resolve the body file for this (WSL / Git-Bash) shell and for the Windows
    # gh.exe. The existence check runs in this Linux shell, so:
    #   - a relative path or a WSL path is stat-able as-is and is converted to a
    #     Windows path for gh.exe by winpath();
    #   - an absolute Windows path (C:\… / C:/…) is mapped into the WSL namespace
    #     (/mnt/<drive>/…) so the existence check passes, and is handed to gh.exe
    #     verbatim (it accepts that form).
    local body="$3" check_path win_path drv rest
    case "$body" in
      [A-Za-z]:\\*|[A-Za-z]:/*)
        drv="${body:0:1}"; rest="${body:2}"
        rest="${rest//\\//}"
        rest="${rest#/}"
        check_path="/mnt/$(printf '%s' "$drv" | tr '[:upper:]' '[:lower:]')/$rest"
        win_path="$body"
        ;;
      *)
        check_path="$body"
        win_path="$(winpath "$body")"
        ;;
    esac
    [ -f "$check_path" ] || { printf 'ERROR: --body-file: %s not found (pass a relative path such as ./body.md, or a path this shell can reach)\n' "$body" >&2; exit 64; }
    bot_gh pr comment "$1" --repo "$REPO" --body-file "$win_path"
    return
  fi
  # comment <pr> "<text>"  -> body passed as an argument
  [ $# -eq 2 ] && [ -n "$2" ] || usage
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
