<?php

namespace irestoulouse\menus;

use irestoulouse\elements\UserData;
use irestoulouse\utils\Dataset;

/**
 * TODO Refaire la classe en utilisant UserData.php, un peu comme ModifyUserDataMenu
 */

/**
 * Creation of the plugin page
 * This page will allow you to add a user by a manager
 * The manager will have to fill in the following information:
 *      - E-mail
 *      - First name
 *      - Last name
 */
class AddUserMenu extends IresMenu {

    public function __construct() {
        parent::__construct(
            "Ajouter utilisateur", // Page title when the menu is selected
            "Ajouter compte", // Name of the menu
            2, // Menu access security level
            "dashicons-admin-users", // Menu icon
            3 // Page position in the list
        );
    }

    /**
     * Contents of the "Add a user" menu
     * Allows to :
     *      - Display a form to add a user (E-mail, First name, Last name)
     *      - Create the user's login in the format:
     *          first letter of the first name concatenated with the last name all in lower case
     *      - Add the user to the database
     */
    public function getContent(): void {?>
        <h1>Créer un compte d'un utilisateur</h1>
        <form method='post' name='createuser' id='createuser' class='validate' novalidate='novalidate'>
        <?php
        /**
         * This action is documented in wp-admin/user-new.php.
         */
        do_action("user_new_form_tag");?>

        <input name='action' type='hidden' value='createuser'>

        <?php
        wp_nonce_field("create-user", "_wpnonce_create-user");
        // Load past data, otherwise set a default value
        $creating = isset($_POST["createuser"]);

        foreach(UserData::all() as $userData){
             if(!in_array($userData->getId(), ["nickname", "first_name", "last_name", "email"])){
                 continue;
             }?>
             <table class='form-table' role='presentation'>
                 <tr class="form-field form-required">
                     <th>
                         <label for='<?php echo $userData->getId() ?>'>
                             <?php
                             _e($userData->getName());
                             if($userData->isRequired()){?>
                                 <span class='description'><?php echo _e("(required)") ?></span>
                                 <?php
                             } ?>
                         </label>
                     </th>
                     <td>
                         <?php
                         if(in_array($userData->getType(), ["text", "email", "checkbox"])){?>
                             <input <?php
                             if($userData->isDisabled()) echo "disabled class='disabled' "; echo Dataset::allFrom($userData)?>
                                     type='<?php echo $userData->getType() ?>'
                                     id='<?php echo $userData->getId() ?>'
                                     name='<?php echo $userData->getId() ?>'
                                     value='<?php echo $creating && isset($_POST[$userData->getId()]) ? wp_unslash($_POST[$userData->getId()]) : "" ?>'>
                             <?php
                         }?>
                     </td>
                 </tr>
              </table>
        <?php
        }

        /**
         * This action is documented in wp-admin/user-new.php
         */
        do_action("user_new_form", "add-new-user");

        submit_button(__("Add New User"), "primary", "createuser",
            true, ["id" => "createusersub", "disabled" => "true"]);
        if($creating) {
            $userFirstname = isset($_POST["first_name"]) ? wp_unslash($_POST["first_name"]) : "";
            $userLastname = isset($_POST["last_name"]) ? wp_unslash($_POST["last_name"]) : "";
            $userEmail = isset($_POST["email"]) ? wp_unslash($_POST["email"]) : "";
            /**
             * Creation of the user's login
             */
            $firstChar = substr($userFirstname, 0, 1);
            $userLogin = strtolower($firstChar . $userLastname);
            $usersSameNickCount = count(array_filter(get_users(), function ($user) use ($userLogin) {
                return $user->nickname === preg_replace("/\d/", "", $userLogin);
            }));
            $correctedUserLogin = $userLogin . ($usersSameNickCount > 1 ? $usersSameNickCount - 1 : "");

            /**
             * Adding the user to the WordPress database
             */
            $userId = wp_insert_user([
                "user_login" => $correctedUserLogin,
                "first_name" => $userFirstname,
                "last_name" => $userLastname,
                "user_pass" => wp_generate_password(), // automatic generated password
                "user_email" => $userEmail,
                "user_registered" => current_time("mysql", 1),
                "user_status" => "0", // visitor
                "display_name" => $correctedUserLogin
            ]);
            if (!is_wp_error($userId)) {
                UserData::registerMetas($userId);?>
                <div id="message" class="updated notice is-dismissible">
                    <p><strong>L'utilisateur <?php echo $correctedUserLogin ?> (ID: <?php echo $userId ?>) a été bien enregistré, <a href='admin.php?page=renseigner_ses_informations'>vous pouvez renseigner ses informations ici</a></strong></p>
                </div> <?php
            } else {?>
                <div id="message" class="error notice is-dismissible">
                    <p><strong>Une erreur s'est produite lors de l'enregistrement de <?php echo $correctedUserLogin ?></strong></p>
                </div>
            <?php }
        }?>

        </form>
    <?php
    }
}