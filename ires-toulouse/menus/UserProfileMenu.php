<?php

namespace irestoulouse\menus;

include_once("IresMenu.php");

use Exception;
use irestoulouse\controllers\UserInputData;
use irestoulouse\elements\Group;
use irestoulouse\elements\input\UserData;
use irestoulouse\utils\Dataset;
use irestoulouse\utils\Identifier;
use WP_User;

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

    /** @var WP_User[] */
    private array $visibleUsers;

    /** @var WP_User */
    private WP_User $lastRegisteredUser;

    /** @var WP_User */
    private WP_User $editingUser;

    /** @var bool */
    private bool $disableAll;

    /**
     * Constructing the menu and link to the admin page
     */
    public function __construct() {
        $this->visibleUsers = Group::getVisibleUsers(wp_get_current_user());
        $this->lastRegisteredUser = Identifier::getLastRegisteredUser($this->visibleUsers)
            ?? wp_get_current_user();
        $this->disableAll = intval($_POST["confirm-modify"] ?? true);

        parent::__construct("Consulter les informations relatifs à votre profil IRES",
            "Mon profil IRES",
            0,
            "dashicons-id-alt",
            3
        );
    }

    public function analyzeSentData() : void {
        if (count($this->visibleUsers) > 0) {
            $this->editingUser = isset($_POST["editingUserId"]) ?
                get_userdata($_POST["editingUserId"] ?? get_current_user_id()) :
                $this->lastRegisteredUser;

            /**
             * If admin, it gets the last created user or chosen user
             * If responsable, verify if he's responsible for the user
             * else chose itself
             */
            if (!in_array($this->editingUser, $this->visibleUsers)) {
                $this->editingUser = wp_get_current_user(); ?>
                <div id="message" class="error notice is-dismissible">
                    <p><strong>Vous n'avez pas la permission de modifier cet
                            utilisateur.</strong></p>
                </div>
            <?php }
        } else {
            $this->editingUser = wp_get_current_user();
        }

        if (isset($_POST["action"]) && $_POST["action"] == "modifyuser") {
            try {
                UserInputData::checkSentData();
                foreach ($_POST as $dataId => $dataValue){
                    if(($data = UserData::fromId($dataId)) !== null){
                        $data->updateValue($dataValue, $this->editingUser);
                    }
                }?>
                <div id="message" class="updated notice is-dismissible">
                    <p><strong>Modification des informations de l'utilisateur
                            <?php echo $this->editingUser->user_login ?> ont été bien
                            effectuées </strong></p>
                </div> <?php
            } catch (Exception $e) { ?>
                <div id="message" class="error notice is-dismissible">
                    <p><strong>Erreur : <?php echo $e->getMessage() ?></strong></p>
                </div>
            <?php }
        }
    }

    /**
     * Content of the page
     */
    public function getContent() : void {
        if (current_user_can("responsable") ||
            current_user_can('administrator')){
            $this->chooseUserForm();
        }
        $this->showModificationBtn(); ?>

        <form method='post' name='modify-user' id='modify-user'
              class='verifiy-form validate' novalidate='novalidate'>
            <?php
            $this->saveEditedUser();
            $this->saveConfirmModification();
            ?>
            <input name='action' type='hidden' value='modifyuser'>
            <table class='form-table' role='presentation'><?php
                foreach (UserData::all() as $data) {
                    $formType = $data->getFormType();
                    $dataId = $data->getId();
                    $isLabel = $formType === "label"; ?>
                    <tr class="form-field form-required">
                        <th class='<?php if ($isLabel)echo "title-label" ?>'>
                            <!-- Creating the title of input -->
                            <?php if ($isLabel) {
                                echo $data->getName();
                            } else { ?>
                                <label for='<?php echo $dataId ?>'> <?php
                                    _e($data->getName());
                                    if ($data->isRequired()) { ?>
                                        <span class='description'><?php _e("(required)") ?></span> <?php
                                    } ?>
                                </label>
                            <?php } ?>
                        </th>
                        <?php if (!$isLabel) { ?>
                            <td>
                                <?php
                                if ($formType === "text") { ?>
                                    <input <?php echo Dataset::allFrom($data) ?>
                                            class="form-control"
                                            type='<?php echo htmlspecialchars($formType) ?>'
                                            id='<?php echo $dataId ?>'
                                            name='<?php echo $dataId ?>'
                                            value='<?php echo htmlspecialchars($data->getValue($this->editingUser)); ?>'
                                            <?php if ($this->disableAll) echo "disabled" ?>>
                                    <?php
                                } else if($formType === "table" && $dataId === "groupes"){
                                    $groups = $data->getExtraData($this->editingUser);
                                    if(count($groups) > 0){ ?>
                                        <table class="table groups-data">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Nom du groupe</th>
                                                    <th scope="col">Type du groupe</th>
                                                    <th scope="col">Responsables</th>
                                                    <th scope="col">Responsable de ce groupe</th>
                                                </tr>
                                            </thead>
                                            <tbody> <?php
                                                /** @var Group $group */
                                                foreach ($groups as $group){
                                                    $respNames = array_map(function($u) {
                                                        return $u->first_name . " " . $u->last_name;
                                                    }, $group->getResponsables()); ?>
                                                    <tr>
                                                        <td><a class="text-decoration-none"
                                                               href="<?php echo get_site_url() ?>/wp-admin/admin.php?page=details_du_groupe&group=<?php echo $group->getId() ?>">
                                                                <?php echo $group->getName() ?></a></td>
                                                        <td><?php echo Group::TYPE_NAMES[$group->getType()] ?></td>
                                                        <td><?php echo implode(", ", $respNames)?></td>
                                                        <td><?php echo $group->isUserResponsable($this->editingUser) ?
                                                                "Oui" : "Non" ?></td>
                                                    </tr>
                                                <?php }
                                            ?> </tbody>
                                        </table> <?php
                                    } else { ?>
                                        <p>L'utilisateur n'appartient à aucun groupe</p>
                                    <?php }
                                } else if ($formType === "radio") {
                                    $value = filter_var($data->getValue($this->editingUser),
                                        FILTER_VALIDATE_BOOLEAN); ?>
                                    Oui <input <?php echo Dataset::allFrom($data) ?>
                                            class="form-control"
                                            type="radio"
                                            id='<?php echo $dataId ?>_oui'
                                            name='<?php echo $dataId ?>'
                                            value="true"
                                            <?php if ($this->disableAll) echo "disabled" ?>
                                            <?php if ($value) echo "checked" ?>>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    Non <input <?php echo Dataset::allFrom($data) ?>
                                            class="form-control"
                                            type="radio"
                                            id='<?php echo $dataId ?>_non'
                                            name='<?php echo $dataId ?>'
                                            value="false"
                                            <?php if ($this->disableAll) echo "disabled" ?>
                                            <?php if (!$value) echo "checked" ?>>
                                    <?php
                                } else if (in_array($formType, ["dropdown", "checklist"])) { ?>
                                    <select <?php if ($formType === "checklist")
                                        echo "multiple" ?>
                                            name='<?php echo $dataId ?>[]'
                                            class="form-control"
                                            id='<?php echo $dataId ?>'
                                            <?php if ($this->disableAll) echo "disabled" ?>> <?php
                                        /**
                                         * Extra data are checked individually and put in the dropdown or checklist
                                         * Multiple items can be selected for checklist, so we check if the user
                                         * has those extra data
                                         */
                                        foreach ($data->getExtraData() as $extraDatum) { ?>
                                            <!-- value of the option -->
                                            <option value='<?php echo $extraDatum ?>'
                                                <?php if ($data->containsExtraData($extraDatum, $this->editingUser))
                                                    echo "selected" ?>>
                                                <!-- check if the extra data has been selected by the user -->
                                                <?php echo $extraDatum ?>
                                                <!-- the option's text -->
                                            </option> <?php
                                        } ?>
                                    </select> <?php
                                }
                                if (!empty($data->getDescription())) { ?>
                                    <p class="description"><?php _e($data->getDescription()) ?></p>
                                <?php } ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <?php
                } ?>
            </table><?php
            if (!$this->disableAll) { ?>
                <button class="btn btn-outline-primary menu-submit" type="submit"
                        name="profile-page" disabled>
                    Modifier les informations
                </button>
            <?php } ?>
        </form> <?php
    }

    private function saveEditedUser() : void{ ?>
        <input name='editingUserId' type='hidden'
               value='<?php echo $this->editingUser->ID ?>'> <?php
    }

    private function saveConfirmModification(){ ?>
        <input name='confirm-button' type='hidden'
               value='<?php echo $this->disableAll ?>'> <?php
    }

    private function showModificationBtn() : void { ?>
        <form method='post' name='confirm-modify-user' id='confirm-modify-user'
              class='validate' novalidate='novalidate'>
            <?php $this->saveEditedUser(); ?>
            <table class='form-table' role='presentation'>
                <tr>
                    <td>
                        <button class="btn btn-primary menu-submit w-100" type="submit"
                                name="confirm-modify" value="<?php echo !$this->disableAll ?>">
                            <?php echo $this->disableAll ? "Déverrouiller" : "Verrouiller" ?> les modifications de l'utilisateur
                        </button>
                    </td>
                </tr>
            </table>
        </form>
    <?php }


    private function chooseUserForm() : void { ?>
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
                        <select name="editingUserId" class="confirm-item form-control"><?php
                            foreach ($this->visibleUsers as $user) { ?>
                                <option value='<?php echo $user->ID ?>'
                                    <?php if ($user->ID === $this->editingUser->ID)
                                        echo "selected" ?>>
                                    <?php echo $user->user_login ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
            </table>
        </form>
    <?php }
}
