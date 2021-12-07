<?php

namespace irestoulouse\menus;

/**
 * Creation of the plugin page
 * This page will allow you to change a role of an util
 * The user that you want to modify the permission have to enter :
 *      - Username
 *      - the permission that you want grant him
 */
class AffectionRoleMenu extends IresMenu {

    public function __construct() {
        register_activation_hook( __FILE__, function (){
            add_role( 'responsable', 'Responsable', array('level_0' => true) );
        });
        register_deactivation_hook( __FILE__, function () {
            remove_role('responsable');
        });
        parent::__construct("Affecter un rôle", // Page title when the menu is selected
            "Affecter un rôle", // Name of the menu
            10, // Menu access security level
            "dashicons-id-alt", // Menu icon
            3 // Page position in the list
        );
    }

    /**
     * Contents of the "Modify a user" menu
     * Allows to :
     *      - change a permission of an user if you are admin
     */
    public function getContent() : void {
    	// Check that the form has been sent
    	if(isset($_POST['username']) ) {
            $user_login = $_POST['username'];

    		$choice = $_POST['choosen_role'];
    		$name_role = ( $choice === 'subscriber' ) ? 'membre' : 'responsable';
    		$message="$user_login est maintenant $name_role.";
    		$type_message="error";

    		// Check if the login name submit exist
    		if ( username_exists( $user_login ) != null ) {
    			$user = get_userdatabylogin( $user_login );

    			// The role for the user haven't been changed because he already had the role choose
    			if (!in_array( "$choice", (array) $user->roles )) {
    				$user = wp_update_user( array( 'ID' => $user->id, 'role' => $choice ) );
    				$type_message = "updated";
    			} else {

    				// Determine the displayed role name
    				$message ="Rien n'a été effectué, $user_login était déjà $name_role.";
    			}
    		} else if ( username_exists( $user_login ) == null ) {
    			$message = "Rien n'a été effectué, $user_login n'est pas un identifiant valide.";
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
                <tr class="form-field form-required">
                    <th scope="row"><label for="username"><?php _e("Username"); ?> <span
                                    class="description"><?php _e("(required)"); ?></span></label></th>
                    <td><input class="to-fill" name="username" type="text" id="username"/></td>
                </tr>

    			<?php if ( current_user_can('edit_users') ) { ?>
                    <tr class="form-field">
                        <th scope="row"><label for="role"><?php _e( 'Role' ); ?></label></th>
                        <td>
                            <select name="choosen_role">
                                <option value="subscriber">Membre</option>
                                <option value="responsable" selected>Responsable</option>
                            </select>
                        </td>
                    </tr>
    			<?php } ?>
                <tr>
                    <th><label for="group"><?php echo _e('Group'); ?></label></th>
                    <td>
                        <select name="group-selection">
                            <option>Groupe 1</option>
                            <option selected>Groupe 2</option>
                        </select>
                    </td>
                </tr>
            </table>
    		<?php submit_button(__("Modifier rôle"), "primary", "modifyuser", true, ["id" => "createusersub"]);
    		?>
        </form>
    	<?php
    }
}