<?php

namespace irestoulouse\menus;

use irestoulouse\utils\Identifier;

/**
 * Creation of the plugin page
 * This page will allow you to change a role of an util
 * The user that you want to modify the permission have to enter :
 *      - Username
 *      - the permission that you want grant him
 */
class AffectionRoleMenu extends IresMenu {

    public function __construct() {
        parent::__construct("Affecter un rôle", // Page title when the menu is selected
            "Affecter un rôle", // Name of the menu
            2, // Menu access security level
            "dashicons-businesswoman", // Menu icon
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
        $user = null;
        if(isset($_POST['username'])) {
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
                    wp_update_user( array( 'ID' => $user->ID, 'role' => $choice ) );
                    $type_message="updated";
                } else {

                    // Determine the displayed role name
                    $message ="Rien n'a été effectué, $user_login était déjà $name_role.";
                }
            } else if ( username_exists( $_POST['username'] ) == null ) {
                $message = "Rien n'a été effectué, $user_login n'est pas un identifiant valide.";
            } ?>
            <div id="message" class="<?php echo "$type_message";?> notice is-dismissible">
                <p><strong><?php echo "$message"; ?></strong></p>
            </div>
    		<?php
    	}?>
        <h1>Modifier le rôle d'un utilisateur</h1> <?php
        if(count(get_users()) > 1){?>
            <form method="post" name="modify-role" id="modify-role" class="validate" novalidate="novalidate">
                <table class="form-table" role="presentation">
                    <tr class="form-field form-required">
                        <th>
                            <label for='users'>
                                Sélectionner l'utilisateur à modifier <?php
                                $lastId = (int) ($_POST["users"] ?? Identifier::getLastRegisteredUser());
                                if($lastId == Identifier::getLastRegisteredUser()){ ?>
                                    <span class='description'>(sélection par défaut de la dernière création)</span>
                                <?php } ?>
                            </label>
                        </th>
                        <td>
                            <select name="username"><?php
                                foreach (get_users() as $u){
                                    if($u->ID == get_current_user_id()){
                                        continue;
                                    }
                                    ?>
                                    <option value='<?php echo $u->nickname ?>' <?php if($lastId == $u->ID) echo "selected" ?>>
                                        <?php echo $u->nickname ?>
                                    </option>
                                <?php }
                                ?></select>
                            </select>
                        </td>
                    </tr>

                    <?php if ( current_user_can('edit_users') ) { ?>
                        <tr class="form-field">
                            <th scope="row"><label for="role"><?php _e( 'Role' ); ?></label></th>
                            <td>
                                <select name="choosen_role">
                                    <option value="subscriber" <?php
                                        if($user != null && in_array("subscriber", $user->roles))
                                            echo "selected" ?>>
                                        Membre
                                    </option>
                                    <option value="responsable" <?php
                                        if($user != null && in_array("responsable", $user->roles))
                                            echo "selected" ?>>
                                        Responsable
                                    </option>
                                </select>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <th><label for="group"><?php echo _e('Groupe'); ?></label></th>
                        <td>
                            <select name="group-selection">
                                <option>Groupe 1</option>
                                <option selected>Groupe 2</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__("Modifier rôle"), "primary", "modify-role", true, ["id" => "modify-role-sub"]);
                ?>
            </form>
    	<?php
        } else { ?>
            <div id="message" class="error notice">
                <p><strong>Aucun utilisateur ne peut être modifié</strong></p>
            </div>
        <?php }
    }
}