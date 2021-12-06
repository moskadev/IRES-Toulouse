<?php
/**
 * @package affectation-role
 * @version 1.0.0
 */
/*
Plugin Name: Affectation Role
Description: Editing role without using the wordpress panel
Author: IUT Rodez
Version: 1.0.0
*/
/**
 * Creation of the plugin page
 * This page will allow you to change a role of an util
 * The user that you want to modify the permission have to enter :
 *      - Username
 *      - the permission that you want grant him
 */
function affectation_page() {
    add_menu_page(
        "Affecter un role", // Page title when the menu is selected
        "Affecter un role", // Name of the menu
        10, // Menu access security level
        "affecter_role", // Menu reference name
        "affectation_role_content", // Call the page content function
        "dashicons-admin-users", // Menu icon
        3 // Page position in the list
    );
}

/**
 * Adding the menu in the dashboard of the WordPress administration
 */
add_action("admin_menu", "affectation_page");

/**
 * Contents of the "Modify a user" menu
 * Allows to :
 *      - change a permission of an user if you are admin
 */
function affectation_role_content() {
    $metas = [
        "username" => "Utilisateur",
        "membre_association" => "Membre association (autre)"
    ];

    // Check that the form has been sent
    if(isset($_POST['username']) ) {
	    $choice = $_POST['choosen_role'];
        $message="Le rôle a bien été modifié";
        $type_message="error";

        // Check if the login name submit exist
        if ( username_exists( $_POST['username'] ) != null ) {
	        $user = get_userdatabylogin( $_POST['username'] );

            // The role for the user haven't been changed because he already had the role choose
            if (!in_array( "$choice", (array) $user->roles )) {
	            $user = wp_update_user( array( 'ID' => $user->id, 'role' => $choice ) );
	            $type_message="updated";
            } else {

	            // Determine the displayed role name
	            $name_role = ( $choice === 'contributor' )
		            ? 'membre' : ( $choice === 'author' )
                    ? 'responsable de groupe' : 'administrateur';
	            $message ="Rien n'a été effectué, l'utilisateur possédait déjà le rôle que vous avez affecté ($name_role)";
            }
	    } else if ( username_exists( $_POST['username'] ) == null ) {
		    $message = "Rien n'a été effectué, vous n'avez pas entré d'identifiant correct";
	    } ?>
	    <div id="message" class="<?php echo "$type_message";?> notice is-dismissible">
            <p><strong><?php echo "$message"; ?></strong></p>
        </div>
    <?php
    }
    ?>
    <form method="post" name="modifyuser" id="modifyuser" class="validate" novalidate="novalidate">
        <h1>Modifier le rôle d'un utilisateur</h1>
        <table class="form-table" role="presentation">
            <tr class="form-field form-required form-invalid">
                <th scope="row"><label for="username"><?php _e("Username"); ?> <span
                                class="description"><?php _e("(required)"); ?></span></label></th>
                <td><input class="to-fill" name="username" type="text" id="username"/></td>
            </tr>

        <?php if ( current_user_can('edit_users') ) { ?>
            <tr class="form-field">
                <th scope="row"><label for="role"><?php _e( 'Role' ); ?></label></th>
                <td>
                    <select name="choosen_role">
                        <option value="contributor">Membre (contributeur)</option>
                        <option value="author" selected>Responsable de groupe (auteur)</option>
                        <option value="administrator">Administrateur</option>
                    </select>
                </td>
            </tr>
            <?php } ?>
            </table>
            <?php submit_button(__("Modifier rôle"), "primary", "modifyuser", true, ["id" => "createusersub"]);
?>
    </form>
<?php
}
            ?>
