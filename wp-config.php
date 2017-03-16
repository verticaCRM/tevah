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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'bbrokers_tevah');

/** MySQL database username */
define('DB_USER', 'bbrokers_dba');

/** MySQL database password */
define('DB_PASSWORD', 'ddhk0na4Z!');

/** MySQL hostname */
define('DB_HOST', 'yf.alsonetworks.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '+&[#M&|Kz/onH]Tbo@vgy^R,xk>#):EP^U*.;pA&CzPy?^_MI#?v(rN0bkt6XtL*');
define('SECURE_AUTH_KEY',  '&d%q&~!qLV+L/(rSx2Nzh*~e_[,plnT~[7;1QP^w9=ILj+yn2:AB9C#*W93At6.O');
define('LOGGED_IN_KEY',    'Vuf5!]zX3_rP]M4*pd0KV<S=/F[+sJ8G&WgjexI.v8Er:E%C_o&I;Aa$WGZ(O<Yu');
define('NONCE_KEY',        '!:dnwG_T8<~&&A7T{|7m[$L7`kMjn]Y?s>`oG$Ix YDH21,[:-:>6w+_]}iR7r8&');
define('AUTH_SALT',        'i/l!-^em?i_<#3-.6v_PqsW[~n~=o!jgSDY]b7e{v|xne9_m+z!QHb#3ar9(=;#7');
define('SECURE_AUTH_SALT', 'uW[;@V+h=aKbFR=i~;#e_p!Rl&+zfqwwOW>Sx4:u2bI4l:t6CU >bAZL4aM>]E-=');
define('LOGGED_IN_SALT',   'dv]$0+zI.&xB_t>I43^}SY2)V<ds[#?9Z&@nXG]V+K@.G;-z6vf/<){&Y:ZIlYHa');
define('NONCE_SALT',       ';nY2^;<(9VkbA]>|Bau2KN~V<Fhg5$RXlAb< !]*2?``.(ilp{A36DUE}a,1(4$B');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'bbcrmFE_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
