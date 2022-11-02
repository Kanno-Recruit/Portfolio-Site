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
define( 'DB_NAME', 'bcdhm_r4356653' );

/** MySQL database username */
define( 'DB_USER', 'bcdhm_work27' );

/** MySQL database password */
define( 'DB_PASSWORD', 'aX9ybRq#' );

/** MySQL hostname */
define( 'DB_HOST', 'mysql34.conoha.ne.jp' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'cY&/s;~AU}Cr9XUD[Zv*w%E`pks&eqTas%Om<&KlvtAC~zD-gUc]c=rNI`1x&Nl?' );
define( 'SECURE_AUTH_KEY',   'aaDE~+TRk422 zK.V8#AnEp7ooZ=Jp/Aa N78r0HFzC^aG0k!M|8IF6bi.]wkG/)' );
define( 'LOGGED_IN_KEY',     '|Tfm}j%sqf;o{e[hu{U/~zH{n#@F(z26~_9s;*,@qoJtK1$633o|)-^wB(+t^U8|' );
define( 'NONCE_KEY',         '.z*]T.P9xX%gcM_YE|<n9@tf<VU@hR/FNsEV!Kuf)}dR;3fAT 5I79;*!!UE8 Ln' );
define( 'AUTH_SALT',         '|bUj(HOlSfOsMXv7!>~]M~l>s@EsIWil{7?`)a5S=[hOw,8^N?.GgH_YA[2Ugcd5' );
define( 'SECURE_AUTH_SALT',  'fix pp>Q!y]7htEqi_HQUlZqRHJ#:ZbKx#l52w:I2>& #+IR92m<oQIPlE6?QS<p' );
define( 'LOGGED_IN_SALT',    '*t6=t$E*Nxym{a?HiFR]-^8NUmC?&)N8q5,ay6x6VJZBBl&9i}K^`Z2&nEg&h]}8' );
define( 'NONCE_SALT',        'd%oG46?O6/(P]:*9x;$76Q_,~oq3*6uDeC#}>|Y#8lzd=!<BkphYx|Cf$^@|oh&.' );
define( 'WP_CACHE_KEY_SALT', 'NUqwR 108X!0<LvOrwIEUE}r#sB&1h{pn{i,:<8[so4rdzwrKjpz&KG52UR,yJj1' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === "https") {
    $_SERVER['HTTPS'] = 'on';
    define('FORCE_SSL_LOGIN', true);
    define('FORCE_SSL_ADMIN', true);
}




define( 'CW_DASHBOARD_PLUGIN_SID', 'MfC4U_4QwTnqq6GB3_dyWKlv2Tbq3LObUlJWUThEGh336v0WjjaFV9HjimFQ7zNoXN4VH6nQLaTo8DhKgDziZQTR0hCl9wkkoKMBl5I6ZHE.' );
define( 'CW_DASHBOARD_PLUGIN_DID', 'O_dXYGOGIpECoBjot31i4I-Lua_b4bVLv55fHp_A0kW-hIlUfj7yaurhX1mSuP0EFw0kKNDw-br5PAHdQIFhqKrenkXWaunxQWQPXyJsWLg.' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
