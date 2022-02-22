<?php

namespace menus;

use irestoulouse\controllers\UserConnection;
use irestoulouse\elements\Group;
use irestoulouse\menus\IresMenu;

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
                    </button><?php
                }?>
            </div>
            <form action="" method="get">
                <input type="hidden" name="page" value="<?php echo $this->getId() ?>"/>
                <input type="text" placeholder="Recherche" name="search" value="<?php if(isset($_GET['search'])) echo $_GET['search']; ?>">
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
                if($user->ID === get_current_user_id()){
                    continue;
                }
                $groupNames = array_map(function ($g){
                    return "<a href='" . home_url("/wp-admin/admin.php?page=details_du_groupe&group=" .
                        $g->getId()) . "'>" . $g->getName() . "</a>";
                }, Group::getUserGroups($user)); ?>
                <tr>
                    <td class="name"><?php echo $user->last_name; ?><br/>
                        <form class="hide-actions" method="post" action=""><?php
                            if (in_array($user, Group::getVisibleUsers(wp_get_current_user()))) { ?>
                                <button type="submit" class="button-link-ires">
                                    <a href="<?php echo home_url("/wp-admin/admin.php?page=mon_profil_ires&user_id=" . $user->ID . "&lock=0") ?>">Modifier</a>
                                </button>|<?php
                            }
                            if (current_user_can('administrator') && !user_can($user, "administrator")) { ?>
                                <button type="button" data-popup-target class="delete-link" onclick="setUserInfo(<?php echo "'" . $user->ID  . '\',\'' . $user->first_name . '\',\'' . $user->last_name .'\''; ?>)">Supprimer</button><?php
                            }?>|
                            <button type="submit" class="button-link-ires">
                                <a href="<?php echo home_url("/wp-admin/admin.php?page=mon_profil_ires&user_id=" . $user->ID . "&lock=1") ?>">Voir</a>
                            </button>
                        </form>
                    </td> <!-- Last name -->
                    <td><?php echo $user->first_name; ?></td> <!-- First name -->
                    <td><a href="mailto:<?php echo $user->user_email; ?>"><?php echo $user->user_email; ?></a></td> <!-- Email -->
                    <td><?php echo $user->user_login; ?></td> <!-- User login -->
                    <td><?php echo count($groupNames) > 0 ? implode(", ", $groupNames) : "Aucun" ?></td>
                </tr>
                <form id="deleteMember" action="" method="post">
                    <input type="hidden" value="<?php echo $user->ID; ?>">
                </form>
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