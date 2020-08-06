<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'itc' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'demo' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'nuHboWTUKv8uzxw8ACZNMTYHOj8LB9MOydAUjNMZkXyLgY6te0H2pRtxhUcsSvsx' );
define( 'SECURE_AUTH_KEY',  'qr5reiIw9we6pzNPnLkhXyvdSDWwawZEu3gxbknL3RzigfqZhMtQZiNAXqD3zIJu' );
define( 'LOGGED_IN_KEY',    'gRr2V4u9vBr2YhbP2sDIA1GdAL7hvdQWJoby3aCXDbM7sstWG9BBWWWajwvAbsly' );
define( 'NONCE_KEY',        'ueVsozngvzl8NDnj62AAYWUtjwCZhkIWVAyLDgcBhNfMDQ2lkFw5OWmZBOPB2Sza' );
define( 'AUTH_SALT',        'DR3VsGDrTzVKxkfLTspvl5y9eJIBehmMXZU1jR5cjvu9ei0yczf8QSSsrZpHDNtq' );
define( 'SECURE_AUTH_SALT', 'd7Un7QHz2LZI67nX2AYCMqrMuQJjx4mLLc8swAEund3uFtxrbm4ODS3SHcFRRPNs' );
define( 'LOGGED_IN_SALT',   '55edyjsU7RLJiPfU7hZS7QraxlRBoezfd7yZ2t6x4YVAb0CeATWML9eMU9nHrILD' );
define( 'NONCE_SALT',       'wO2oz5efbpZ40HyJJIwbcRDOLrPSQfswwdSk4dbTNwHwCmliOz0MchurLhRx4j5K' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'le_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );
define('WP_POST_REVISIONS', false);
define('AUTOSAVE_INTERVAL', 300);
## Disable Editing in Dashboard
define('DISALLOW_FILE_EDIT', true);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
