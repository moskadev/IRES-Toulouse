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
        7, // Menu access security level
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

        // Check if the login name submit exist
        if ( username_exists( $_POST['username'] ) != null ) {
	        $user = get_userdatabylogin( $_POST['username'] );

            // The role for the user haven't been changed because he already had the role choose
            if (!in_array( "$choice", (array) $user->roles )) {
	            $user = wp_update_user( array( 'ID' => $user->id, 'role' => $choice ) );
	            echo "<script>alert(\"Le rôle a bien été modifié\")</script>";
            } else {

	            // Determine the displayed role name
	            $name_role = ( $choice === 'contributor' )
		            ? 'membre' : ( $choice === 'author' )
                    ? 'responsable de groupe' : 'administrateur';
	            echo "<script>alert(\"Rien n'a été effectué car l'utilisateur possédait déjà le rôle que vous avez affecté ($name_role)\")</script>";
            }
	    } else if ( username_exists( $_POST['username'] ) == null ) {
		    echo "<script>alert(\"Vous n'avez pas entré d'identifiant correct, la requête n'a donc pas été exécutée\")</script>";
	    }
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


     <script>
        if (document.getElementById(alert1)) {
            alert("Le rôle a bien été modifié");
        } else if (document.getElementById(alert2)) {

        }
      /*   afficher les utils s'ils sont dans la base de donnée
                searchInput.addEventListener('input', function(){
                    const input = searchInput.value;
                    const result = person.filter(item => item.name.includes(input.toLocaleLowerCase()));
                    let suggestion = '';
                    if(input !='') {
                        result.forEach(resultItem =>
                            suggestion +=
                           <div class="suggestion">${resultItem.name}</div>
                        );
                    }

                    buttonRole.disabled = !areFilled();
                    buttonRole.style.cursor = areFilled() ? "pointer" : "not-allowed";
                    document.getElementById('suggestions').innerHTML = suggestion;
               });
            });
*/
     const forms = document.querySelectorAll("form");
     forms.forEach(function(form) {
         const formInputs = [...form.querySelectorAll(".to-fill")];
         const buttonModify = form.querySelector("input[type=submit]");

         // cursor "prohibited" by default on the validation button
         buttonModify.style.cursor = "not-allowed";

         // add the input event to the form inputs
         form.addEventListener("input", function () {
             buttonModify.disabled = !areFilled();
             buttonModify.style.cursor = areFilled() ? "pointer" : "not-allowed";
         });

         /**
          * Verification of each input of the form by evaluating if they
          * have a value. For e-mails, they must respect the format
          * abc012@abc012.abc (some special characters included)
          *
          * @returns {boolean} true if the value of each input has
          *                        been entered and follows the imposed format
          */
         function areFilled() {
             let filled = true;
             formInputs.some(input => {
                 if(filled) {
                     filled = input.value;
                 }
             });
             return filled;
         }
     });
     </script>
