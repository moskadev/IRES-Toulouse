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


// Debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);

function my_admin_menu() {
	add_menu_page(
		'Add a user',
        'Add a user',
        0,
        'add-account',
        'my_admin_page_contents',
        'dashicons-schedule',
        3
    );
}

// add_action( 'admin_menu', 'my_admin_menu' );

/**
 * Fonction de test des requêtes SQL
 */
function test_page() {
	add_menu_page(
		'Add a user',
        'Add a user',
        0,
        'add-account',
        'test_sql',
        'dashicons-schedule',
        3
    );
}
// add_action( 'admin_menu', 'test_page' );

function test_sql() {
	global $wpdb; // Permet d'utiliser $wpdb

	// Récupérer l'id maximum de la table
	$results = $wpdb->get_results(
		'SELECT user_login, MAX(ID) AS max_id FROM wp_users'
	);
	
	foreach ( $results as $result ) {
		$highest_id = $result->max_id;
	}
	echo "Highest ID : ".$highest_id."<br>"; // Affichage de l'ID max de la table
	echo "<br><br>";
	// Création d'une personne 'fictive' pour l'ajouter à la table des users
	$prenom = "Robin";
	$nom = "Fougeron";
	$display = $prenom." ".$nom;
	$mail = "robin.fougeron@gmail.com";
	$user_login = strtolower( substr( $prenom, 0, 1 ).$nom );
	$new_user_id = (int) $highest_id + 1; // Incrémentation de l'id pour ajouter l'utilisateur

	// Affichage de l'utilisateur en question
	echo "<h3>Ajout d'un utilisateur :</h3>Prénom : ".$prenom
	     ."<br>Nom : ".$nom
		 ."<br>Mail : ".$mail
		 ."<br>Login : ".$user_login
		 ."<br>ID : ".$new_user_id; 
	
	$userdata = array(
	'user_login' => $user_login,
	'user_pass' => NULL,
	'user_email' => $mail,
	'user_registered' => 'NOW',
	'user_status' => '0',
	'display_name' => $display);
	
	$user_id = wp_insert_user( $userdata ) ;

	if ( ! is_wp_error( $user_id ) ) {
		echo "User created : ". $user_id;
	}

	// ID, user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_activation_key, user_status, display_name
	
}
/*
 * ===================================
 * 	    fin des fonctions de tests
 * ===================================
 */



function my_admin_page_contents() {
	
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
	<!-- <tr class="form-field">
		<th scope="row"><label for="user_login"><?php _e( 'Username' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
		<td><input name="user_login" type="text" id="user_login" value="<?php echo esc_attr( $new_user_login ); ?>" autocapitalize="none" autocorrect="off" maxlength="60" /></td>
	</tr> -->
    <?php
		$new_user_login = strtolower( substr( $new_user_firstname, 0, 1 ).$new_user_lastname );	
        echo $new_user_login;
    ?>
	<tr class="form-field form-required">
		<th scope="row"><label for="email"><?php _e( 'Email' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
		<td><input name="email" type="email" id="email" value="<?php echo esc_attr( $new_user_email ); ?>" /></td>
	</tr>
	<?php if ( ! is_multisite() ) { ?>
	<tr class="form-field">
		<th scope="row"><label for="first_name"><?php _e( 'First Name' ); ?> </label></th>
		<td><input name="first_name" type="text" id="first_name" value="<?php echo esc_attr( $new_user_firstname ); ?>" /></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="last_name"><?php _e( 'Last Name' ); ?> </label></th>
		<td><input name="last_name" type="text" id="last_name" value="<?php echo esc_attr( $new_user_lastname ); ?>" /></td>
	</tr>
	<tr class="form-field form-required user-pass2-wrap hide-if-js">
		<th scope="row"><label for="pass2"><?php _e( 'Repeat Password' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
		<td>
		<input name="pass2" type="password" id="pass2" autocomplete="off" aria-describedby="pass2-desc" />
		<p class="description" id="pass2-desc"><?php _e( 'Type the password again.' ); ?></p>
		</td>
	</tr>
	<?php } // End if ! is_multisite().	?>
</table>

	<?php
	/** This action is documented in wp-admin/user-new.php */
	do_action( 'user_new_form', 'add-new-user' );
	?>

	<?php submit_button( __( 'Add New User' ), 'primary', 'createuser', true, array( 'id' => 'createusersub' ) );
	
}


$new_user_displayname = $new_user_firstname . " " . $new_user_lastname;

// $wpdb->query(
//    $wpdb->prepare(
//    "
//    INSERT INTO $wpdb->users
//    ( id, user_login, user_nicename, user_email )
//    VALUES ( %d, %s, %s, %s )
//    ",
//    array(
//          $new_user_id,
//          $new_user_login,
// 		 $new_user_email,
// 		 $new_user_displayname,
//       )
//    )
// );
// INSERT INTO wp_users ( id, user_login, user_nicename, user_email )
// VALUES ( 10, "rfougeron", "robin.fougeron@iut-rodez.fr", "Robin Fougeron")



?>

