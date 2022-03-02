<?php

namespace menus;

use irestoulouse\controllers\UserConnection;
use irestoulouse\elements\Group;
use irestoulouse\menus\IresMenu;
use irestoulouse\menus\UserProfileMenu;
use irestoulouse\utils\ExcelGenerator;
use irestoulouse\utils\Identifier;
use irestoulouse\utils\Locker;

class UserListMenu extends IresMenu {

    /** @var \WP_User[] */
    private array $searchedMembers;

    public function __construct() {
        parent::__construct(
            "Liste des profils de l'IRES de Toulouse",
            "Comptes IRES",
            0,
            "dashicons-id-alt",
            3
        );
    }

    public function analyzeSentData() : void {
        /*
         * Deleting the user after validation (with the confirmation popup)
         */
        $message = $type_message = "";
        if (current_user_can('administrator') && isset($_POST['delete'])) {
            try {
                $deletedUser = get_userdata($_POST['delete']);
                if($deletedUser !== false) {
                    $fullName = "{$deletedUser->last_name} {$deletedUser->first_name} ({$deletedUser->user_login})";

                    $message = "Erreur : L'utilisateur $fullName n'a pas pu être supprimé";
                    $type_message = "error";
                    if (UserConnection::delete($deletedUser)) {
                        $message = "L'utilisateur $fullName a bien été supprimé";
                        $type_message = "updated";
                    }
                }
            } catch (\Exception $e){
                $message = "Erreur : L'utilisateur $fullName n'a pas pu être supprimé";
                $type_message = "error";
            }
        }
        if(!empty($message) && !empty($type_message)) {?>
            <div id="message" class="<?php echo $type_message ?> notice is-dismissible">
                <p><strong><?php echo $message ?></strong></p>
            </div> <?php
        }
        /**
         * Sorting the users
         */
        $search = $_GET["search"] ?? "";
        $orderBy = $_GET["orderby"] ?? "";
        $this->searchedMembers = get_users([
            "search" => "*$search*",
            "orderby" => wp_get_current_user()->$orderBy !== null ? $orderBy : "",
            "order" => $this->getOrder(),
            "search_columns" => ["user_login", "first_name", "last_name", "user_email"]
        ]);
    }

