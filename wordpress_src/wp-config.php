<?php

// fix constant for wordpress
const PLAN_TYPE_FREE = 'free';
const PLAN_TYPE_BUSINESS = 'business';

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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

//Using environment variables for DB connection information
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */

$connectstr_dbhost = getenv('DATABASE_HOST') ?: '127.0.0.1';
$connectstr_dbname = getenv('DATABASE_NAME') ?: 'magicaklocaldb';
$connectstr_dbusername = getenv('DATABASE_USERNAME');
$connectstr_dbpassword = getenv('DATABASE_PASSWORD');

define('DB_NAME', $connectstr_dbname);

/** MySQL database username */
define('DB_USER', $connectstr_dbusername);

/** MySQL database password */
define('DB_PASSWORD',$connectstr_dbpassword);

/** MySQL hostname */
define('DB_HOST', $connectstr_dbhost);

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

/**#@-*/

define('WP_CACHE', true);

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

$plan_type = getenv('PLAN_TYPE') ?: PLAN_TYPE_FREE;
$hostname = getenv('HOSTNAME');

$website_host = preg_replace('/-[0-9]*$/', '', $hostname);

// get replicas from file .replica
$replicas = 0;
if (file_exists('/var/www/html/.replica')) {
    $replicas = intval(file_get_contents('/var/www/html/.replica'));
}

if ($plan_type == PLAN_TYPE_FREE) {
    define('WP_REDIS_CONFIG', [
        'token' => 'u0QZkTTB9g1klns3AFcVxhyrSsVWkR0iEiiR7WerupnTjjlzqiE6b1XUUPQ7',
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
        'timeout' => 0.5,
        'read_timeout' => 0.5,
        'retry_interval' => 10,
        'retries' => 3,
        'backoff' => 'smart',
        'maxttl' => 3600 * 24, // 24 hours
        'serializer' => 'igbinary',
        'async_flush' => true,
        'split_alloptions' => true,
        'prefetch' => true,
        'debug' => false,
        'save_commands' => false,
        'service' => "redismaster-$website_host"
    ]);
} elseif ($plan_type == PLAN_TYPE_BUSINESS) {

    $sentinels = [];

    for ($i = 0; $i <= $replicas; $i++) {
        // push sentinels to array
        $sentinels[] = "tcp://$website_host-$i.$website_host-db-svc-headless:26379";
    }

    define('WP_REDIS_CONFIG', [
        'token' => 'u0QZkTTB9g1klns3AFcVxhyrSsVWkR0iEiiR7WerupnTjjlzqiE6b1XUUPQ7',
        'timeout' => 0.5,
        'read_timeout' => 0.5,
        'retry_interval' => 10,
        'retries' => 3,
        'backoff' => 'smart',
        'maxttl' => 3600 * 24, // 24 hours
        'serializer' => 'igbinary',
        'async_flush' => true,
        'split_alloptions' => true,
        'prefetch' => true,
        'debug' => false,
        'save_commands' => false,
        'service' => "redismaster-$website_host",
        'sentinels' => $sentinels
    ]);
}

define('WP_REDIS_DISABLED', getenv('WP_REDIS_DISABLED') ?: false);


// end of redis pro

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
$debug = getenv('DEBUG') ?: false;

if(isset($debug) && $debug == 'true') {
    $debug = true;
} elseif(isset($debug) && $debug == 'false') {
    $debug = false;
} else {
    $debug = false;
}

define( 'WP_DEBUG', $debug );
define( 'WP_DEBUG_DISPLAY', $debug );

define('WP_MEMORY_LIMIT', '16387M');
define('WP_MAX_MEMORY_LIMIT', '16388M');

define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);

/* That's all, stop editing! Happy blogging. */
/**https://codex.wordpress.org/Function_Reference/is_ssl */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
	$_SERVER['HTTPS'] = 'on';

//Relative URLs for swapping across app service deployment slots
if ( $local_url = getenv('WP_LOCAL_URL') ) {
    define('WP_HOME',    rtrim( $local_url, '/' ) );
    define('WP_SITEURL', rtrim( $local_url, '/' ) );
    define('DOMAIN_CURRENT_SITE', parse_url( $local_url, PHP_URL_HOST ) . ( parse_url( $local_url, PHP_URL_PORT ) ? ':' . parse_url( $local_url, PHP_URL_PORT ) : '' ) );
} elseif(isset($_SERVER['HTTP_HOST'])) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    define('WP_HOME', $scheme . '://'. $_SERVER['HTTP_HOST']);
    define('WP_SITEURL', $scheme . '://'. $_SERVER['HTTP_HOST']);
    define('DOMAIN_CURRENT_SITE', $_SERVER['HTTP_HOST']);
}
define('WP_CONTENT_URL', '/wp-content');

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
