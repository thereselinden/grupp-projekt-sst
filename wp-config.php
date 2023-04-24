<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'grupp_projekt_sst' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',         'R{])`2mm_PF,IUo?Yii2N;H+$&H!MS_GY~W_oWbPa&;D$;cu;hP&XiUK0U;}ps9d' );
define( 'SECURE_AUTH_KEY',  'R%=tr8Oz82a;eK;7Z]20}-3Nnt?3lR<=lPYyzEA;__:1--<p$uh)>jhzFm$mFwMO' );
define( 'LOGGED_IN_KEY',    '~<G6Sy|M6vk3(uhg>V*ce*f UtV>Yd`Zb59772MCm#3N +c#0jhxl4k6iTW>]AA5' );
define( 'NONCE_KEY',        ':cRnXSFQ#5Shl17iS5:jRgskzEHUuQ$r H3WuS&-f/r.zvBKRm9}*>}EBp:YxSU0' );
define( 'AUTH_SALT',        '((Ap-spvq%W6bKn~ KA|s&@eHzclsKv`=QC&:^Ltzqg)YxJ6b_A>6?uo-^haOaZF' );
define( 'SECURE_AUTH_SALT', 'Ehqo~DQ6-G*/t3K>CUz?]/}ln.7rF,>IStv<z2sA|$5/*9v9}0?(oSWB~+DZr>-%' );
define( 'LOGGED_IN_SALT',   '2p:p$J{qd^hxe1PiQ2-Rq0MHmZK OK=6I V1 E6T*Fid|M[Z5>9M.9bXjk#c9)CW' );
define( 'NONCE_SALT',       'yRo4.Oo9Cx2vU#6+0*+wl|&hNSd{Tdx_:mI8rmBPqS0NDwccXz,h`tV?d(&%;xmq' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
