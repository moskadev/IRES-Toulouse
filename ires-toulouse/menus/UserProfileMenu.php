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
    private WP_User $editingUser;

    /** @var bool */
    private bool $canEditUser;

    /** @var bool */
    private bool $locked;

    /**
     * Constructing the menu and link to the admin page
     */
    public function __construct() {
        parent::__construct("Consulter les informations relatifs de ",
            "Mon profil IRES",
            0,
            "dashicons-id-alt",
            3
        );
    }

    public function analyzeSentData() : void {
        $type_message = $message = "";
        $this->visibleUsers = Group::getVisibleUsers(wp_get_current_user());

        try {
            if (count($this->visibleUsers) > 0 && isset($_GET["user_id"])) {
                if (($this->editingUser = get_userdata($_GET["user_id"])) === false) {
                    $this->editingUser = get_userdata(get_current_user_id());
                }
            } else {
                $this->editingUser = wp_get_current_user();
            }
        } catch(\Exception $e){
            $this->editingUser = wp_get_current_user();
            $message = "L'utilisateur recherché n'a pas été trouvé";
            $type_message = "error";
        }
        $this->canEditUser = in_array($this->editingUser, $this->visibleUsers);
        $this->locked = $this->canEditUser ? intval($_GET["lock"] ?? true) : true;
        if (isset($_POST["action"]) && $_POST["action"] === "modify") {
            if($this->canEditUser){
                try {
                    UserInputData::checkSentData();
                    foreach ($_POST as $dataId => $dataValue){
                        if(($data = UserData::fromId($dataId)) !== null){
                            $data->updateValue($dataValue, $this->editingUser);
                        }
                    }
                    $message = "Modification des informations de l'utilisateur " .
                        $this->editingUser->user_login . " ont été bien effectuées";
                    $type_message = "updated";
                } catch (Exception $e) {
                    $message = "Erreur : " . $e->getMessage();
                    $type_message = "error";
                }
            } else {
                $message = "Vous n'avez pas l'autorisation de modifier l'utilisateur" . $this->editingUser->user_login;
                $type_message = "error";
            }
        }

        if (!empty($message) && !empty($type_message)) { ?>
            <div id="message" class="<?php echo $type_message ?> notice is-dismissible">
                <p><strong><?php echo $message ?></strong></p>
            </div><?php
        }
    }

    /**
     * Content of the page
     */
    public function getContent() : void {
        $this->chooseUserForm();
        if($this->canEditUser) {
            $this->showModificationBtn();
        } ?>

        <form method='post' name='modify' class='verifiy-form'>
            <input name='action' type='hidden' value='modify'>
            <table class='form-table' role='presentation'><?php
                foreach (UserData::all() as $data) {
                    $formType = $data->getFormType();
                    $dataId = $data->getId();
                    $isLabel = $formType === "label"; ?>
                    <tr class="form-field form-required">
                        <th class='<?php if ($isLabel) echo "title-label" ?>'>
                            <!-- Creating the title of input -->
                            <?php if ($isLabel) {
                                echo $data->getName();
                            } else { ?>
                                <label for='<?php echo $dataId ?>'> <?php
                                    echo $data->getName();
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
                                            type='<?php echo htmlspecialchars($formType) ?>'
                                            id='<?php echo $dataId ?>'
                                            name='<?php echo $dataId ?>'
                                            value='<?php echo htmlspecialchars($data->getValue($this->editingUser)); ?>'
                                        <?php if ($this->locked) echo "disabled" ?>>
                                    <?php
                                } else if($formType === "table" && $dataId === "groupes"){
                                    $groups = $data->getExtraData($this->editingUser);
                                    if(count($groups) > 0){ ?>
                                        <table class="widefat groups-data striped">
                                            <thead>
                                            <tr>
                                                <th class="row-title">Nom du groupe</th>
                                                <th class="row-title">Type du groupe</th>
                                                <th class="row-title">Responsables</th>
                                                <th class="row-title">Responsable de ce groupe</th>
                                            </tr>
                                            </thead>
                                            <tbody> <?php
                                            /** @var Group $group */
                                            foreach ($groups as $group){
                                                $respNames = array_map(function($u) {
                                                    return "<a href='" . home_url("/wp-admin/admin.php?page=" . $this->getId() . "&user_id=" . $u->ID . "&lock=1") . "'>" . $u->first_name . " " . $u->last_name . "</a>";
                                                }, $group->getResponsables()); ?>
                                                <tr>
                                                    <td><a class="text-decoration-none"
                                                           href="<?php echo home_url("/wp-admin/admin.php?page=details_du_groupe&group=" . $group->getId()) ?>">
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
                                            type="radio"
                                            id='<?php echo $dataId ?>_oui'
                                            name='<?php echo $dataId ?>'
                                            value="true"
                                        <?php if ($this->locked) echo "disabled" ?>
                                        <?php if ($value) echo "checked" ?>>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    Non <input <?php echo Dataset::allFrom($data) ?>
                                            type="radio"
                                            id='<?php echo $dataId ?>_non'
                                            name='<?php echo $dataId ?>'
                                            value="false"
                                        <?php if ($this->locked) echo "disabled" ?>
                                        <?php if (!$value) echo "checked" ?>>
                                    <?php
                                } else if (in_array($formType, ["dropdown", "checklist"])) { ?>
                                    <select <?php if ($formType === "checklist") echo "multiple" ?>
                                            name='<?php echo $dataId ?>[]'
                                            id='<?php echo $dataId ?>'
                                        <?php if ($this->locked) echo "disabled" ?>> <?php
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
                                    <p class="description"><?php echo $data->getDescription() ?></p>
                                <?php } ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <?php
                } ?>
            </table><?php
            if (!$this->locked) { ?>
                <button class="button-primary menu-submit button-large" type="submit"
                        name="profile-page" disabled>
                    <span class="dashicons dashicons-id"></span>
                    Modifier les informations
                </button>
            <?php } ?>
        </form> <?php
    }

    private function showModificationBtn() : void { ?>
        <table class="data-table">
            <tr>
                <td>
                    <button class="button-primary button-large"
                            onclick='location.href="<?php echo home_url("/wp-admin/admin.php?page=" . $this->getId() . "&user_id=" . $this->editingUser->ID . "&lock=" . !$this->locked) ?>"'>
                        <span class='dashicons <?php echo $this->locked ? "dashicons-lock'></span>Déverrouiller" : "dashicons-unlock'></span>Verrouiller" ?> les modifications de l'utilisateur
                    </button>
                </td>
            </tr>
        </table>
    <?php }

    private function chooseUserForm() : void { ?>
        <div class="custom-dropdown">
            <button class="dropdown-btn">
                <span><?php echo $this->editingUser->user_login ?></span><?php
                if(count($this->visibleUsers) > 1){?>
                    <span class="dashicons dashicons-arrow-down"></span>
                <?php } ?>
            </button>
            <div class="dropdown-content"><?php
                foreach ($this->visibleUsers as $user) { ?>
                    <a class="<?php if($user->ID === $this->editingUser->ID) echo "dropdown-selected" ?>"
                       href="<?php echo home_url("/wp-admin/admin.php?page=" . $this->getId() . "&user_id=" . $user->ID) ?>">
                        <?php echo $user->user_login ?>
                    </a>
                <?php } ?>
            </div>
        </div>
    <?php }
}
