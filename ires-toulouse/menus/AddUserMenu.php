<?php

namespace irestoulouse\menus;

use irestoulouse\elements\input\UserInputData;
use irestoulouse\utils\Dataset;

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
    public function getContent(): void {
        $creating = isset($_POST["createuser"]);
        $userId = -1;

        if($creating) {
            $userFirstname = isset($_POST["first_name"]) ? wp_unslash($_POST["first_name"]) : "";
            $userLastname = isset($_POST["last_name"]) ? wp_unslash($_POST["last_name"]) : "";
            $userEmail = isset($_POST["email"]) ? wp_unslash($_POST["email"]) : "";
            /**
             * Creation of the user's login
             */
            $firstChar = substr($userFirstname, 0, 1);
            $userLogin = strtolower($firstChar . $userLastname);

            /**
             * We verify if the same nickname/user login and so we count the quantity of users
             * with the same nickname by deleting the numbers
             * We also reduce it by 1 because the current user is already in the array too,
             * it's useless to count it
             */
            $usersSameNickCount = count(array_filter(get_users(), function ($user) use ($userLogin) {
                return $user->nickname === preg_replace("/\d/", "", $userLogin);
            })) - 1;
            $correctedUserLogin = $userLogin . ($usersSameNickCount > 0 ? $usersSameNickCount : "");

            try{
                $this->verifyPostData();
                /**
                 * Adding the user to the WordPress database
                 */
                $userId = wp_insert_user([
                    "user_login" => $correctedUserLogin,
                    "first_name" => $userFirstname,
                    "last_name" => $userLastname,
                    "user_pass" => "test", //wp_generate_password(), // automatic generated password
                    "user_email" => $userEmail,
                    "user_registered" => current_time("mysql", 1),
                    "user_status" => "0", // visitor
                    "display_name" => $correctedUserLogin
                ]);
                if(is_wp_error($userId)){
                    throw new \Exception($userId->get_error_message());
                }
                UserInputData::registerExtraMetas($userId);?>
                <div id="message" class="updated notice is-dismissible">
                    <p><strong>L'utilisateur <?php echo $correctedUserLogin ?> (ID: <?php echo $userId ?>) a été bien
                            enregistré, <a href='admin.php?page=renseigner_des_informations'>
                                vous pouvez renseigner ses informations ici</a></strong></p>
                </div> <?php
            } catch(\Exception $e) {?>
                <div id="message" class="error notice is-dismissible">
                    <p><strong><?php echo "$correctedUserLogin : " . $e->getMessage() ?></strong></p>
                </div>
            <?php }
        }?>
        <h1>Créer un compte d'un utilisateur</h1>
        <form method='post' name='createuser' id='createuser' class='verifiy-form validate' novalidate='novalidate'>
        <?php
        /**
         * This action is documented in wp-admin/user-new.php.
         */
        do_action("user_new_form_tag");?>

        <input name='action' type='hidden' value='createuser'>

        <?php
        wp_nonce_field("create-user", "_wpnonce_create-user");
        // Load past data, otherwise set a default value

        foreach(UserInputData::all() as $inputData){
            $inputFormType = $inputData->getFormType();
            $inputId = $inputData->getId();

             if(!in_array($inputId, ["nickname", "first_name", "last_name", "email"])){
                 continue;
             }?>
             <table class='form-table' role='presentation'>
                 <tr class="form-field form-required">
                     <th>
                         <label for='<?php echo $inputId ?>'>
                             <?php
                             _e($inputData->getName());
                             if($inputData->isRequired()){?>
                                 <span class='description'><?php _e("(required)") ?></span>
                                 <?php
                             } ?>
                         </label>
                     </th>
                     <td>
                         <?php
                         if(in_array($inputData->getFormType(), ["text", "email"])){?>
                             <input <?php echo Dataset::allFrom($inputData)?>
                                     class='<?php if($inputId === "nickname") echo "update-nickname" ?>'
                                     type='<?php echo htmlspecialchars($inputFormType) ?>'
                                     id='<?php echo htmlspecialchars($inputId) ?>'
                                     name='<?php echo htmlspecialchars($inputId) ?>'
                                     value='<?php echo $creating && isset($_POST[$inputId]) ? wp_unslash($_POST[$inputId]) : "" ?>'>
                             <?php
                             if(!empty($inputData->getDescription())){?>
                                 <p class="description"><?php _e($inputData->getDescription()) ?></p>
                             <?php }
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

        /**
         * Temporaire : la génération de mdp auto est fait mais le mail le contenant
         * doit être codé
         */
        if($userId > -1) {
            echo "<h2>Mot de passe : test </h2>";
            }
        ?>

        </form>
    <?php
    }
}