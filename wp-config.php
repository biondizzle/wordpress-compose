<?php
/**
 * WordPress configuration — fully environment-driven
 * All settings come from docker-compose environment variables
 */

// ** Database ** //
define( 'DB_NAME',     getenv('WORDPRESS_DB_NAME')     ?: 'wordpress' );
define( 'DB_USER',     getenv('WORDPRESS_DB_USER')     ?: 'wordpress' );
define( 'DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD') ?: 'wordpress' );
define( 'DB_HOST',     getenv('WORDPRESS_DB_HOST')     ?: '127.0.0.1' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 */
define( 'AUTH_KEY',         getenv('AUTH_KEY')         ?: 'change-me-1' );
define( 'SECURE_AUTH_KEY',  getenv('SECURE_AUTH_KEY')  ?: 'change-me-2' );
define( 'LOGGED_IN_KEY',    getenv('LOGGED_IN_KEY')    ?: 'change-me-3' );
define( 'NONCE_KEY',        getenv('NONCE_KEY')        ?: 'change-me-4' );
define( 'AUTH_SALT',        getenv('AUTH_SALT')        ?: 'change-me-5' );
define( 'SECURE_AUTH_SALT', getenv('SECURE_AUTH_SALT') ?: 'change-me-6' );
define( 'LOGGED_IN_SALT',   getenv('LOGGED_IN_SALT')   ?: 'change-me-7' );
define( 'NONCE_SALT',       getenv('NONCE_SALT')       ?: 'change-me-8' );

/**#@-*/

$table_prefix = getenv('WORDPRESS_TABLE_PREFIX') ?: 'wp_';

define( 'WP_DEBUG', filter_var(getenv('WP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN) );

/* Add any custom values between this line and the "stop editing" line. */

// WordPress URL — set via WORDPRESS_URL env var (e.g. https://thelittlecici.com)
$wp_url = getenv('WORDPRESS_URL');
if ( $wp_url ) {
    define( 'WP_HOME',  $wp_url );
    define( 'WP_SITEURL', $wp_url );
}

// Force WordPress to use the FTP extension
define('FS_METHOD', 'ftpext');
define('FTP_BASE', '/app');
define('FTP_HOST', getenv('FTP_HOST') ?: '127.0.0.1:2121');
define('FTP_USER', getenv('FTP_USER') ?: 'wordpress');
define('FTP_PASS', getenv('FTP_PASS') ?: 'wordpress');

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
