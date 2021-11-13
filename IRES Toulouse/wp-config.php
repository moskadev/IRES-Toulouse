<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'IRES_Toulouse' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', 'root' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ' A6AKIQ^pk_)J]QDH64=c,GMV^JrlrThR6B0<^/m|X3*037&kq~S8/=*tfCb_0, ' );
define( 'SECURE_AUTH_KEY',  'R3joMaxY^`S.jbkcJW{;n>f4:GZFwCEE;Ish!WFjj}H+zzLfVmr%mF4Vv7cocQ3&' );
define( 'LOGGED_IN_KEY',    'JRV]4zayI>N<sk $.[x:^;HmRD9j5:%nhU0kU[efC[%UAoC~4=d8_%E*eGGX(k}l' );
define( 'NONCE_KEY',        'C#rCyuh&y+SL0I-/X !t)n^78([d1hXd`;u:MIZ2&)aW{QG;29RPF(2Bs {#CA|p' );
define( 'AUTH_SALT',        'TB9U(S}Anvf(_*2f[=ZPu<QQ^C0 He3P{Jm0!>;kC |tq5y>jOIJ3v<}m+mpsivN' );
define( 'SECURE_AUTH_SALT', 'vkI~Y%ttgM08&Z<-ZWHX)tYx-g#0;~&|%+/m/T?2:+2O~u{n<29E.nUfQqoP)=x-' );
define( 'LOGGED_IN_SALT',   'y1qw&NZoNNo8M^UD19aOzIG`= F~gQ y)3vR*e(j>BBF8ijv4v(TQc3;pYWUUrmN' );
define( 'NONCE_SALT',       '#@Qai H#N=<C@1#OH|>?|z3Ux_C*LQqrrbZ`~i,#G63&/*.Wqt9Bkx?8_|-r^9/C' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );
