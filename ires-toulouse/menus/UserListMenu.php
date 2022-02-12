<?php

namespace menus;

use irestoulouse\menus\IresMenu;
use stdClass;

class UserListMenu extends IresMenu {
    public function __construct() {
        parent::__construct(
            "Liste des profils de l'IRES de Toulouse",
            "Comptes IRES",
            0,
            "dashicons-id-alt",
            3
        );
    }
    public function getContent() : void {
        if (isset($_GET['order']) && isset($_GET['orderby'])) {
            if ($_GET['orderby'] === "first_name" && $_GET['order'] === "desc") {
                echo "<style>#sorting-indicator-first_name { transform: rotate(180deg); }</style>";
            } elseif ($_GET['orderby'] === "last_name" && $_GET['order'] === "desc") {
                echo "<style>#sorting-indicator-last_name { transform: rotate(180deg); }</style>";
            }
        }

        ?>
        <div class="grid-container">
            <div class="form-add-member">
                <form action="<?php echo get_site_url()."/wp-admin/admin.php?page=ajouter_un_compte"; ?>" method="post">
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
                        <input type="search" id="search" name="search">
                        <input class="button-secondary" type="submit" value="Rechercher des comptes"/>
                    </p>
                </form>
            </div>
        </div>

        <div class="popup-delete" id="popup-delete">
            <div class="popup-header">
                <div class="title">Suppression de : Nom Prénom</div>
                <button data-close-button class="close-button">&times;</button>
            </div>
            <div class="popup-body">
                Êtes-vous sur de vouloir supprimer ce compte ?
                <form action="" method="post">

                    <input class="button-primary" name="delete" type="submit" value="Confirmer"/>
                    <input class="button-secondary" data-close-button type="button" value="Annuler"/>
                </form>
            </div>
        </div>
        <div id="overlay"></div>
        <table class="widefat">
            <thead>
                <tr>
                    <th class="manage-column column-username column-primary sortable desc">
                        <a href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=comptes_ires&orderby=last_name&order=<?php echo self::sens(); ?>"><!-- order = asc ou desc-->
                            <span>Nom</span>
                            <span id="sorting-indicator-last_name" class="sorting-indicator"></span>
                        </a>
                    </th>
                    <th class="manage-column column-username column-primary sortable desc">
                        <a href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=comptes_ires&orderby=first_name&order=<?php echo self::sens(); ?>"><!-- order = asc ou desc-->
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

            $users = self::getAllMembers($_GET['search'] ?? '');

            // Sorting of the users
            if (isset($_GET['orderby']) && $_GET['orderby'] === 'last_name') { // Sorting by last name
                isset($_GET['order']) && $_GET['order'] === 'asc' ?
                    usort($users, function($a, $b) {return strcmp($a->last_name, $b->last_name);}) :
                    usort($users, function($a, $b) {return strcmp($b->last_name, $a->last_name);});
            }
            if (isset($_GET['orderby']) && $_GET['orderby'] === 'first_name') { // Sorting by last name
                isset($_GET['order']) && $_GET['order'] === 'asc' ?
                    usort($users, function($a, $b) {return strcmp($a->first_name, $b->first_name);}) :
                    usort($users, function($a, $b) {return strcmp($b->first_name, $a->first_name);});
            }

            foreach ($users as $user) {
                if ((int) $user->ID === 1)
                    continue;
                ?>
                <tr id="line" class="<?php if ($counter % 2 == 0) echo "alternate"; ?>"> <!-- Class alternate if row/2 == 0 -->
                    <td class="name">
                        <?php echo $user->last_name; ?>
                        <br/>
                        <span id="hide-info">
                            <form method="post">
                                <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                                <input type="hidden" name="user_login" value="<?php echo $user->user_login; ?>">
                                <input type="hidden" name="user_email" value="<?php echo $user->user_email; ?>">
                                <input type="hidden" name="first_name" value="<?php echo $user->first_name; ?>">
                                <input type="hidden" name="last_name" value="<?php echo $user->last_name; ?>">
                                <?php   if (current_user_can('responsable') || current_user_can('administrator')) { ?>
                                    <a href="">Modifier</a>&emsp;
                                <?php   } if (current_user_can('administrator')) { ?>
                                    <button type="button" data-popup-target="#popup-delete" class="delete">Supprimer</button>&emsp;
                                <?php   }?>
                                <a href="">Voir</a>
                            </form>
                        </span>
                    </td> <!-- Last name -->
                    <td class=""><?php echo $user->first_name; ?></td> <!-- First name -->
                    <td class=""><a href="mailto:<?php echo $user->user_email; ?>"><?php echo $user->user_email; ?></a></td> <!-- Email -->
                    <td class=""><?php echo $user->user_login; ?></td> <!-- User login -->
                    <td class=""><?php  ?></td> <!-- Group -->
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
                $array = new stdClass();
                $array->ID = $login->ID;
                $array->first_name = $login->first_name;
                $array->last_name = $login->last_name;
                $array->user_login = $login->user_login;
                $array->user_email = $login->user_email;
                array_push($results, $array);
            }
        }
        return $results;
    }

    private function sens(): string {
        return (isset($_GET['order']) && $_GET['order'] === "asc") ? "desc" : "asc";
    }
}
?>