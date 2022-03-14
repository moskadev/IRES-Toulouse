<?php

namespace irestoulouse\menus\users;

use Exception;
use irestoulouse\group\GroupFactory;
use irestoulouse\menus\Menu;
use irestoulouse\menus\MenuFactory;
use irestoulouse\menus\MenuIds;
use irestoulouse\utils\Identifier;
use irestoulouse\utils\Locker;
use WP_User;

/**
 * Menu where all users are listed, multiple options are possible
 * like exporting all data, search bar, delete, profile's check
 *
 * @version 2.0
 */
class UserListMenu extends Menu {

    /** @var string */
    private string $orderBy;
    /** @var string */
    private string $order;
    /** @var string */
    private string $search;
    /** @var WP_User[] */
    private array $searchedMembers;

    /**
     * Initializing the menu
     */
    public function __construct() {
        parent::__construct(MenuIds::USER_LIST_MENU, "Comptes IRES",
            "Liste des profils de l'IRES de Toulouse", 0, "dashicons-id-alt", 3
        );
    }

    /**
     * @param string $orderBy the order on a user's metadata
     * @param string $orderType the type of order (asc or desc)
     * @param string $search the search of a user
     *
     * @return string the full page's url
     */
    public function getPageUrl(string $orderBy = "", string $orderType = "", string $search = "") : string {
        $params = ["orderby" => $orderBy, "order" => $orderType, "search" => $search];
        if (strlen($orderBy) === 0) {
            unset($params["orderby"]);
        }
        if (strlen($orderType) === 0) {
            unset($params["order"]);
        }
        if (strlen($search) === 0) {
            unset($params["search"]);
        }
        return parent::createPageUrl($params);
    }

    /**
     * Checking if a user should be deleted and sorting the users
     *
     * @param array $params $_GET and $_POST combined
     */
    public function analyzeParams(array $params) : void {
        /**
         * Deleting the user after validation (with the confirmation popup)
         */
        $message = $type_message = "";
        if (current_user_can('administrator') && isset($params['delete'])) {
            try {
                $deletedUser = get_userdata($params['delete']);
                if ($deletedUser !== false) {
                    $fullName = Identifier::generateFullName($deletedUser);

                    $message = "Erreur : L'utilisateur $fullName n'a pas pu être supprimé";
                    $type_message = "error";
                    if (wp_delete_user($deletedUser->ID)) {
                        $message = "L'utilisateur $fullName a bien été supprimé";
                        $type_message = "updated";
                    }
                }
            } catch (Exception $e) {
                $message = "Erreur : L'utilisateur sélectionné n'a pas pu être supprimé";
                $type_message = "error";
            }
        }
        /**
         * Sorting the users
         */
        $this->order = $params["order"] ?? "asc";
        $this->search = $params["search"] ?? "";
        $this->orderBy = $params["orderby"] ?? "";
        $this->searchedMembers = get_users([
            "search" => "*$this->search*",
            "orderby" => wp_get_current_user()->{$this->orderBy} !== null ? $this->orderBy : "",
            "order" => $this->order,
            "search_columns" => ["user_login", "first_name", "last_name", "user_email"]
        ]);
        $this->showNoticeMessage($type_message, $message);
    }

    /**
     * Display of the content of this page
     */
    public function showContent() : void { ?>
        <!-- Confirmation popup for deletion of a user -->
        <div class="popup">
            <div class="popup-element">
                <div class="popup-header">
                    <p class="title popup-title"></p>
                    <button data-close-button class="close-button">&times;</button>
                </div>
                <div class="popup-body">
                    <p>Êtes-vous sûr de vouloir supprimer ce compte ?</p>
                    <form action="" method="post">
                        <input type="hidden" id="userId" name="delete" value="">
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

        <div class="action-list-bar">
            <div><?php
                if (current_user_can('responsable') || current_user_can('administrator')) { ?>
                    <button class="button-secondary"
                            onclick="location.href='<?php echo MenuFactory::fromId(MenuIds::USER_REGISTER_MENU)->getPageUrl() ?>'">
                        Ajouter un membre
                    </button> <?php
                }
                if (current_user_can('direction') || current_user_can('administrator')) { ?>
                    <form action="" method="post" style="display: inline-block">
                        <select class="export-dropdown button-secondary">
                            <option selected disabled>Tout exporter</option>
                            <option data-type="excel" data-user-ids="">Exporter en Excel
                            </option>
                            <option data-type="csv" data-user-ids="">Exporter en CSV
                            </option>
                        </select>
                        <select disabled
                                class="export-dropdown export-selection button-secondary">
                            <option selected disabled>Exporter la sélection</option>
                            <option data-type="excel" data-user-ids="export-selection">
                                Exporter en Excel
                            </option>
                            <option data-type="csv" data-user-ids="export-selection">
                                Exporter en CSV
                            </option>
                        </select>
                    </form><?php
                } ?>
            </div>
            <form action="" method="get">
                <input type="hidden" name="page" value="<?php echo $this->getId() ?>"/>
                <input type="text" placeholder="Recherche" name="search"
                       value="<?php echo $this->search ?>">
                <button class="button-secondary" type="submit">Rechercher des comptes
                </button>
                <button class="button-secondary button-secondary-delete"
                        type="submit"
                        onclick="document.querySelector('input[name=search]').value = ''">
                    Effacer
                </button>
            </form>
        </div>
        <table class="widefat striped users-list">
            <thead>
            <tr>
                <?php
                if (current_user_can('responsable') || current_user_can('administrator') || current_user_can('direction')) { ?>
                    <td class="manage-column column-cb check-column">
                        <input type="checkbox" class="checkbox-export-all">
                    </td>
                    <?php
                } ?>
                <th class="manage-column column-username column-primary sortable <?php echo $this->order ?>">
                    <a href="<?php echo $this->getPageUrl("last_name", $this->reverseOrder(), $this->search) ?>">
                        <!-- order = asc ou desc-->
                        <span>Nom</span>
                        <span id="sorting-indicator-last_name"
                              class="sorting-indicator"></span>
                    </a>
                </th>
                <th class="manage-column column-username column-primary sortable <?php echo $this->order ?>">
                    <a href="<?php echo $this->getPageUrl("first_name", $this->reverseOrder(), $this->search) ?>">
                        <!-- order = asc ou desc-->
                        <span>Prénom</span>
                        <span id="sorting-indicator-first_name"
                              class="sorting-indicator"></span>
                    </a>
                </th>
                <th>Email</th>
                <th>Identifiant</th>
                <th>Groupes</th>
            </tr>
            </thead>
            <tbody> <?php
            foreach ($this->searchedMembers as $user) {
                $menuProfile = MenuFactory::fromId(MenuIds::USER_PROFILE_MENU);
                $groupNames = array_map(function ($g) {
                    return "<a href='" . MenuFactory::fromId(MenuIds::GROUP_DETAILS_MENU)->getPageUrl($g->getId()) .
                        "'>" . $g->getName() . "</a>";
                }, GroupFactory::getUserGroups($user)); ?>
                <tr> <?php
                    if (current_user_can('responsable') || current_user_can('administrator') || current_user_can('direction')) { ?>
                        <th scope="row" class="check-column">
                            <input type="checkbox" class="checkbox-export"
                                   value="<?php echo $user->ID; ?>">
                        </th> <?php
                    } ?>
                    <td class="name"><?php echo $user->last_name; ?><br/>
                        <div class="hide-actions"
                             style="display: grid; grid-template-columns: repeat(2, max-content); grid-column-gap: 10px">
                            <form method="post" action=""><?php
                                if (in_array($user, GroupFactory::getVisibleUsers(wp_get_current_user()))) { ?>
                                    <button type="submit" class="button-link-ires">
                                        <a href="<?php echo $menuProfile->getPageUrl($user->ID, Locker::STATE_UNLOCKED) ?>">Modifier</a>
                                    </button> <?php
                                }
                                if (current_user_can('administrator') && (!user_can($user, "administrator") || $user->ID !== get_current_user_id())) { ?>
                                    <button type="button" data-popup-target
                                            class="delete-link"
                                            onclick="setUserInfo(<?php echo "'" . $user->ID . '\',\'' . $user->first_name . '\',\'' . $user->last_name . '\''; ?>)">
                                        Supprimer
                                    </button> <?php
                                } ?>
                                <button type="submit" class="button-link-ires">
                                    <a href="<?php echo $menuProfile->getPageUrl($user->ID, Locker::STATE_UNLOCKABLE) ?>">Voir</a>
                                </button>
                            </form>
                        </div>
                    </td> <!-- Last name -->
                    <td><?php echo $user->first_name; ?></td> <!-- First name -->
                    <td>
                        <a href="mailto:<?php echo $user->user_email; ?>"><?php echo $user->user_email; ?></a>
                    </td> <!-- Email -->
                    <td><?php echo $user->user_login; ?></td> <!-- User login -->
                    <td style="max-width: 300px">
                        <?php echo count($groupNames) > 0 ? implode(", ", $groupNames) : "Aucun" ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
            <tfoot> <?php
            if (count($this->searchedMembers) > 9) { ?>
                <tr>
                    <th class="row-title">Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Identifiant</th>
                    <th>Groupes</th>
                </tr><?php
            } ?>
            </tfoot>
        </table>
        <?php
    }

    /**
     * Reverse the order : if asc, desc or else asc
     *
     * @return string the reversed current order
     */
    private function reverseOrder() : string {
        return $this->order === "asc" ? "desc" : "asc";
    }
}