#!/usr/bin/env bash
# Levanta un WordPress local (SQLite, sin MySQL) con el plugin y el theme
# de este repositorio enlazados, y carga datos de EJEMPLO.
#
#   WP_DIR=/ruta/wp ./tools/dev-setup.sh      # instala
#   php -S 127.0.0.1:8080 -t "$WP_DIR"        # sirve
#
# Requiere: php >= 8.0 (pdo_sqlite), composer, wp-cli (o WP_CLI=php wp-cli.phar).
set -euo pipefail
REPO="$(cd "$(dirname "$0")/.." && pwd)"
WP_DIR="${WP_DIR:-$REPO/.wp}"
WP="${WP_CLI:-wp} --path=$WP_DIR --allow-root"
URL="${WP_URL:-http://127.0.0.1:8080}"

if [ ! -f "$WP_DIR/wp-load.php" ]; then
  composer create-project --no-interaction johnpbloch/wordpress-core "$WP_DIR"
fi
if [ ! -d "$WP_DIR/wp-content/plugins/sqlite-database-integration" ]; then
  mkdir -p "$WP_DIR/wp-content/plugins"
  TMP="$(mktemp -d)"
  git clone --depth 1 https://github.com/WordPress/sqlite-database-integration.git "$TMP/sqlite"
  # Monorepo: el plugin está en packages/ y usa symlinks internos (se resuelven con -L).
  cp -rL "$TMP/sqlite/packages/plugin-sqlite-database-integration" "$WP_DIR/wp-content/plugins/sqlite-database-integration"
  rm -rf "$TMP"
fi
sed "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$WP_DIR/wp-content/plugins/sqlite-database-integration#;s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" \
  "$WP_DIR/wp-content/plugins/sqlite-database-integration/db.copy" > "$WP_DIR/wp-content/db.php"

mkdir -p "$WP_DIR/wp-content/themes"
ln -sfn "$REPO/wp-content/plugins/motocred-core" "$WP_DIR/wp-content/plugins/motocred-core"
ln -sfn "$REPO/wp-content/themes/motocred-2026" "$WP_DIR/wp-content/themes/motocred-2026"

if [ ! -f "$WP_DIR/wp-config.php" ]; then
  $WP config create --dbname=wp --dbuser=x --dbpass=x --skip-check --extra-php <<PHP
define( 'MOTOCRED_DEMO', true );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
PHP
fi
$WP core is-installed 2>/dev/null || $WP core install --url="$URL" --title=MotoCred --admin_user=admin --admin_password=admin --admin_email=dev@example.com --skip-email
$WP option update home "$URL"
$WP option update siteurl "$URL"
$WP option update blogdescription "Motos 0KM en cuotas en Mendoza"
$WP language core install es_AR --activate >/dev/null 2>&1 || true
$WP rewrite structure '/%postname%/' --hard
$WP plugin activate motocred-core
$WP theme activate motocred-2026
$WP motocred seed --demo
$WP rewrite flush --hard
echo "Listo: php -S 127.0.0.1:8080 -t $WP_DIR  (admin / admin)"
