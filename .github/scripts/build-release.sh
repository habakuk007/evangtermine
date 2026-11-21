#!/usr/bin/env bash
#
# Builds the release file set and the release ZIP of the plugin.
#
# The file set is built like deploy.sh of 10up/action-wordpress-plugin-deploy
# does it (rsync with .distignore as the exclude list), so the ZIP holds the
# same files as the WordPress.org package. The ZIP has the top-level folder
# "evangtermine" and is named evangtermine-<version>.zip.
#
# Usage: bash .github/scripts/build-release.sh [output-dir]
#   output-dir  default: build/ (ignored by git and excluded by .distignore)
#
# Needs: bash, rsync, find, unzip, and zip or python3 (or python). Run it from the
# repository root or from anywhere inside the checkout.
set -euo pipefail

SLUG="evangtermine"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
OUT="${1:-${ROOT}/build}"
mkdir -p "${OUT}"
OUT="$(cd "${OUT}" && pwd)"

if [ ! -f "${ROOT}/.distignore" ]; then
	echo "::error::.distignore is missing. The deploy action would fall back to .gitattributes."
	exit 1
fi
for tool in rsync unzip; do
	if ! command -v "${tool}" >/dev/null 2>&1; then
		echo "::error::${tool} is required (on Windows use WSL)."
		exit 1
	fi
done

VERSION="$(sed -n 's/^ \* Version:[[:space:]]*//p' "${ROOT}/${SLUG}.php" | head -n 1 | tr -d '\r')"
if [ -z "${VERSION}" ]; then
	echo "::error::Could not read the version from ${SLUG}.php."
	exit 1
fi

# Build the folder and the ZIP in a scratch directory first: the output
# directory may be inside the repository (build/), so it must not be copied.
WORK="$(mktemp -d)"
trap 'rm -rf "${WORK}"' EXIT
PKG="${WORK}/${SLUG}"
mkdir -p "${PKG}"

# Same command as in deploy.sh of the 10up action.
rsync -rc --exclude-from="${ROOT}/.distignore" "${ROOT}/" "${PKG}/" --delete --delete-excluded

echo "Release files:"
(cd "${WORK}" && find "${SLUG}" -type f | sort)

# ── Checks ──────────────────────────────────────────────────────────
status=0

# Must not be shipped, at any depth: hidden files and folders, dependency
# folders, tests, tool configuration, Markdown documents, archives.
unwanted="$(cd "${PKG}" && find . -mindepth 1 \( \
	-name '.*' -o -name 'vendor' -o -name 'node_modules' -o -name 'tests' -o -name 'build' \
	-o -name 'composer.lock' \
	-o -name 'package.json' -o -name 'package-lock.json' \
	-o -name 'phpcs.xml*' -o -name 'phpunit.xml*' -o -name 'opencode.json' \
	-o -name '*.md' -o -name '*.zip' -o -name '*.log' \
	\) -print | sort)"
if [ -n "${unwanted}" ]; then
	echo "::error::Tooling files are in the release file set. Add them to .distignore."
	echo "${unwanted}"
	status=1
fi

# Must be shipped.
for required in evangtermine.php readme.txt uninstall.php autoloader.php application assets; do
	if [ ! -e "${PKG}/${required}" ]; then
		echo "::error::${required} is missing in the release file set."
		status=1
	fi
done

if [ "${status}" -ne 0 ]; then
	exit "${status}"
fi
echo "The release file set is clean."

# ── ZIP ─────────────────────────────────────────────────────────────
ZIP="${OUT}/${SLUG}-${VERSION}.zip"
rm -f "${ZIP}"
if command -v zip >/dev/null 2>&1; then
	(cd "${WORK}" && zip -rqX "${ZIP}" "${SLUG}")
else
	PY="$(command -v python3 || command -v python || true)"
	if [ -z "${PY}" ]; then
		echo "::error::zip or python3 is required to create the ZIP."
		exit 1
	fi
	"${PY}" - "${WORK}" "${SLUG}" "${ZIP}" <<'PYEOF'
import os, sys, zipfile
work, slug, target = sys.argv[1:4]
with zipfile.ZipFile(target, "w", zipfile.ZIP_DEFLATED) as z:
    for dirpath, dirnames, filenames in os.walk(os.path.join(work, slug)):
        dirnames.sort()
        for name in sorted(filenames):
            full = os.path.join(dirpath, name)
            z.write(full, os.path.relpath(full, work).replace(os.sep, "/"))
PYEOF
fi

# Verify the ZIP itself: every entry lives in "evangtermine/".
if [ -n "$(unzip -Z1 "${ZIP}" | grep -v "^${SLUG}/" || true)" ]; then
	echo "::error::The ZIP has entries outside ${SLUG}/."
	exit 1
fi
if ! unzip -Z1 "${ZIP}" | grep -qx "${SLUG}/${SLUG}.php"; then
	echo "::error::${SLUG}/${SLUG}.php is missing in the ZIP."
	exit 1
fi

echo "Built ${ZIP} (version ${VERSION}, $(du -h "${ZIP}" | cut -f1), $(unzip -Z1 "${ZIP}" | grep -vc '/$') files)"
if [ -n "${GITHUB_OUTPUT:-}" ]; then
	echo "zip=${ZIP}" >> "${GITHUB_OUTPUT}"
	echo "version=${VERSION}" >> "${GITHUB_OUTPUT}"
fi
