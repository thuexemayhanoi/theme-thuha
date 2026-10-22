#!/usr/bin/env bash
# Builds a disposable local WordPress (SQLite, no MySQL) for integration tests.
# Never point this at production. Everything lives under $RBH_WP_ROOT.
#
#   RBH_WP_ROOT=/tmp/rbh-wp tests/integration/bootstrap-wp.sh
#
# Downloads (test tooling only, nothing is copied into this repository):
#   - WordPress core     composer johnpbloch/wordpress-core
#   - SQLite integration git  WordPress/sqlite-database-integration
#   - Yoast SEO          git  Yoast-dist/wordpress-seo (the production SEO owner)
#   - WP-CLI             composer wp-cli/wp-cli-bundle
set -euo pipefail

REPO="$(cd "$(dirname "$0")/../.." && pwd)"
ROOT="${RBH_WP_ROOT:-${TMPDIR:-/tmp}/rbh-wp}"
WP_VERSION="${RBH_WP_VERSION:-7.1.3}"
YOAST_VERSION="${RBH_YOAST_VERSION:-28.7}"
PORT="${RBH_WP_PORT:-8899}"
export COMPOSER_ALLOW_SUPERUSER=1

mkdir -p "$ROOT"
cd "$ROOT"

if [ ! -d tools/vendor/wp-cli ]; then
  mkdir -p tools && (cd tools && composer init -n --name=rbh/tools -q && composer require -n -q wp-cli/wp-cli-bundle:^2.12)
fi
if [ ! -d core/vendor/johnpbloch/wordpress-core ]; then
  mkdir -p core && (cd core && composer init -n --name=rbh/core -q && composer require -n -q "johnpbloch/wordpress-core:$WP_VERSION")
fi
[ -d sqlite-src ] || git clone -q --depth 1 https://github.com/WordPress/sqlite-database-integration.git sqlite-src
[ -d yoast-src ] || git clone -q --depth 1 --branch "$YOAST_VERSION" https://github.com/Yoast-dist/wordpress-seo.git yoast-src

rm -rf site
cp -r core/vendor/johnpbloch/wordpress-core site
cp -rL sqlite-src/packages/plugin-sqlite-database-integration site/wp-content/plugins/sqlite-database-integration
sed "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$ROOT/site/wp-content/plugins/sqlite-database-integration#; s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
  site/wp-content/plugins/sqlite-database-integration/db.copy > site/wp-content/db.php
cp -r yoast-src site/wp-content/plugins/wordpress-seo
rm -rf site/wp-content/plugins/wordpress-seo/.git
cp yoast-src/src/generated/container.php "$ROOT/yoast-container.php"
(cd site/wp-content/plugins/wordpress-seo && composer dump-autoload -n -q --no-dev --no-plugins -o)
# dump-autoload can remove the generated DI container; restore it.
cp "$ROOT/yoast-container.php" site/wp-content/plugins/wordpress-seo/src/generated/container.php

cat > site/wp-config.php <<EOF
<?php
define( 'DB_NAME', 'wp' ); define( 'DB_USER', '' ); define( 'DB_PASSWORD', '' ); define( 'DB_HOST', '' );
define( 'DB_CHARSET', 'utf8mb4' ); define( 'DB_COLLATE', '' );
define( 'DB_DIR', __DIR__ . '/wp-content/database/' ); define( 'DB_FILE', 'wp.sqlite' );
foreach ( array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' ) as \$k ) { define( \$k, 'local-test-only-' . \$k ); }
\$table_prefix = 'wp_';
define( 'WP_DEBUG', true ); define( 'WP_DEBUG_LOG', __DIR__ . '/debug.log' ); define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_HTTP_BLOCK_EXTERNAL', true ); define( 'AUTOMATIC_UPDATER_DISABLED', true ); define( 'DISABLE_WP_CRON', true );
if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
require_once ABSPATH . 'wp-settings.php';
EOF

# Theme folder name = current active stylesheet (wp-theme). Plugin is linked from the repo.
ln -sfn "$REPO" site/wp-content/themes/wp-theme
ln -sfn "$REPO/plugins/rentbikehanoi-native-vi" site/wp-content/plugins/rentbikehanoi-native-vi

cat > "$ROOT/wp" <<EOF
#!/usr/bin/env bash
exec php -d memory_limit=512M "$ROOT/tools/vendor/wp-cli/wp-cli/php/boot-fs.php" --path="$ROOT/site" --allow-root "\$@" 2> >(grep -v -E 'wp_update_(plugins|themes)|WordPress.org' >&2)
EOF
chmod +x "$ROOT/wp"

"$ROOT/wp" core install --url="http://127.0.0.1:$PORT" --title="Motorbike Nguyen Ha" --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email >/dev/null
"$ROOT/wp" plugin activate wordpress-seo >/dev/null
"$ROOT/wp" theme activate wp-theme >/dev/null
"$ROOT/wp" eval-file "$REPO/tests/integration/fixtures-en.php"
"$ROOT/wp" yoast index --reindex --skip-confirmation >/dev/null 2>&1 || true
"$ROOT/wp" rewrite flush >/dev/null
# Router for PHP's built-in server: real files are served, everything else (incl. *.xml sitemaps) goes to WordPress.
cat > "$ROOT/router.php" <<'PHP'
<?php
$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
if ( '/' !== $path && is_file( __DIR__ . '/site' . $path ) && ! preg_match( '/\.php$/', $path ) ) {
	return false;
}
chdir( __DIR__ . '/site' );
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/site/index.php';
PHP
echo "WordPress ready at $ROOT/site (serve with: php -S 127.0.0.1:$PORT -t $ROOT/site $ROOT/router.php)"
