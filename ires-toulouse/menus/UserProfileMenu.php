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

    /**
     * Constructing the menu and link to the admin page
     */
    public function __construct() {
        parent::__construct("Consulter les informations relatifs à votre profil IRES",
            "Mon profil IRES",
            0,
            "dashicons-id-alt",
            3
        );
        $this->visibleUsers = Group::getVisibleUsers(wp_get_current_user());
        $this->lastRegisteredUser = Identifier::getLastRegisteredUser($this->visibleUsers)
            ?? wp_get_current_user();
    }

    /**
     * Content of the page
     */
    public function getContent() : void
    {
    }
}