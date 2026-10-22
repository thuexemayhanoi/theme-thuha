#!/usr/bin/env bash
# Runs every check of this repository. Stops at the first failure.
#
#   scripts/test-all.sh                     static checks only
#   RBH_WP_ROOT=/tmp/rbh-wp scripts/test-all.sh --integration [--bootstrap] [--package]
#
# --bootstrap   build a fresh local WordPress (WordPress + SQLite + Yoast) in $RBH_WP_ROOT
# --integration run the WordPress + browser tests against that local site (127.0.0.1 only)
# --package     rebuild dist/ and verify the ZIPs (requires a clean, committed tree)
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
cd "$REPO"
PHPCS="${PHPCS:-$REPO/vendor/bin/phpcs}"
INTEGRATION=0; BOOTSTRAP=0; PACKAGE=0
for arg in "$@"; do case "$arg" in --integration) INTEGRATION=1;; --bootstrap) BOOTSTRAP=1;; --package) PACKAGE=1;; esac; done

step() { printf '\n== %s\n' "$*"; }

step "Existing theme checks (AGENTS.md)"
node --check assets/js/assistant-seo-20261019.js
node tests/assistant.cjs

step "Repository guardrails"
node tests/static/repo-check.mjs

step "Vietnamese content and locked business data"
php tests/static/content-test.php | tail -1

step "Coding standards"
if [ -x "$PHPCS" ]; then
  "$PHPCS" --standard=phpcs-plugin.xml.dist -q && echo "WPCS: plugin clean"
  "$PHPCS" --standard=phpcs.xml.dist -q . && echo "PHPCompatibilityWP 7.4+: clean"
else
  echo "SKIPPED: phpcs not installed (run: composer install)"; exit 1
fi

if [ "$INTEGRATION" = 1 ]; then
  : "${RBH_WP_ROOT:?Set RBH_WP_ROOT}"
  PORT="${RBH_WP_PORT:-8899}"
  [ "$BOOTSTRAP" = 1 ] && { step "Bootstrap local WordPress"; tests/integration/bootstrap-wp.sh; }
  step "Start local server on 127.0.0.1:$PORT"
  php -S "127.0.0.1:$PORT" -t "$RBH_WP_ROOT/site" "$RBH_WP_ROOT/router.php" >"$RBH_WP_ROOT/server.log" 2>&1 &
  SERVER=$!; trap 'kill $SERVER 2>/dev/null || true' EXIT; sleep 1
  step "WordPress integration (English protection, routing, SEO, sitemap, admin)"
  RBH_BASELINE_REF="${RBH_BASELINE_REF:-}" node tests/integration/run.mjs
  step "Browser UI (switch placement, a11y, Light/Dark) — text brand, then logo"
  NODE_PATH="$(npm root -g)" node tests/ui/language-switch.test.cjs ${RBH_SHOTS:-}
  "$RBH_WP_ROOT/wp" eval-file tests/integration/fixture-logo.php >/dev/null
  RBH_LOGO=1 NODE_PATH="$(npm root -g)" node tests/ui/language-switch.test.cjs ${RBH_SHOTS:-}
  "$RBH_WP_ROOT/wp" eval-file tests/integration/fixture-logo.php remove >/dev/null
fi

if [ "$PACKAGE" = 1 ]; then
  step "Package and verify artifacts"
  python3 scripts/package.py
  python3 tests/static/package-test.py | tail -1
fi
echo; echo "ALL CHECKS PASSED"
