<?php

namespace irestoulouse\menus\users;

use Exception;
use irestoulouse\controllers\InputDataController;
use irestoulouse\data\UserCustomDataFactory;
use irestoulouse\exceptions\InvalidDataValueException;
use irestoulouse\group\Group;
use irestoulouse\group\GroupFactory;
use irestoulouse\group\GroupType;
use irestoulouse\menus\Menu;
use irestoulouse\menus\MenuFactory;
use irestoulouse\menus\MenuIds;
use irestoulouse\utils\Dataset;
use irestoulouse\utils\Identifier;
use irestoulouse\utils\Locker;
use WP_User;

/**
 * This page will allow you to modify the personal information of an user
 * The user can modify all the following information:
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
 *
 * @version 2.0
 */
class UserProfileMenu extends Menu {

    /** @var WP_User[] */
    private array $visibleUsers;

    /** @var WP_User */
    private WP_User $editingUser;

    /** @var bool */
    private bool $canEditUser;

    /** @var int */
    private int $lockedState;

    /**
     * Constructing the menu
     */
    public function __construct() {
        parent::__construct(MenuIds::USER_PROFILE_MENU, "Mon profil IRES",
            "Consulter les informations relatifs de ", 0, "dashicons-id-alt", 3
        );
    }

    /**
     * @param string|int $userId the user's identifier
     * @param int $lockState the lock state of this page
     *
     * @return string the full url with params
     */
    public function getPageUrl($userId = "", int $lockState = Locker::STATE_LOCKED) : string {
        $params = ["user_id" => $userId, "lock" => $lockState];
        if (strlen($userId) === 0) {
            unset($params["user_id"]);
        }
        return parent::createPageUrl($params);
    }

    /**
     * @param array $params $_GET and $_POST combined
     */
    public function analyzeParams(array $params) : void {
        $this->visibleUsers = GroupFactory::getVisibleUsers(wp_get_current_user());
        try {
            if (count($this->visibleUsers) > 0 && isset($params["user_id"])) {
                $id = is_numeric($params["user_id"]) ?
                    get_userdata($params["user_id"]) :
                    get_user_by("login", Identifier::extractLogin($params["user_id"]));
                if ($id === false) {
                    $this->editingUser = get_userdata(get_current_user_id());
                    $this->showNoticeMessage("error", "L'utilisateur recherché n'a pas été trouvé");
                } else {
                    $this->editingUser = $id;
                }
            } else {
                $this->editingUser = wp_get_current_user();
            }
        } catch (Exception $e) {
            $this->editingUser = wp_get_current_user();
            $this->showNoticeMessage("error", "L'utilisateur recherché n'a pas été trouvé");
        }
        $this->canEditUser = in_array($this->editingUser, $this->visibleUsers);
        $this->lockedState = $this->canEditUser ?
            intval($params["lock"] ?? Locker::STATE_LOCKED) :
            Locker::STATE_LOCKED;
        if (isset($params["action"]) && $params["action"] === "modify") {
            if ($this->canEditUser) {
                try {
                    InputDataController::checkSentData();
                    foreach ($params as $dataId => $dataValue) {
                        if (($data = UserCustomDataFactory::fromId($dataId)) !== null) {
                            $data->updateValue($dataValue, $this->editingUser);
                        }
                    }
                    $this->showNoticeMessage("updated", "Modification des 
                        informations de l'utilisateur " . $this->editingUser->user_login .
                        " ont été bien effectuées"
                    );
                } catch (InvalidDataValueException $e) {
                    $this->showNoticeMessage("error", "Erreur : " . $e->getMessage());
                }
            } else {
                $this->showNoticeMessage("error", "Vous n'avez pas 
                    l'autorisation de modifier l'utilisateur" .
                    $this->editingUser->user_login
                );
            }
        }
    }

