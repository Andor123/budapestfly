<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache



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
define( 'DB_NAME', 'budapestfly' );

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
define( 'AUTH_KEY',         ')/6vsGa(u4SK*Sg<o)vuKs}^vSXp~4k9B2~$uju1sb%IpX&SK&@vGX;jBdf{i?7x' );
define( 'SECURE_AUTH_KEY',  'H,PweP>]F]ufjPb3zPE:=IZZC1<kH[fb8`xprV?0ux)8$+[S_i:lEZ<!f@<{$oob' );
define( 'LOGGED_IN_KEY',    'OPMK>Vi#Ldn1X<=-40JX/0wKvr.5V.sZ&xC`wmBx.%_zm?JuW-qK3[k4b7wAk+fi' );
define( 'NONCE_KEY',        '9,F!;HwWl{sx0+X(yn#3XI1_LE@vwu%OD%5ZXuwnlKz#>q.94L0ETVaLAr0$l7<$' );
define( 'AUTH_SALT',        'DpU`>n.~I{Ee+:igXD,-bCN/<iJ-mHAE|p`!GLoPZgt$a)Tb:];}U1Pj#;`.s5sK' );
define( 'SECURE_AUTH_SALT', '*pyf{M?1SwGg%Y7&d@^&@kmm1.k=V.)Df ztroqIk4|g_#o>pZ5Dm$_y&g `wn~[' );
define( 'LOGGED_IN_SALT',   'pd/oN=ni7b6iQxPM*sYU<Znk[DORF_$[c;bzE/+$Q}7t}hEbjGbVHl-CsY,YUTLL' );
define( 'NONCE_SALT',       ']o*<vnc|mQ59J|4OJN8D01<x0cF1l^VYN.G~^I{VV1Oi,{C3^X!b8v=s=Y~n#P,9' );

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
