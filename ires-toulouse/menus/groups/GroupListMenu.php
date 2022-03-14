<?php

namespace irestoulouse\menus\groups;

use Exception;
use irestoulouse\group\Group;
use irestoulouse\group\GroupFactory;
use irestoulouse\group\GroupType;
use irestoulouse\menus\Menu;
use irestoulouse\menus\MenuFactory;
use irestoulouse\menus\MenuIds;
use irestoulouse\utils\Locker;


/**
 * List all groups that exists. The user can see
 * a list of its groups too.
 *
 * @version 2.0
 */
class GroupListMenu extends Menu {

    /** @var bool */
    private bool $creatingGroup;

    /**
     * Initializing everything related to this menu
     */
    public function __construct() {
        parent::__construct(MenuIds::GROUP_LIST_MENU, "Groupes IRES",
            "Liste des groupes de l'IRES de Toulouse", 0, "dashicons-businesswoman", 3
        );
    }

    /**
     * Checking if a group has been created or removed and also
     * the menu can display different messages (error, notice, etc)
     *
     * @param array $params $_GET and $_POST combined
     */
    public function analyzeParams(array $params) : void {
        $message = $type_message = "";
        $this->creatingGroup = isset($params["submitGroup"]);
        /*
         * Delete a group if it exists
         */
        if (strlen($delete = trim($params['delete'] ?? "")) > 0 &&
            ($deletedGroup = GroupFactory::fromId($delete)) !== null) {
            $message = "Le groupe " . $deletedGroup->getName() . " n'a pas pu être supprimé.";
            $type_message = "error";
            if (GroupFactory::delete($deletedGroup->getId())) {
                $message = "Le groupe " . $deletedGroup->getName() . " a été supprimé.";
                $type_message = "updated";
            }
        }

        /*
         * Add a new group if possible
         */
        if (GroupFactory::isValid($nom = trim($params['addGroup'] ?? ""), $type = $params['typeAddGroup'] ?? - 1)) {
            $message = "Impossible de créer le groupe " . esc_attr($nom);
            $type_message = "error";
            try {
                if (GroupFactory::register(esc_attr($nom), intval(esc_attr($type)))) {
                    $create = GroupFactory::fromName(esc_attr($nom));

                    $type_message = "updated";
                    $message = "Le groupe de " . $create->getName() .
                        ", dénommé <a href=" . MenuFactory::fromId(MenuIds::GROUP_DETAILS_MENU)->getPageUrl($create->getId()) .
                        ">" . $create->getName() . "</a>, a été créé.";
                }
            } catch (Exception $e) {
                // do nothing, the error message has been already set
            }
        }
        $this->showNoticeMessage($type_message, $message);
    }

    /**
     * Contents of the "Create a group" menu
     * Allows to :
     *      - create a group of user if you are admin
     */
    public function showContent() : void { ?>
        <!-- Confirmation popup for deletion of a group -->
        <div class="popup">
            <div class="popup-element">
                <div class="popup-header">
                    <p class="title popup-title"></p>
                    <button data-close-button class="close-button">&times;</button>
                </div>
                <div class="popup-body">
                    <p>Êtes-vous sûr de vouloir supprimer ce groupe ?</p>
                    <form action="" method="post">
                        <input type="hidden" id="groupId" name="delete" value="">
                        <button class="confirm-delete button-primary button-delete"
                                type="submit">Confirmer
                        </button>
                        <button class="button-secondary" type="button" data-close-button>
                            Annuler
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <h3>Légende : </h3>
        <p>
            <mark class="underline-blue">Le surlignage bleu signifie que vous êtes membre
                du groupe
            </mark>
            <br>
            <mark class="underline-orange">Le surlignage orange signifie que vous êtes
                membre et responsable du groupe
            </mark>
        </p>

        <?php
        /**
         * Form to add a group
         * - Name of the group
         * - Add button
         */
        $this->createGroupForm();

        /**
         * Display all the groups, users' groups and all groups
         */
        if (count(GroupFactory::getUserGroups(wp_get_current_user())) > 0) { ?>
            <h2 class="title-label">Vos groupes : </h2> <?php
            self::printGroups(GroupFactory::getUserGroups(wp_get_current_user()));
        } ?>

        <h2 class="title-label">Groupes : </h2> <?php
        self::printGroups(GroupFactory::all());
    }

