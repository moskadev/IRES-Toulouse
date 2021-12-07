<?php

namespace irestoulouse\menus;

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
            0, // Menu access security level
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
        do_action("user_new_form_tag");

        echo "<input name='action' type='hidden' value='createuser'>";

        wp_nonce_field("create-user", "_wpnonce_create-user");
        // Load past data, otherwise set a default value
        $creating = isset($_POST["createuser"]);

        $necessaryData = [
            "user_login",
            "first_name",
            "last_name",
            "email",
        ];
        $new_user_firstname = $creating && isset($_POST["first_name"]) ? wp_unslash($_POST["first_name"]) : "";
        $new_user_lastname = $creating && isset($_POST["last_name"]) ? wp_unslash($_POST["last_name"]) : "";
        $new_user_email = $creating && isset($_POST["email"]) ? wp_unslash($_POST["email"]) : "";
        ?>
        <table class="form-table" role="presentation">
            <tr class="form-field form-required">
                <th><label for="user_login"><?php _e( 'Username' ); ?></label></th>
                <td><input type="text" name="user_login" id="user_login" value="" disabled class="regular-text">
                    <span class="description"><?php _e( 'Usernames cannot be changed.' ); ?></span>
                </td>
            <tr class="form-field form-required">
                <th><label for="email"><?php _e("Email"); ?> <span
                            class="description"><?php _e("(required)"); ?></span></label></th>
                <td><input class="to-fill" name="email" type="email" id="email" value="<?php echo esc_attr($new_user_email); ?>"/></td>
            </tr>
            <tr class="form-field form-required">
                <th><label for="first_name"><?php _e("First Name"); ?> <span
                            class="description"><?php _e("(required)"); ?></span></label></th>
                <td><input class="to-fill" name="first_name" type="text" id="first_name"
                           value="<?php echo esc_attr($new_user_firstname); ?>"/></td>
            </tr>
            <tr class="form-field form-required">
                <th><label for="last_name"><?php _e("Last Name"); ?> <span
                            class="description"><?php _e("(required)"); ?></span></label></th>
                <td><input class="to-fill" name="last_name" type="text" id="last_name"
                           value="<?php echo esc_attr($new_user_lastname); ?>"/></td>
            </tr>
        </table>

        <?php
        /**
         * This action is documented in wp-admin/user-new.php
         */
        do_action("user_new_form", "add-new-user");

        submit_button(__("Add New User"), "primary", "createuser",
            true, ["id" => "createusersub", "disabled" => "true"]);
        /**
         * Creation of the user's login
         */
        $firstChar = substr($new_user_firstname, 0, 1);
        $newUserLogin = strtolower($firstChar . $new_user_lastname);

        /**
         * Adding the user to the WordPress database
         */
        $user_id = wp_insert_user([
            "user_login" => $newUserLogin,
            "user_pass" => wp_generate_password(), // automatic generated password
            "user_email" => $new_user_email,
            "user_registered" => current_time("mysql", 1),
            "user_status" => "0", // visitor
            "display_name" => $newUserLogin
        ]);

        // TODO Ajouter les disciplines
        if (!is_wp_error($user_id)) {
            echo "<p>L'utilisateur $newUserLogin a été ajouté. Une notification lui a été envoyé.</p>";
        }?>
        </form>
    <?php }
}