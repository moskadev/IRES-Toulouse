<?php

namespace irestoulouse\menus;

use Exception;
use irestoulouse\controllers\UserConnection;
use irestoulouse\controllers\UserInputData;
use irestoulouse\elements\input\UserData;
use irestoulouse\utils\Dataset;

use WP_User;

/**
 * Creation of the plugin page
 * This page will allow you to add a user by a manager
 * The manager will have to fill in the following information:
 *      - E-mail
 *      - First name
 *      - Last name
 */
class UserRegisterMenu extends IresMenu {

    /** @var WP_User|null */
    private ?WP_User $loggedUser = null;

    public function __construct() {
        parent::__construct(
            "Création d'un profil IRES", // Page title when the menu is selected
            "Ajouter un compte", // Name of the menu
            2, // Menu access security level
            "dashicons-admin-users", // Menu icon
            3 // Page position in the list
        );
    }

    public function analyzeSentData() : void {
        if (!empty($_POST["first_name"]) &&
            !empty($_POST["last_name"]) &&
            !empty($_POST["user_email"])
        ) {
            try {
                $connection = new UserConnection(
                    $_POST["first_name"],
                    $_POST["last_name"],
                    $_POST["user_email"]);

                UserInputData::checkSentData();
                $this->loggedUser = $connection->register(); ?>

                <div id="message" class="updated notice is-dismissible">
                    <p><strong>L'utilisateur <?php echo $this->loggedUser->user_login ?> a
                            été bien enregistré, <a href='admin.php?page=mon_profil_ires'>
                                vous pouvez renseigner ses informations ici</a></strong>
                    </p>
                </div> <?php
            } catch (Exception $e) { ?>
                <div id="message" class="error notice is-dismissible">
                    <p><strong><?php echo $e->getMessage() ?></strong></p>
                </div>
            <?php }
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
    public function getContent() : void {?>
        <form method='post' class='verifiy-form validate' novalidate='novalidate'>
            <table class='form-table' role='presentation'>
                <?php
                foreach (UserData::all() as $data) {
                    $formType = $data->getFormType();
                    $dataId = $data->getId();

                    if (!$data->isWordpressMeta() ||
                        $dataId === "user_login" && $this->loggedUser === null) {
                        continue;
                    }

                    $value = "";
                    if(isset($_POST[$dataId])){
                        $value = $_POST[$dataId];
                    } else if($this->loggedUser !== null) {
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
                                    class='form-control'
                                    type='<?php echo $formType ?>'
                                    id='<?php echo $dataId ?>'
                                    name='<?php echo $dataId ?>'
                                    value='<?php echo $value ?>'>
                            <?php
                            if (!empty($data->getDescription())) { ?>
                                <p class="description"><?php _e($data->getDescription()) ?></p>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                } ?>
            </table>
            <button class="btn btn-outline-primary menu-submit" type="submit"
                    name="createuser" disabled>
                Créer un nouveau compte IRES
            </button>
        </form>
        <?php
    }
}