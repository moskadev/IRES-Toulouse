<?php

namespace irestoulouse\menus\users;

use Exception;
use irestoulouse\controllers\InputDataController;
use irestoulouse\controllers\UserConnectionController;
use irestoulouse\data\UserCustomDataFactory;
use irestoulouse\menus\Menu;
use irestoulouse\menus\MenuFactory;
use irestoulouse\menus\MenuIds;
use irestoulouse\utils\Dataset;
use WP_User;

/**
 * This page will allow you to add a user by a manager
 * The manager will have to fill in the following information:
 *      - E-mail
 *      - First name
 *      - Last name
 *
 * @version 2.0
 */
class UserRegisterMenu extends Menu {

    /** @var WP_User|null */
    private ?WP_User $loggedUser = null;

    /**
     * Initializing the menu
     */
    public function __construct() {
        parent::__construct(MenuIds::USER_REGISTER_MENU, "Ajouter un compte",
            "Création d'un profil IRES", 3, "dashicons-admin-users", 3
        );
    }

    /**
     * Checking if a user can be created, if a first name, last name and
     * e-mail has been sent. It calls a controller and it will try to
     * register from the following given params the user
     *
     * @param array $params $_GET and $_POST combined
     */
    public function analyzeParams(array $params) : void {
        if (strlen($nom = trim($params["first_name"] ?? "")) > 0 &&
            strlen($prenom = trim($params["last_name"] ?? "")) > 0 &&
            strlen($email = trim($params["user_email"] ?? "")) > 0
        ) {
            try {
                $connection = new UserConnectionController($nom, $prenom, $email);

                InputDataController::checkSentData();
                $this->loggedUser = $connection->register();

                $this->showNoticeMessage("updated",
                    "L'utilisateur " . $this->loggedUser->user_login . "  a été bien enregistré, 
                    <a href='" . MenuFactory::fromId(MenuIds::USER_PROFILE_MENU)->getPageUrl($this->loggedUser->ID) . "'>
                        vous pouvez renseigner ses informations ici
                    </a>"
                );
            } catch (Exception $e) {
                $this->showNoticeMessage("error", $e->getMessage());
            }
        }
    }

    /**
     * Contents of the "Add a user" menu
     * Allows to :
     *      - Display a form to add a user (E-mail, First name, Last name)
     *      - Create the user's login in the format:
     *          first letter of the first name concatenated with the last name all in lower case
     *      - Add the user to the database
     */
    public function showContent() : void { ?>
        <form method='post' class='verifiy-form'>
            <table class='form-table' role='presentation'>
                <?php
                foreach (UserCustomDataFactory::all() as $data) {
                    $formType = $data->getFormType();
                    $dataId = $data->getId();

                    if (!$data->isWordpressMeta() ||
                        $dataId === "user_login" && $this->loggedUser === null) {
                        continue;
                    }

                    $value = "";
                    if (isset($_POST[$dataId])) {
                        $value = $_POST[$dataId];
                    } else if ($this->loggedUser !== null) {
                        $value = $data->getValue($this->loggedUser);
                    }
                    ?>
                    <tr class="form-field form-required">
                        <th>
                            <label for='<?php echo $dataId ?>'>
                                <?php
                                _e($data->getName());
                                if ($data->isRequired()) {
                                    ?>
                                    <span class='description'><?php _e("(required)") ?></span>
                                    <?php
                                } ?>
                            </label>
                        </th>
                        <td>
                            <input <?php echo Dataset::allFrom($data) ?>
                                    type='<?php echo $formType ?>'
                                    id='<?php echo $dataId ?>'
                                    name='<?php echo $dataId ?>'
                                    value='<?php echo $value ?>'>
                            <?php
                            if (strlen($data->getDescription()) > 0) { ?>
                                <p class="description"><?php _e($data->getDescription()) ?></p>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                } ?>
            </table>
            <button class="button-primary menu-submit button-large" type="submit"
                    name="createuser" disabled>
                <span class="dashicons dashicons-insert"></span>
                Créer un nouveau compte IRES
            </button>
        </form>
        <?php
    }
}