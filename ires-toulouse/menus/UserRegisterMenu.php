<?php

namespace irestoulouse\menus;

use irestoulouse\controllers\UserInputData;
use irestoulouse\controllers\UserConnection;
use irestoulouse\elements\input\UserData;
use irestoulouse\utils\Dataset;

/**
 * Creation of the plugin page
 * This page will allow you to add a user by a manager
 * The manager will have to fill in the following information:
 *      - E-mail
 *      - First name
 *      - Last name
 */
class UserRegisterMenu extends IresMenu {

    public function __construct() {
        parent::__construct(
            "Création d'un profil IRES", // Page title when the menu is selected
            "Créer un profil", // Name of the menu
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
    public function getContent() : void {
        $loggedUser = null;

        if (isset($_POST["first_name"]) && isset($_POST["last_name"]) && isset($_POST["email"])) {
            $userFirstname = $_POST["first_name"] ?? "";
            $userLastname = $_POST["last_name"] ?? "";
            $userEmail = $_POST["email"] ?? "";

            try {
                $connection = new UserConnection($userFirstname, $userLastname, $userEmail);

                UserInputData::checkSentData();
                $loggedUser = $connection->register();
                ?>
                <div id="message" class="updated notice is-dismissible">
                    <p><strong>L'utilisateur <?php echo $loggedUser->user_login ?> a
                            été bien enregistré, <a href='admin.php?page=profil_ires'>
                                vous pouvez renseigner ses informations ici</a></strong>
                    </p>
                </div> <?php
            } catch (\Exception $e) { ?>
                <div id="message" class="error notice is-dismissible">
                    <p><strong><?php echo $e->getMessage() ?></strong></p>
                </div>
            <?php }
        } ?>
        <form method='post' class='verifiy-form validate' novalidate='novalidate'>
            <?php

            foreach (UserData::all() as $inputData) {
                $inputFormType = $inputData->getFormType();
                $inputId = $inputData->getId();

                if (!in_array($inputId, [
                    "nickname",
                    "first_name",
                    "last_name",
                    "email"
                ])) {
                    continue;
                } ?>
                <table class='form-table' role='presentation'>
                    <tr class="form-field form-required">
                        <th>
                            <label for='<?php echo $inputId ?>'>
                                <?php
                                _e($inputData->getName());
                                if ($inputData->isRequired()) {
                                    ?>
                                    <span class='description'><?php _e("(required)") ?></span>
                                    <?php
                                } ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            if (in_array($inputData->getFormType(), ["text", "email"])) {?>
                                <input <?php echo Dataset::allFrom($inputData) ?>
                                        type='<?php echo htmlspecialchars($inputFormType) ?>'
                                        id='<?php echo htmlspecialchars($inputId) ?>'
                                        name='<?php echo htmlspecialchars($inputId) ?>'
                                        value='<?php echo ($inputId === "nickname" && $loggedUser !== null ?
                                            $loggedUser->user_login : ($_POST[$inputId] ?? "")) ?>'>
                                <?php
                                if (!empty($inputData->getDescription())) {
                                    ?>
                                    <p class="description"><?php _e($inputData->getDescription()) ?></p>
                                <?php }
                            } ?>
                        </td>
                    </tr>
                </table>
                <?php
            }
            submit_button(__("Add New User"), "primary", "createuser",
                true, ["id" => "createusersub", "disabled" => "true"]
            );
            ?>
        </form>
        <?php
    }
}