    public function getContent() : void {?>
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
                        <button class="confirm-delete button-primary button-delete" type="submit">Confirmer</button>
                        <button class="button-secondary" type="button" data-close-button>Annuler</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="action-list-bar">
            <div><?php
                if (current_user_can('responsable') || current_user_can('administrator')) { ?>
                    <button class="button-secondary"
                            type="submit"
                            onclick="location.href='<?php echo home_url("/wp-admin/admin.php?page=ajouter_un_compte") ?>'">
                        Ajouter un membre
                    </button> <?php
                }
                if (current_user_can('direction') || current_user_can('administrator')) { ?>
                    <form action="" method="post" style="display: inline-block">
                        <select class="export-dropdown button-secondary">
                            <option selected disabled>Tout exporter</option>
                            <option data-type="excel" data-user-ids="">Exporter en Excel</option>
                            <option data-type="csv" data-user-ids="">Exporter en CSV</option>
                        </select>
                        <select disabled class="export-dropdown export-selection button-secondary">
                            <option selected disabled>Exporter la sélection</option>
                            <option data-type="excel" data-user-ids="export-selection">Exporter en Excel</option>
                            <option data-type="csv" data-user-ids="export-selection">Exporter en CSV</option>
                        </select>
                    </form><?php
                }?>
            </div>
            <form action="" method="get">
                <input type="hidden" name="page" value="<?php echo $this->getId() ?>"/>
                <input type="text" class="search-field" placeholder="Recherche" name="search" value="<?php if(isset($_GET['search'])) echo $_GET['search']; ?>">
                <button class="button-secondary" type="submit">Rechercher des comptes</button>
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
                        <input type="checkbox" onclick="document.querySelector('.export-selection').disabled = !document.querySelector('.export-selection').disabled">
                    </td>
                    <?php
                    } ?>
                    <th class="manage-column column-username column-primary sortable <?php echo $this->getOrder() ?>">
                        <a href="<?php echo home_url("/wp-admin/admin.php?page=comptes_ires&orderby=last_name&order=" . $this->getOrder(true)) ?>"><!-- order = asc ou desc-->
                            <span>Nom</span>
                            <span id="sorting-indicator-last_name" class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="manage-column column-username column-primary sortable <?php echo $this->getOrder() ?>">
                        <a href="<?php echo home_url("/wp-admin/admin.php?page=comptes_ires&orderby=first_name&order=" . $this->getOrder(true)) ?>"><!-- order = asc ou desc-->
                            <span>Prénom</span>
                            <span id="sorting-indicator-first_name" class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th>Email</th>
                    <th>Identifiant</th>
                    <th>Groupe</th>
                </tr>
            </thead>
            <tbody> <?php
            foreach ($this->searchedMembers as $user) {
                $groupNames = array_map(function ($g){
                    return "<a href='" . home_url("/wp-admin/admin.php?page=details_du_groupe&group=" .
                        $g->getId()) . "'>" . $g->getName() . "</a>";
                }, Group::getUserGroups($user)); ?>
                <tr> <?php
                    if (current_user_can('responsable') || current_user_can('administrator') || current_user_can('direction')) { ?>
                    <th scope="row" class="check-column">
                        <input type="checkbox" class="checkbox-export" value="<?php echo $user->ID; ?>">
                    </th> <?php
                    } ?>
                    <td class="name"><?php echo $user->last_name; ?><br/>
                        <div class="hide-actions" style="display: grid; grid-template-columns: repeat(2, max-content); grid-column-gap: 10px">
                            <form method="post" action=""><?php
                                if (in_array($user, Group::getVisibleUsers(wp_get_current_user()))) { ?>
                                    <button type="submit" class="button-link-ires">
                                        <a href="<?php echo home_url("/wp-admin/admin.php?page=mon_profil_ires&user_id=" . $user->ID .
                                            "&lock=" . Locker::STATE_UNLOCKED) ?>">Modifier</a>
                                    </button> <?php
                                }
                                if (current_user_can('administrator') && (!user_can($user, "administrator") || $user->ID !== get_current_user_id())) { ?>
                                    <button type="button" data-popup-target class="delete-link" onclick="setUserInfo(<?php echo "'" . $user->ID  . '\',\'' . $user->first_name . '\',\'' . $user->last_name .'\''; ?>)">Supprimer</button> <?php
                                }?>
                                <button type="submit" class="button-link-ires">
                                    <a href="<?php echo home_url("/wp-admin/admin.php?page=mon_profil_ires&user_id=" . $user->ID .
                                        "&lock=" . Locker::STATE_UNLOCKABLE) ?>">Voir</a>
                                </button>
                            </form> <?php
                            if (current_user_can('direction') || current_user_can('administrator')) { ?>
                                <form action="" method="post">
                                    <select class="export-dropdown button-link-ires">
                                        <option selected disabled>Exporter</option>
                                        <option data-type="excel" data-user-ids="<?php echo $user->ID ?>">Exporter en Excel</option>
                                        <option data-type="csv" data-user-ids="<?php echo $user->ID ?>">Exporter en CSV</option>
                                    </select>
                                </form> <?php
                            } ?>
                        </div>
                    </td> <!-- Last name -->
                    <td><?php echo $user->first_name; ?></td> <!-- First name -->
                    <td><a href="mailto:<?php echo $user->user_email; ?>"><?php echo $user->user_email; ?></a></td> <!-- Email -->
                    <td><?php echo $user->user_login; ?></td> <!-- User login -->
                    <td><?php echo count($groupNames) > 0 ? implode(", ", $groupNames) : "Aucun" ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
            <tfoot> <?php
                if(count($this->searchedMembers) > 9){ ?>
                    <tr>
                        <th class="row-title">Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Identifiant</th>
                        <th>Groupe</th>
                    </tr><?php
                } ?>
            </tfoot>
        </table>
        <?php
    }

    /**
     * @param bool $reverse
     *
     * @return string
     */
    private function getOrder(bool $reverse = false) : string{
        $order = $_GET["order"] ?? "asc";
        return !$reverse ? $order : ($order === "asc" ? "desc" : "asc");
    }
}