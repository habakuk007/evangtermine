#!/usr/bin/env bash
# Keeps the tracked copies .github/agents and .github/instructions in sync with
# their source of truth .agents/agents and .agents/instructions.
#
#   bash .agents/scripts/sync-github-copies.sh          refresh the copies from .agents/
#   bash .agents/scripts/sync-github-copies.sh --check  fail if the copies differ (used by CI)
#
# Line endings (CRLF vs LF) are ignored when comparing.
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$root"
dirs=(agents instructions)

if [[ "${1:-}" == "--check" ]]; then
	status=0
	for d in "${dirs[@]}"; do
		if ! diff -rq --strip-trailing-cr ".agents/$d" ".github/$d"; then
			status=1
		fi
	done
	if [[ $status -ne 0 ]]; then
		echo "::error::.github/agents or .github/instructions differ from .agents/. Run: bash .agents/scripts/sync-github-copies.sh" >&2
	fi
	exit $status
fi

for d in "${dirs[@]}"; do
	rm -rf ".github/$d"
	mkdir -p ".github/$d"
	cp -R ".agents/$d/." ".github/$d/"
done
echo "Refreshed .github/agents and .github/instructions from .agents/."
