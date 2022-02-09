<?php

namespace irestoulouse\menus;

include_once("IresMenu.php");

use Exception;
use irestoulouse\controllers\UserInputData;
use irestoulouse\controllers\UserModification;
use irestoulouse\elements\Group;
use irestoulouse\elements\input\UserData;
use irestoulouse\utils\Dataset;

/**
 * Creation of the plugin page
 * This page will allow you to modify your personal information
 * The user will have to modify all the following information:
 *      - LAST NAME (IN UPPER CASE)
 *      - FIRST NAME (IN UPPERCASE)
 *      - EMAIL (professional if possible)
 *      - PHONE (I authorize the display of my phone number on the IRES website)
 *      - CAPES
 *      - CAPET
 *      - AGREGATION
 *      - CRPE
 *      - CAFFA
 *      - CAPLP
 *      - CAPPEI
 *      - THESIS
 *      - PROFESSIONAL SITUATION
 *      - DISCIPLINE TAUGHT
 *      - WORKING TIME
 *      - NAME OF THE INSTITUTION
 *      - SCHOOL CITY
 *      - SCHOOL"S UAI/RNE CODE
 *      NAME OF THE HEAD OF THE SCHOOL
 *      - PAF TRAINING LEADER 2018/2019
 *      - IF YES, TITLE OF THE TRAINING
 *      - PARTICIPATION IN A MATH LAB
 *      - Are you a member of INSPE?
 *      - DO YOU DO ANY INTERVENTIONS AT THE INSPE?
 *      - CII MEMBER
 *      - MEMBER OF A TEACHER ASSOCIATION (APMEP, ...)
 *      - MEMBER of a learned society (Société Mathématique de France, Société Française de Physique, ...)
 *      - ASSOCIATION MEMBER (OTHER)
 */
class UserProfileMenu extends IresMenu {

    /**
     * Constructing the menu and link to the admin page
     */
    public function __construct() {
        parent::__construct("Consulter les informations relatifs à votre profil IRES", // Page title when the menu is selected
            "Profil IRES", // Name of the menu
            0, // Menu access security level
            "dashicons-id-alt", // Menu icon
            3 // Page position in the list
        );
    }

