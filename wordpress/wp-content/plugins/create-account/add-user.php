<?php
/**
 * @package Create_Account
 * @version 1.0.0
 */
/*
Plugin Name: Create Account
Description: Create account without using the WordPress panel
Author: IUT Rodez
Version: 1.0.0
*/
$host = 'localhost';
$db = 'wordpress';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo $e->getMessage() ;
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

/**
 * Création de la page du plugin
 * Cette page permettra d'ajouter un utilisateur par un responsable
 * Le responsable devra renseigner les informations suivantes :
 *      - Le Mail
 *      - Le Prénom
 *      - Le Nom
 */
function my_page() {
	add_menu_page(
		'Ajouter un utilisateur',	// Titre de la page quand le menu est selectionné
        'Ajouter compte',			// Nom du menu
        0,							// Niveau de sécurité d'accès au menu
        'add-account',				// Nom de référence du menu
        'page_content', 			// Appel de la fonction du contenu de la page
        'dashicons-admin-users', 			// Icone du menu
        3 							// Position de la page dans la liste
    );
}
/** Ajout du menu dans le tableau de bord de l'administration WordPress */
add_action( 'admin_menu', 'my_page' );

/**
 * Contenu du menu "Ajouter un utilisateur"
 * Permet de :
 * 		- Afficher un formulaire pour ajouter un utilisateur (Mail, Prénom, Nom)
 * 		- Créer le login de l'utilisateur sous le format :
 * 				première lettre du prénom concaténé avec le nom le tout en minuscule
 * 		- Ajouter l'utilisateur à la base de données
 */
function page_content() {
	?>
        <h1>
			Créer un compte d'un utilisateur.
        </h1>
		
        <form method="post" name="createuser" id="createuser" class="validate" novalidate="novalidate"
		<?php
	/** This action is documented in wp-admin/user-new.php */
	do_action( 'user_new_form_tag' );
	
	?>
>
<input name="action" type="hidden" value="createuser" />
	<?php wp_nonce_field( 'create-user', '_wpnonce_create-user' ); ?>
	<?php
	// Load up the passed data, else set to a default.
	$creating = isset( $_POST['createuser'] );

	$new_user_login             = $creating && isset( $_POST['user_login'] ) ? wp_unslash( $_POST['user_login'] ) : '';
	$new_user_firstname         = $creating && isset( $_POST['first_name'] ) ? wp_unslash( $_POST['first_name'] ) : '';
	$new_user_lastname          = $creating && isset( $_POST['last_name'] ) ? wp_unslash( $_POST['last_name'] ) : '';
	$new_user_email             = $creating && isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '';
	$new_user_uri               = $creating && isset( $_POST['url'] ) ? wp_unslash( $_POST['url'] ) : '';
	$new_user_role              = $creating && isset( $_POST['role'] ) ? wp_unslash( $_POST['role'] ) : '';
	$new_user_send_notification = $creating && ! isset( $_POST['send_user_notification'] ) ? false : true;
	$new_user_ignore_pass       = $creating && isset( $_POST['noconfirmation'] ) ? wp_unslash( $_POST['noconfirmation'] ) : '';

	?>
<table class="form-table" role="presentation">
	<tr class="form-field form-required">
		<th scope="row"><label for="email"><?php _e( 'Email' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
		<td><input name="email" type="email" id="email" value="<?php echo esc_attr( $new_user_email ); ?>" /></td>
	</tr>
	<?php if ( ! is_multisite() ) { ?>
	<tr class="form-field form-required">
		<th scope="row"><label for="first_name"><?php _e( 'First Name' ); ?> </label></th>
		<td><input name="first_name" type="text" id="first_name" value="<?php echo esc_attr( $new_user_firstname ); ?>" /></td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row"><label for="last_name"><?php _e( 'Last Name' ); ?> </label></th>
		<td><input name="last_name" type="text" id="last_name" value="<?php echo esc_attr( $new_user_lastname ); ?>" /></td>
	</tr>
	<?php } // End if ! is_multisite().	?>
</table>

	<?php
	/** This action is documented in wp-admin/user-new.php */
	do_action( 'user_new_form', 'add-new-user' );
	?>

	<?php submit_button( __( 'Add New User' ), 'primary', 'createuser', true, array( 'id' => 'createusersub' ) );
	// Création du login de l'utilisateur
	$new_user_login = strtolower( substr( $new_user_firstname, 0, 1 ).$new_user_lastname );
	/**
	 * Ajout de l'utilisateur à la base de données WordPress
	 */
	$userdata = array(
		'user_login' => $new_user_login,
		'user_pass' => NULL,
		'user_email' => $new_user_email,
		'user_registered' => current_time('mysql', 1),
		'user_status' => '0',
		'display_name' => $new_user_login);
		
	$user_id = wp_insert_user( $userdata ) ;

	$msg = "L'utilsateur $new_user_login a était ajouté";
	
	if ( ! is_wp_error( $user_id ) ) {
		echo $msg;
	}
}