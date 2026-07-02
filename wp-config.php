<?php
/**
 * WordPress configuration with FTP support for frankenphp
 */

// ** Database settings — override via environment variables in docker-compose ** //
define( 'DB_NAME',     getenv('WORDPRESS_DB_NAME')     ?: 'wordpress' );
define( 'DB_USER',     getenv('WORDPRESS_DB_USER')     ?: 'wordpress' );
define( 'DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD') ?: 'wordpress' );
define( 'DB_HOST',     getenv('WORDPRESS_DB_HOST')     ?: '127.0.0.1' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 */
define( 'AUTH_KEY',         'x9kL2mP7vQwR4tYz1aB3cD5eF8gH0jK2' );
define( 'SECURE_AUTH_KEY',  'mN4pQ7rS0tU2vW4xY6zA8cB0dE2fG4h' );
define( 'LOGGED_IN_KEY',    'J6kL8mN0pQ2rS4tU6vW8xY0zA2bC4dE' );
define( 'NONCE_KEY',        'f6G8hJ0kL2mN4pQ6rS8tU0vW2xY4zA6b' );
define( 'AUTH_SALT',        'c8D0eF2gH4jK6mL8mN0pQ2rS4tU6vW8x' );
define( 'SECURE_AUTH_SALT', 'Y0zA2bC4dE6fG8hJ0kL2mN4pQ6rS8tU0' );
define( 'LOGGED_IN_SALT',   'v2W4xY6zA8bC0dE2fG4hJ6kL8mN0pQ2r' );
define( 'NONCE_SALT',       'S4tU6vW8xY0zA2bC4dE6fG8hJ0kL2mN4p' );

/**#@-*/

$table_prefix = 'wp_';

define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

// Force WordPress to use the FTP extension
define('FS_METHOD', 'ftpext');

// Absolute path to the WordPress root installation directory
define('FTP_BASE', '/app/');

// FTP server connection details (localhost — ftpserver running in same container via supervisor)
define('FTP_HOST', '127.0.0.1:2121');
define('FTP_USER', 'wordpress');
define('FTP_PASS', 'wordpress');

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
