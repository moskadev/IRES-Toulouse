<?php

namespace menus;

use irestoulouse\controllers\UserConnection;
use irestoulouse\elements\Group;
use irestoulouse\menus\IresMenu;
use stdClass;

class UserListMenu extends IresMenu {

    /** @var array */
    private array $users;

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
        if (current_user_can('administrator') && isset($_POST['delete']) && $_POST['user_id']) {
            try {
                $deletedUser = get_userdata($_POST['user_id']);
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
        $this->users = self::getAllMembers($_GET['search'] ?? '');

        // Sorting of the users
        if (isset($_GET['orderby']) && $_GET['orderby'] === 'last_name') { // Sorting by last name
            isset($_GET['order']) && $_GET['order'] === 'asc' ?
                usort($this->users, function($a, $b) {return strcmp($a->last_name, $b->last_name);}) :
                usort($this->users, function($a, $b) {return strcmp($b->last_name, $a->last_name);});
        }
        if (isset($_GET['orderby']) && $_GET['orderby'] === 'first_name') { // Sorting by last name
            isset($_GET['order']) && $_GET['order'] === 'asc' ?
                usort($this->users, function($a, $b) {return strcmp($a->first_name, $b->first_name);}) :
                usort($this->users, function($a, $b) {return strcmp($b->first_name, $a->first_name);});
        }
    }

    public function getContent() : void {?>
        <!-- Confirmation popup for deletion of a user -->
        <div class="popup-delete" id="popup-delete">
            <div class="popup-header">
                <div id="popup-title" class="title"></div>
                <button data-close-button class="close-button">&times;</button>
            </div>
            <div class="popup-body">
                Êtes-vous sur de vouloir supprimer ce compte ?
                <form action="" method="post">
                    <input type="hidden" id="userId" name="user_id" value="">
                    <input class="button-primary" name="delete" type="submit" value="Confirmer"/>
                    <input class="button-secondary" data-close-button type="button" value="Annuler"/>
                </form>
            </div>
        </div>
        <div id="overlay"></div>


        <div class="grid-container">
            <div class="form-add-member">
                <form action="/wp-admin/admin.php?page=ajouter_un_compte">
                    <?php   if (current_user_can('responsable') || current_user_can('administrator')) { ?>
                        <p class="add-member">
                            <input class="button-secondary" type="submit" value="Ajouter un membre"/>
                        </p>
                    <?php   }?>
                </form>
            </div>
            <div class="search">
                <form action="" method="get">
                    <p class="search-box">
                        <input type="hidden" id="action" name="page" value="comptes_ires">
                        <input type="text" id="search" placeholder="Recherche" name="search" value="<?php if(isset($_GET['search'])) echo $_GET['search']; ?>">
                        <input class="button-secondary" type="submit" value="Rechercher des comptes"/>
                        <input class="button-delete" type="submit"  onclick="document.getElementById('search').value = '';" value="Effacer"/>
                    </p>
                </form>
            </div>
        </div>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th class="manage-column column-username column-primary sortable <?php echo self::sens(); ?>">
                        <a href="/wp-admin/admin.php?page=comptes_ires&orderby=last_name&order=<?php echo self::sens(); ?>"><!-- order = asc ou desc-->
                            <span>Nom</span>
                            <span id="sorting-indicator-last_name" class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="manage-column column-username column-primary sortable <?php echo self::sens(); ?>">
                        <a href="/wp-admin/admin.php?page=comptes_ires&orderby=first_name&order=<?php echo self::sens(); ?>"><!-- order = asc ou desc-->
                            <span>Prénom</span>
                            <span id="sorting-indicator-first_name" class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th>Email</th>
                    <th>Identifiant</th>
                    <th>Groupe</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $counter = 0;
            foreach ($this->users  as $user) {
                if($user->ID === get_current_user_id()){
                    continue;
                }
                $groupNames = array_map(function ($g){
                    return "<a href='/wp-admin/admin.php?page=details_du_groupe&group=" .
                        $g->getId() . "'>" . $g->getName() . "</a>";
                }, Group::getUserGroups($user)); ?>
                <tr id="line">
                    <td class="name"><?php echo $user->last_name; ?><br/>
                        <form id="hide-info" method="post" action=""><?php
                            if (in_array($user, Group::getVisibleUsers(wp_get_current_user()))) { ?>
                                <button type="submit" class="button-link-ires">
                                    <a href="/wp-admin/admin.php?page=mon_profil_ires&user_id=<?php echo $user->ID ?>&lock=0">Modifier</a>
                                </button><?php
                            }
                            if (current_user_can('administrator') && !user_can($user, "administrator")) { ?>
                                <button type="button" data-popup-target="#popup-delete" class="delete" onclick="setUserInfo(<?php echo "'" . $user->ID  . '\',\'' . $user->first_name . '\',\'' . $user->last_name .'\''; ?>)">Supprimer</button>&emsp; <?php
                            }?>
                            <button type="submit" class="button-link-ires">
                                <a href="/wp-admin/admin.php?page=mon_profil_ires&user_id=<?php echo $user->ID ?>&lock=1">Voir</a>
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
                $counter++;
            }
            ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="row-title">Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Identifiant</th>
                    <th>Groupe</th>
                </tr>
            </tfoot>
        </table>
        <?php
    }

    /**
     * @param string $search
     * @return array
     */
    private function getAllMembers(string $search): array {
        global $wpdb;
        $search = strtolower($search);
        $sql = "SELECT ID, user_login, user_email FROM {$wpdb->prefix}users WHERE ID != 1";
        $sql2 = "SELECT user_id, meta_value FROM {$wpdb->prefix}usermeta WHERE meta_key = 'first_name' AND user_id != 1";
        $sql3 = "SELECT user_id, meta_value FROM {$wpdb->prefix}usermeta WHERE meta_key = 'last_name' AND user_id != 1";
        $logins = $wpdb->get_results($wpdb->prepare($sql));
        $first_name = $wpdb->get_results($wpdb->prepare($sql2));
        $last_name = $wpdb->get_results($wpdb->prepare($sql3));

        foreach ($logins as $login) {
            foreach ($first_name as $a)
                if ($login->ID === $a->user_id)
                    $login->first_name = $a->meta_value;
            foreach ($last_name as $b)
                if ($login->ID === $b->user_id)
                    $login->last_name = $b->meta_value;
        }

        $results = [];
        foreach($logins as $login) {
            if (str_contains(strtolower($login->first_name), $search) ||
                str_contains(strtolower($login->last_name), $search) ||
                str_contains(strtolower($login->user_login), $search) ||
                str_contains(strtolower($login->user_email), $search)) {

                if(($u = get_userdata($login->ID)) !== false){
                    $results[] = $u;
                }
            }
        }
        return $results;
    }

    private function sens(): string {
        return (isset($_GET['order']) && $_GET['order'] === "asc") ? "desc" : "asc";
    }
}
?>