    /**
     * Content of the page
     */
    public function showContent() : void {
        $this->chooseUserForm();
        if ($this->canEditUser && $this->lockedState !== Locker::STATE_UNLOCKABLE) {
            $this->displayLockerButton();
        } ?>

        <form method='post' name='modify' class='verifiy-form'>
        <input name='action' type='hidden' value='modify'>
        <table class='form-table' role='presentation'><?php
            foreach (UserCustomDataFactory::all(true) as $data) {
                $formType = $data->getFormType();
                $dataId = $data->getId();
                $isLabel = $formType === "label"; ?>
                <tr class="form-field form-required">
                    <th class='<?php if ($isLabel)
                        echo "title-label" ?>'>
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
                    </th> <?php
                    if (!$isLabel) { ?>
                        <td> <?php
                            if ($formType === "text") { ?>
                                <input <?php echo Dataset::allFrom($data) ?>
                                        type='<?php echo htmlspecialchars($formType) ?>'
                                        id='<?php echo $dataId ?>'
                                        name='<?php echo $dataId ?>'
                                        value='<?php echo htmlspecialchars($data->getValue($this->editingUser)); ?>'
                                    <?php if ($this->lockedState >= Locker::STATE_LOCKED)
                                        echo "disabled" ?>>
                                <?php
                            } else if ($formType === "table" && $dataId === "groupes") {
                                $groups = $data->getExtraData($this->editingUser);
                                if (count($groups) > 0) { ?>
                                    <table class="widefat groups-data striped">
                                        <thead>
                                        <tr>
                                            <th class="row-title">Nom du groupe</th>
                                            <th class="row-title">Type du groupe</th>
                                            <th class="row-title">Responsables</th>
                                            <th class="row-title">Responsable de ce
                                                groupe
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody> <?php
                                        /** @var Group $group */
                                        foreach ($groups as $group) {
                                            $respNames = array_map(function ($u) {
                                                return "<a href='" . $this->getPageUrl($u->ID) . "'>" . $u->first_name . " " . $u->last_name . "</a>";
                                            }, $group->getResponsables()); ?>
                                            <tr>
                                                <td><a class="text-decoration-none"
                                                       href="<?php echo MenuFactory::fromId(MenuIds::GROUP_DETAILS_MENU)->getPageUrl($group->getId()) ?>">
                                                        <?php echo $group->getName() ?></a>
                                                </td>
                                                <td><?php echo GroupType::NAMES[$group->getType()] ?></td>
                                                <td><?php echo implode(", ", $respNames) ?></td>
                                                <td><?php echo $group->isUserResponsable($this->editingUser) ?
                                                        "Oui" : "Non" ?></td>
                                            </tr>
                                        <?php }
                                        ?> </tbody>
                                    </table> <?php
                                } else { ?>
                                    <p>L'utilisateur n'appartient à aucun groupe</p>
                                <?php }
                            } else if ($formType === "radio") { ?>
                                <label class="switch">
                                    <input class="switch-radio"
                                        <?php echo Dataset::allFrom($data) ?>
                                           type="radio"
                                           name="<?php echo $dataId ?>"
                                           value="<?php echo $data->getValue($this->editingUser) ?>"
                                        <?php if ($this->lockedState >= Locker::STATE_LOCKED)
                                            echo "disabled" ?>>
                                    <span class="slider round"></span>
                                </label> <?php
                            } else if (in_array($formType, ["dropdown", "checklist"])) { ?>
                                <select <?php echo Dataset::allFrom($data);
                                if ($formType === "checklist")
                                    echo "multiple" ?>
                                        name='<?php echo $dataId ?>[]'
                                        id='<?php echo $dataId ?>'
                                    <?php if ($this->lockedState >= Locker::STATE_LOCKED)
                                        echo "disabled" ?>> <?php
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
                            if (strlen($data->getDescription()) > 0) { ?>
                                <p class="description"><?php echo $data->getDescription() ?></p> <?php
                            } ?>
                        </td> <?php
                    } ?>
                </tr>
                <?php
            } ?>
        </table> <?php
        $this->submitAndExportButtons();
        ?> </form> <?php
    }

    /**
     * Display the submit button new data and export data button of the page
     */
    private function submitAndExportButtons() : void { ?>
        <div class="input-register-container input-register-2"> <?php
            if ($this->lockedState === Locker::STATE_UNLOCKED) { ?>
                <button class="button-primary menu-submit button-large" type="submit"
                        name="profile-page" disabled>
                    <span class="dashicons dashicons-id"></span>
                    Modifier les informations
                </button> <?php
            }
            if (current_user_can("direction") || current_user_can("administrator")) { ?>
                <select class="export-dropdown button-secondary button-large">
                    <option selected disabled>Exporter ces données</option>
                    <option data-type="excel"
                            data-user-ids="<?php echo $this->editingUser->ID ?>">Exporter
                        en Excel
                    </option>
                    <option data-type="csv"
                            data-user-ids="<?php echo $this->editingUser->ID ?>">Exporter
                        en CSV
                    </option>
                </select> <?php
            } ?>
        </div> <?php
    }

    /**
     * Display an input with auto-completation feature where any
     * user can be looked for and the page is unlocked
     */
    private function chooseUserForm() : void { ?>
        <form style="display: inline-block"
              action="<?php echo $this->getPageUrl(get_current_user_id()) ?>"
              method="get">
            <input type="text" class="search-field user-field-login"
                   value="<?php echo Identifier::generateFullName($this->editingUser) ?>"
                <?php if ($this->lockedState >= Locker::STATE_LOCKED || count($this->visibleUsers) <= 1)
                    echo "disabled" ?>>
        </form>
    <?php }

    /**
     * Show the locker button depending on 3 states :
     * - Unlocked, the user can modify the data
     * - Locked, the user can't modify them
     * - Unlockable, the button isn't displayed and the user can't modify anything
     */
    private function displayLockerButton() : void { ?>
        <table class="data-table">
            <tr>
                <td>
                    <button class="<?php echo $this->lockedState ? "button-primary" : "button-secondary" ?> button-large"
                            onclick='location.href="<?php echo $this->getPageUrl($this->editingUser->ID, !$this->lockedState) ?>"'>
                        <span class='dashicons <?php echo $this->lockedState ? "dashicons-lock'></span>Déverrouiller" : "dashicons-unlock'></span>Verrouiller" ?> les modifications de l'
                              utilisateur
                    </button>
                </td>
            </tr>
        </table>
    <?php }
}
