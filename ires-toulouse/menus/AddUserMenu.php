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
            /*$usersSameNickCount = count(array_filter(get_users(), function ($user) use ($userLogin) {
                return $user->nickname === preg_replace("/\d/", "", $userLogin);
            })) - 1;*/
            $usersSameNickCount = $this->getLogin($userLogin);

            $correctedUserLogin = $userLogin . ($usersSameNickCount > 0 ? $usersSameNickCount : "");

            try{
                $this->verifyPostData();
                /**
                 * Adding the user to the WordPress database
                 */
                $newuser_key = wp_generate_password( 20, false );
                $userId = wp_insert_user([
                    "user_login" => $correctedUserLogin,
                    "first_name" => $userFirstname,
                    "last_name" => $userLastname,
                    "user_pass" => $newuser_key, // automatic generated password
                    "user_email" => $userEmail,
                    "user_registered" => current_time("mysql", 1),
                    "user_status" => "0", // visitor
                    "display_name" => $correctedUserLogin
                ]);
                if(is_wp_error($userId)){
                    throw new \Exception($userId->get_error_message());
                }
                UserInputData::registerExtraMetas($userId);

                if (isset($_POST['createuser']) && isset($_POST['email']) && $newuser_key != "" && $newuser_key != null) {
                    $message =
                        'Bonjour,

Vous avez été invité à rejoindre le site %2$s avec le role de %3$s.

Identifiant : %4$s
Mot de passe : %5$s

Nous vous conseillons de modifier votre mot de passe ici :
%6$s

Vous pouvez modifier vos informations IRES en cliquant sur ce lien :
%7$s';

                    $newUser = get_userdata($userId);
                    $new_user_email['to']      = $newUser->user_email;
                    $new_user_email['subject'] = sprintf(
                    /* translators: Joining confirmation notification email subject. %s: Site title. */
                        __( '[%s] Joining Confirmation' ),
                        wp_specialchars_decode( get_option( 'blogname' ) )
                    );

                    $new_password_reset_key = get_password_reset_key($newUser);
                    $new_user_email['message'] = sprintf(
                        $message,
                        get_option( 'blogname' ),
                        home_url(),
                        wp_specialchars_decode( translate_user_role($newUser->roles[0]) ),
                        $newUser->user_login,
                        $newuser_key,
                        home_url("/wp-login.php?action=rp&key=$new_password_reset_key&login=$newUser->user_login"),
                        home_url("/wp-admin/admin.php?page=information_ires")
                    );
                    $new_user_email['headers'] = '';

                    $new_user_email = apply_filters( 'invited_user_email', $new_user_email, $newUser, $newUser->roles[0], $newuser_key );
                    wp_mail(
                        $new_user_email['to'],
                        $new_user_email['subject'],
                        $new_user_email['message'],
                        $new_user_email['headers']
                    );

                    ?>
                    <div id="message" class="updated notice is-dismissible">
                        <form action="<?php echo get_site_url() ?>/wp-admin/admin.php?page=information_ires" id="postUser" method="post">
                            <input type="hidden" name="users" value="<?php echo $userId; ?>">
                        </form>
                        <p><strong>L'utilisateur <?php echo $correctedUserLogin ?> (ID: <?php echo $userId ?>) a été bien
                                enregistré, <a href='#'
                                               onclick='document.getElementById("postUser").submit()'>
                                    vous pouvez renseigner ses informations ici</a></strong></p>
                    </div>
                    <?php
                }
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

                if(!in_array($inputId, ["first_name", "last_name", "email"])){
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
            ?>

        </form>
        <?php
    }

    /**
     * @param $user_login string the login of the futur user
     * @return int the highest number + 1 of the different login
     */
    private function getLogin($user_login): int {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("SELECT user_login FROM wp_users WHERE user_login LIKE %s ORDER BY ID", $user_login."%"), ARRAY_A);
        $str = (int) explode($user_login, end($results)['user_login'])[1];
        return $str === 0 ? get_userdatabylogin($user_login) === false ? -1 : 1 : $str + 1;
    }
}