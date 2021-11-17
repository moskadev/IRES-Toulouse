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
define( 'DB_NAME', 'wordpress' );

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
define( 'AUTH_KEY',         'd}RyOmXOpD<3+P9AbgF}dL)g6Eo:#oa=X4o{)u;7J=@oMdfaTQ(8_jFJ3lq;B/%z' );
define( 'SECURE_AUTH_KEY',  ')kg&@DgN<3P#$_|fl$jPifs=T3+`o}*~nqJrdN?@R<JI1l)tp:l-_/d+^ElKuP^P' );
define( 'LOGGED_IN_KEY',    'X/1Gh,QM]$VQ&gO9gnwjU8[NpdKorf:,|Hj x.(K,-WT`rW>Z4%`O4*YfI]~ZF|+' );
define( 'NONCE_KEY',        'jqbWrVDF$[9%(ePNU#Rl;JNDr8X8C>e5d@vx|VX,:2|^t*Y*,M(~I:hhz_p$kT01' );
define( 'AUTH_SALT',        'TJP&F v%cm5=B$NOUpg_=K7P=yB}Hi27GCYYk2ANWe~Y1[a0jFFNQ5<`YRs]% &!' );
define( 'SECURE_AUTH_SALT', 'P<]?EkeW~I{HX6_$:GU?3MV0e@z7P:<5gikrq>,4h04QCy&9N9TZO8PDQ-G >b.d' );
define( 'LOGGED_IN_SALT',   'x}0_.8+[@@foyGw7x)zpoc85|aQLGO5Wu,U9L?6{!Q0WRi,F1gBH;p,K6)|iw]7$' );
define( 'NONCE_SALT',       '|p(^/?LW~[T0f?e1CF87;+HxMV):Y`)K~wsRi|.<{Ytr}]WFxGZkaF{I99{C@z<;' );
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
