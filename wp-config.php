<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'zeit_services_db' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '>1OcedYyL[8q)tejkcbTC-=NMk_`*<#IG:^LmDfJw0C-g1dU.`Kb/#/e}q.,s#+G' );
define( 'SECURE_AUTH_KEY',  'xFp=|^t_>~EUf%q2q~@q>IAyvI!nt*#iUGm7w1PH}NbBpWR{S#g]#C_uKGmg|fh|' );
define( 'LOGGED_IN_KEY',    '7HkXHBQ[BqSu47m3X:/Pf{TU6k2{8_PyvxLGmXKp52|:?N`qnj0JMp0Y wWO8|z0' );
define( 'NONCE_KEY',        'G#9q+]XcY/F7O/Kn_g_TBPRPpb.KnW,E(t}Zft1xVySF3GY8Ak&kSn;bG$d9&B<1' );
define( 'AUTH_SALT',        ')_ULz_5[9`uC!8]w_;An^@foLu+)b8+HG7I[ _2$I_rq3LhCpj}.vQ0/nGcQ8>TT' );
define( 'SECURE_AUTH_SALT', 'gFBLJ|T#?6fs`zyK48ldeMZ;<qmWt5Ygg^c%Ai2B#ZNHLG-)h3qWDJK1_z]-_#hX' );
define( 'LOGGED_IN_SALT',   'W; ~~<MxLt0<Taz34IE/Ae,|@`ETv/f|G88hUhS1U1V /S8S7+u`~<(L&.p2%~Id' );
define( 'NONCE_SALT',       '>dp1!fy<:zjwO[bE>{4@~o>JP@T8WT=RdYH;ePfT|x);E-|:k&fSL?#-*yb},Xj1' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
