<?php
/**
 * @package Create_Account
 * @version 1.0.0
 */
/*
Plugin Name: Create Account
Description: Create an account without using the WordPress panel
Author: IUT Rodez
Version: 1.0.0
*/

/**
 * Creation of the plugin page
 * This page will allow you to add a user by a manager
 * The manager will have to fill in the following information:
 *      - E-mail
 *      - First name
 *      - Last name
 */
function my_page() {
    add_menu_page(
        "Ajouter un utilisateur", // Page title when the menu is selected
        "Ajouter compte", // Name of the menu
        0, // Menu access security level
        "add-account", // Menu reference name
        "page_content", // Calling the page content function
        "dashicons-admin-users", // Menu icon
        3 // Page position in the list
    );
}

/**
 * Adding the menu in the dashboard of the WordPress administration
 */
add_action("admin_menu", "my_page");

/**
 * Contents of the "Add a user" menu
 * Allows to :
 *      - Display a form to add a user (E-mail, First name, Last name)
 *      - Create the user's login in the format:
 *          first letter of the first name concatenated with the last name all in lower case
 *      - Add the user to the database
 */
function page_content() {?>
    <h1>Créer un compte d'un utilisateur.</h1>

    <form method="post" name="createuser" id="createuser" class="validate" novalidate="novalidate"
        <?php
        /**
         * This action is documented in wp-admin/user-new.php.
         */
        do_action("user_new_form_tag"); ?>>

        <input name="action" type="hidden" value="createuser"/>
        <?php

        wp_nonce_field("create-user", "_wpnonce_create-user");
        // Load past data, otherwise set a default value
        $creating = isset($_POST["createuser"]);

        $new_user_login = $creating && isset($_POST["user_login"]) ? wp_unslash($_POST["user_login"]) : "";
        $new_user_firstname = $creating && isset($_POST["first_name"]) ? wp_unslash($_POST["first_name"]) : "";
        $new_user_lastname = $creating && isset($_POST["last_name"]) ? wp_unslash($_POST["last_name"]) : "";
        $new_user_email = $creating && isset($_POST["email"]) ? wp_unslash($_POST["email"]) : "";
        $new_user_uri = $creating && isset($_POST["url"]) ? wp_unslash($_POST["url"]) : "";
        $new_user_role = $creating && isset($_POST["role"]) ? wp_unslash($_POST["role"]) : "";
        $new_user_send_notification = !($creating && !isset($_POST["send_user_notification"]));
        $new_user_ignore_pass = $creating && isset($_POST["noconfirmation"]) ? wp_unslash($_POST["noconfirmation"]) : "";

        ?>
        <table class="form-table" role="presentation">

            <tr class="form-field form-required">
                <th scope="row"><label for="email"><?php _e("Email"); ?> <span
                                class="description"><?php _e("(required)"); ?></span></label></th>
                <td><input class="to-fill" name="email" type="email" id="email" value="<?php echo esc_attr($new_user_email); ?>"/></td>
            </tr>
            <?php if (!is_multisite()) { ?>
                <tr class="form-field form-required">
                    <th scope="row"><label for="first_name"><?php _e("First Name"); ?> <span
                                    class="description"><?php _e("(required)"); ?></span></label></th>
                    <td><input class="to-fill" name="first_name" type="text" id="first_name"
                               value="<?php echo esc_attr($new_user_firstname); ?>"/></td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row"><label for="last_name"><?php _e("Last Name"); ?> <span
                                    class="description"><?php _e("(required)"); ?></span></label></th>
                    <td><input class="to-fill" name="last_name" type="text" id="last_name"
                               value="<?php echo esc_attr($new_user_lastname); ?>"/></td>
                </tr>
            <?php } // End if ! is_multisite().	?>
        </table>

        <?php
        /**
         * This action is documented in wp-admin/user-new.php
         */
        do_action("user_new_form", "add-new-user");

        submit_button(__("Add New User"), "primary", "createuser", true, ["id" => "createusersub", "disabled" => "true"]);
        /**
         * Creation of the user's login
         */
        $first_char_firstname = substr($new_user_firstname, 0, 1);
        $new_user_login = strtolower($first_char_firstname . $new_user_lastname);
        /**
         * Adding the user to the WordPress database
         */
        $user_id = wp_insert_user([
            "user_login" => $new_user_login,
            "user_pass" => null,
            "user_email" => $new_user_email,
            "user_registered" => current_time("mysql", 1),
            "user_status" => "0",
            "display_name" => $new_user_login
        ]);
        if (!is_wp_error($user_id)) {
            echo "L'utilsateur $new_user_login a été ajouté";
        }
        ?>
    </form>
    <script>
        const forms = document.querySelectorAll("form");
        forms.forEach(function(form) {
            const formInputs = [...form.querySelectorAll(".to-fill")];
            const buttonCreate = form.querySelector("input[type=submit]");

            // cursor "prohibited" by default on the validation button
            buttonCreate.style.cursor = "not-allowed";

            // add the input event to the form inputs
            form.addEventListener("input", function () {
                buttonCreate.disabled = !areFilled();
                buttonCreate.style.cursor = areFilled() ? "pointer" : "not-allowed";
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
                        // basic validation regEx for emails
                        // https://stackoverflow.com/a/48800
                        filled = input.id === "email" ?
                            input.value.match(/^\S+@\S+\.\S+$/g) :
                            input.value;
                    }
                });
                return filled;
            }
        });
    </script>
    <?php
}
