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
define('DB_NAME', 'wordsample');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY',         '+=~xk I]1j.03W]LJ{e%Yg`YNxgbgm.|N_ -{N:O3xW2: ?dZbki;+W_N_3h6U a');
define('SECURE_AUTH_KEY',  'ZmX#2Z [rzLtAL]S0;>Idy%}WQ)r((o9Q]Q3=F+t}!Dm~Ihuwu*q*ZhNl%h:Q<?c');
define('LOGGED_IN_KEY',    '!=`t4@(@b5_s,F,ZR_w&Dzaki%Yvb|p2,{5//b_$t:VRL]bP.dtSemQ0IFp:0?3;');
define('NONCE_KEY',        'Q#tGl%BpHWIl:u#}P*,_-whz*pCQZ;olbMBNN!p!tI[dwj.4@*?X.2np(-l)nf>v');
define('AUTH_SALT',        '->_Npx`g84^#KeO7sz{g-Rg:3vwQew@ zi1cI$&5)8QT>k%*m^mzA{u)SyFfNwH*');
define('SECURE_AUTH_SALT', '|:nXq+lZ,p>xh>p{P$RorIzJmsG4Xg:WM;o?WB5-/(0-w0i$JAurTL}/98_1qV@Y');
define('LOGGED_IN_SALT',   '*`Ogpkv%sy+lF~OiOTeI3/AH5^,Ne79CzB5ND-}r3h|Tbs}X.[y+Hg`syj>7_(5`');
define('NONCE_SALT',       '9P]]+gd8bq?Jqwk7eB(-M>2i:[26Y~>JZ8`@-HWGiJrh:J)`q2u;=I9v%sN#5U32');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