    /**
     * Content of the page
     */
    public function getContent() : void {
        $isResp = current_user_can("responsable") || current_user_can('administrator');
        $seeableUsers = Group::getSeeableUsers(wp_get_current_user());
        $disableAll = false;

        if (count($seeableUsers) > 0) {
            $editingUser = isset($_POST["editingUserId"]) ?
                get_user_by("id", $_POST["editingUserId"] ?? get_current_user_id()) :
                wp_get_current_user();

            /**
             * If admin, it gets the last created user or chosen user
             * If responsable, verify if he's responsible for the user
             * else chose itself
             */
            if (!in_array($editingUser, $seeableUsers)) {
                $seeableUsers = wp_get_current_user(); ?>
                <div id="message" class="error notice is-dismissible">
                    <p><strong>Vous n'avez pas la permission de modifier cet
                            utilisateur.</strong></p>
                </div>
            <?php }
        } else {
            $editingUser = wp_get_current_user();
        }
        $modification = new UserModification($editingUser);
        $inputsData = new UserInputData($editingUser);

        if (isset($_POST["action"]) && $_POST["action"] == "modifyuser") {
            try {
                UserInputData::checkSentData();
                $modification->updateAllUserData(); ?>
                <div id="message" class="updated notice is-dismissible">
                    <p><strong>Modification des informations de l'utilisateur
                            <?php echo $editingUser->user_login ?> ont été bien
                            effectuées </strong></p>
                </div> <?php
            } catch (Exception $e) { ?>
                <div id="message" class="error notice is-dismissible">
                    <p><strong>Erreur : <?php echo $e->getMessage() ?></strong></p>
                </div>
            <?php }
        }

        if ($isResp) { ?>
            <form method='post' name='to-modify-user' id='to-modify-user'
                  class='validate' novalidate='novalidate'>
                <table class='form-table' role='presentation'>
                    <tr class="form-field form-required">
                        <th>
                            <label for='editingUserId'>
                                Sélectionnez l'utilisateur à modifier
                            </label>
                        </th>
                        <td>
                            <select name="editingUserId"><?php
                                foreach ($seeableUsers as $user) {
                                    ?>
                                    <option value='<?php echo $user->ID ?>' <?php if ($user == $editingUser)
                                        echo "selected" ?>>
                                        <?php echo $user->nickname ?>
                                    </option>
                                <?php }
                                ?></select>
                            </select>
                        </td>
                    </tr>
                </table>
                <button class="btn btn-success" type="submit" id="to-modify-user-btn">
                    Modifier cet utilisateur
                </button>
                <p class='description'>Veuillez valider si vous avez sélectionné
                    un nouveau utilisateur</p>
            </form>
            <?php
        } else {
            $disableAll = true;
        }
        ?>

        <form method='post' name='modify-user' id='modify-user'
              class='verifiy-form validate' novalidate='novalidate'>
            <input name='editingUserId' type='hidden'
                   value='<?php echo $editingUser->ID ?>'>
            <input name='action' type='hidden' value='modifyuser'><?php
            foreach (UserData::all() as $inputData) {
                $inputFormType = $inputData->getFormType();
                $inputId = $inputData->getId();

                if ($inputFormType === "label") {
                    echo "<h2>" . $inputData->getName() . "</h2>";
                    continue;
                } ?>
                <table class='form-table' role='presentation'>
                    <tr class="form-field form-required">
                        <th>
                            <!-- Creating the title of input -->
                            <label for='<?php echo $inputId ?>'> <?php
                                _e($inputData->getName());
                                if ($inputData->isRequired()) {
                                    ?>
                                    <span class='description'><?php _e("(required)") ?></span> <?php
                                } ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            if (in_array($inputFormType, ["text", "email"])) {
                                ?>
                                <input <?php echo Dataset::allFrom($inputData) ?>
                                        class="form-control"
                                        type='<?php echo htmlspecialchars($inputFormType) ?>'
                                        id='<?php echo htmlspecialchars($inputId) ?>'
                                        name='<?php echo htmlspecialchars($inputId) ?>'
                                        value='<?php echo htmlspecialchars($inputsData->getInputValue($inputId)); ?>'
                                    <?php if ($disableAll)
                                        echo "disabled" ?>>
                                <?php
                            } else if ($inputFormType === "radio") {
                                $value = filter_var($inputsData->getInputValue($inputId), FILTER_VALIDATE_BOOLEAN); ?>
                                Oui <input <?php echo Dataset::allFrom($inputData) ?>
                                        class="form-control"
                                        type="radio"
                                        id='<?php echo htmlspecialchars($inputId) ?>_oui'
                                        name='<?php echo htmlspecialchars($inputId) ?>'
                                        value="true"
                                    <?php if ($value == true)
                                        echo "checked" ?>
                                    <?php if ($disableAll)
                                        echo "disabled" ?>>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                Non <input <?php echo Dataset::allFrom($inputData) ?>
                                        class="form-control"
                                        type="radio"
                                        id='<?php echo htmlspecialchars($inputId) ?>_non'
                                        name='<?php echo htmlspecialchars($inputId) ?>'
                                        value="false"
                                    <?php if ($value == false)
                                        echo "checked" ?>
                                    <?php if ($disableAll)
                                        echo "disabled" ?>>
                                <?php
                            } else if (in_array($inputFormType, [
                                "dropdown",
                                "checklist"
                            ])) { ?>
                                <select <?php if ($inputFormType === "checklist")
                                    echo "multiple" ?>
                                        name='<?php echo $inputId ?>[]'
                                        class="form-control"
                                        id='<?php echo $inputId ?>'
                                    <?php if ($disableAll)
                                        echo "disabled" ?>> <?php
                                    /**
                                     * Extra data are checked individually and put in the dropdown or checklist
                                     * Multiple items can be selected for checklist, so we check if the user
                                     * has those extra data
                                     */
                                    foreach ($inputData->getExtraData() as $data) {
                                        ?>
                                        <!-- value of the option -->
                                        <option value='<?php echo $data ?>'
                                            <?php if ($modification->containsExtraData($inputData, $data))
                                                echo "selected" ?>>
                                            <!-- check if the extra data has been selected by the user -->
                                            <?php echo $data ?>
                                            <!-- the option's text -->
                                        </option> <?php
                                    } ?>
                                </select> <?php
                            }
                            if (!empty($inputData->getDescription())) { ?>
                                <p class="description"><?php _e($inputData->getDescription()) ?></p>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
                <?php
            }
            if (!$disableAll) { ?>
                <button class="btn btn-outline-primary menu-submit" type="submit"
                        name="profile-page" id="profile-page-sub" disabled>
                    Modifier les informations
                </button>
            <?php }
            ?>
        </form> <?php
    }
}