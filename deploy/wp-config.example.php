<?php
/**
 * WordPress configuration for the Railway deployment of the DTC website.
 * All credentials come from Railway service variables; nothing is hard-coded.
 */

/* ---------- Database (Railway MySQL service) ---------- */
define( 'DB_NAME',     getenv( 'WORDPRESS_DB_NAME' ) ?: 'railway' );
define( 'DB_USER',     getenv( 'WORDPRESS_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WORDPRESS_DB_PASSWORD' ) ?: '' );
define( 'DB_HOST',     getenv( 'WORDPRESS_DB_HOST' ) ?: '127.0.0.1' );
define( 'DB_CHARSET',  'utf8' );
define( 'DB_COLLATE',  '' );

/* ---------- Authentication unique keys and salts ---------- */
/* De echte waarden staan als service-variabele in Railway en horen niet
   in de repo. Nieuwe set nodig? https://api.wordpress.org/secret-key/1.1/salt/ */
define( 'AUTH_KEY',               getenv( 'AUTH_KEY' ) ?: '' );
define( 'SECURE_AUTH_KEY',        getenv( 'SECURE_AUTH_KEY' ) ?: '' );
define( 'LOGGED_IN_KEY',          getenv( 'LOGGED_IN_KEY' ) ?: '' );
define( 'NONCE_KEY',              getenv( 'NONCE_KEY' ) ?: '' );
define( 'AUTH_SALT',              getenv( 'AUTH_SALT' ) ?: '' );
define( 'SECURE_AUTH_SALT',       getenv( 'SECURE_AUTH_SALT' ) ?: '' );
define( 'LOGGED_IN_SALT',         getenv( 'LOGGED_IN_SALT' ) ?: '' );
define( 'NONCE_SALT',             getenv( 'NONCE_SALT' ) ?: '' );
define( 'WP_CACHE_KEY_SALT',      getenv( 'WP_CACHE_KEY_SALT' ) ?: '' );

/* ---------- Table prefix ---------- */
$table_prefix = 'wp_';

/* ---------- Behind Railway's TLS-terminating proxy ---------- */
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === strtolower( explode( ',', $_SERVER['HTTP_X_FORWARDED_PROTO'] )[0] ) ) {
	$_SERVER['HTTPS'] = 'on';
	$_SERVER['SERVER_PORT'] = 443;
}
if ( ! empty( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {
	$_SERVER['HTTP_HOST'] = explode( ',', $_SERVER['HTTP_X_FORWARDED_HOST'] )[0];
}

/* ---------- Site URL ---------- */
$dtc_site_url = getenv( 'WP_SITE_URL' );
if ( ! $dtc_site_url && getenv( 'RAILWAY_PUBLIC_DOMAIN' ) ) {
	$dtc_site_url = 'https://' . getenv( 'RAILWAY_PUBLIC_DOMAIN' );
}
if ( $dtc_site_url ) {
	define( 'WP_HOME',    $dtc_site_url );
	define( 'WP_SITEURL', $dtc_site_url );
}

/* ---------- Filesystem / updates ---------- */
define( 'FS_METHOD', 'direct' );
define( 'DISALLOW_FILE_EDIT', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_MEMORY_LIMIT', '512M' );
define( 'WP_MAX_MEMORY_LIMIT', '512M' );

/* ---------- Debugging ---------- */
define( 'WP_DEBUG',         (bool) getenv( 'WP_DEBUG' ) );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_DEBUG_LOG',     false );

/* That's all, stop editing! Happy publishing. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
