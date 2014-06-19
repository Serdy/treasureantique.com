<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
 //Added by WP-Cache Manager
define( 'WPCACHEHOME', '/var/www/treasureantique.com/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager
define('DB_NAME', 'shop');

/** MySQL database username */
define('DB_USER', 'shop_mysql');

/** MySQL database password */
define('DB_PASSWORD', '"zWuJiCE');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'Jl}@T:|3acVSOq(1<5#a:Phgw1m#=/C.;&111DDmQD.X;-^4MME_xe9dd_a1v7Ee');
define('SECURE_AUTH_KEY',  '`+]eRn/a_;K0qt-z|?GF.xsWolP],}kJ1_Dda%u&3~|#Vs4C{||AB5fj%KU@$p*1');
define('LOGGED_IN_KEY',    'C&pzN.A>Lb.[9G1upkZl^`4RZh+_>H0WSm$cO|p/?67;3g2GQLW.Lw*A,]mFf$sJ');
define('NONCE_KEY',        ']g+#LIizpSyA:Pd*ZaY|jl{j(D|^f=Y>r%-||RV{p[Wclsyxg&;#z+o{iW:;0PDn');
define('AUTH_SALT',        ',g5sBKI+|H}DIts]5rYoE=m,sXrR/`PY#0{+[p.p;qSmyE@dr8X-:[6QM;&ysGaU');
define('SECURE_AUTH_SALT', '/?+]RBPeW5Q;{U9Ii&.&W1H|-9~f` ,3 A0ZOgrYL59Y|47[0n9=!y9O2J(|Wb>+');
define('LOGGED_IN_SALT',   'TCal}ckhw][cQoN-Bjn:^~33}&bG$3@OZ#&Po>xd;|AMayDr ;k;/(WnwZY-k||B');
define('NONCE_SALT',       'HCH2+QX?o{lw|/!3~ppf.J78e-VorlkO03QMZeFFHYi$,Y_W4Z>_OdKRk1`i-Mu$');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