    /**
     * Create a new form for adding new group. Once the user clicked
     * on "Adding a new group", the user can enter the group's name
     * and type. The user can choose to add it or to cancel its action
     */
    private function createGroupForm() : void {
        if (current_user_can('administrator')) { ?>
            <form action="" method="post"> <?php
                if ($this->creatingGroup) {
                    ?>
                    <div class="input-register-container input-register-4">
                        <input type="text" name="addGroup" placeholder="Nom du groupe">
                        <select name="typeAddGroup"> <?php
                            foreach (GroupType::NAMES as $type => $name) {
                                ?>
                                <option value="<?php echo $type ?>"><?php echo $name ?></option>
                            <?php } ?>
                        </select>
                        <button class="button-primary" type="submit">Ajouter</button>
                        <button class="button-secondary" type="button"
                                onclick="reloadPage()">Annuler
                        </button>
                    </div>
                <?php } else { ?>
                    <button type="submit" name="submitGroup"
                            class="button-primary menu-submit button-large">
                        <span class="dashicons dashicons-groups"></span>
                        Ajouter un nouveau groupe
                    </button>
                <?php }
                ?>
            </form> <?php
        }
    }

    /**
     * Print the row of a table for the group given in parameter
     *
     * @param $groups Group[] groups to print
     */
    private function printGroups(array $groups) : void{
        if (count($groups) > 0) {
            $detailsGroup = MenuFactory::fromId(MenuIds::GROUP_DETAILS_MENU);
            $currentUser = wp_get_current_user();
            ?>
            <table class="widefat data-table striped">
                <thead>
                <tr>
                    <th class="row-title">Nom</th>
                    <th class="row-title">Type</th>
                    <th class="row-title" style="width: 300px;">Responsable(s)</th>
                    <th class="row-title">Date de création</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($groups as $group) {
                    $respNames = array_map(function ($u) {
                        return "<a href='" . MenuFactory::fromId(MenuIds::USER_PROFILE_MENU)->getPageUrl($u->ID, Locker::STATE_LOCKED) .
                            "'>" . $u->first_name . " " . $u->last_name . "</a>";
                    }, $group->getResponsables()); ?>
                <tr class="<?php
                if ($group->isUserResponsable($currentUser)) {
                    echo "is-resp ";
                } else if ($group->userExists($currentUser))
                    echo "row-hover" ?>">
                    <!-- Name of the group -->
                    <th>
                        <a class="text-decoration-none"
                           href="<?php echo $detailsGroup->getPageUrl($group->getId()) ?>">
                            <?php echo $group->getName() ?>
                        </a>
                    </th>
                    <!-- Group's type -->
                    <td> <?php echo GroupType::NAMES[$group->getType()] ?></td>
                    <!-- Name of the users in charge of the group -->
                    <td> <?php echo implode(", ", $respNames) ?></td>
                    <!-- Date -->
                    <td><?php echo $group->getCreationTime() ?></td>
                    <td class="hide-actions">
                        <?php
                        if (current_user_can('administrator') || $group->isUserResponsable($currentUser)) { ?>
                            <form method="post">
                            <button type="button" id="modify" name="modify"
                                    value="<?php echo $group->getId() ?>"
                                    class="button-secondary"
                                    onclick="location.href='<?php echo $detailsGroup->getPageUrl($group->getId()) ?>'">
                                Modifier
                            </button> <?php
                            if (current_user_can('administrator')) { ?>
                            <button type="button" id="delete" name=""
                                    value="<?php echo $group->getId() ?>"
                                    class="button-secondary button-secondary-delete"
                                    onclick="setGroupInfo('<?php echo $group->getName() ?>', '<?php echo $group->getId() ?>')"
                                    data-popup-target>
                                    Supprimer
                                </button><?php
                            } ?>
                            </form><?php
                        }
                        ?>
                    </td>
                    </tr><?php
                } ?>
                </tbody>
                <?php

                /*
                 * Affichage du bas de page si il y a plus de 9 groupes
                 */
                if (count($groups) > 9) { ?>
                    <tfoot>
                    <tr>
                        <th class="row-title">Nom</th>
                        <th class="row-title">Type</th>
                        <th class="row-title">Responsable(s)</th>
                        <th class="row-title">Date de création</th>
                        <th></th>
                    </tr>
                    </tfoot>
                <?php } ?>
            </table> <?php
        } else { ?>
            <p>Aucun groupe n'existe</p>
        <?php }
    }
}