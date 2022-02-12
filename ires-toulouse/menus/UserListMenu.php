<?php

namespace menus;

use irestoulouse\menus\IresMenu;

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
        <table>
            <tr>
                <td>
                    <form action="<?php echo get_site_url()."/wp-admin/admin.php?page=ajouter_un_compte"; ?>" method="post">
                        <?php   if (current_user_can('responsable') || current_user_can('administrator')) { ?>
                            <p class="search-box">
                                <input class="button-secondary" type="submit" value="Ajouter un membre"/>
                            </p>
                        <?php   }?>
                    </form>
                </td>
                <td>
                    <form method="get">
                        <p class="search-box">
                            <input type="search" id="search">
                            <input class="button-secondary" type="submit" value="Recher des comptes"/>
                        </p>
                    </form>
                </td>
            </tr>
        </table>

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
            $users = self::getAllMembers();
            foreach ($users as $user) {
                $user->first_name = get_user_meta($user->ID, 'first_name')[0];
                $user->last_name = get_user_meta($user->ID, 'last_name')[0];
            }

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
                            <?php   if (current_user_can('responsable') || current_user_can('administrator')) { ?>
                                <a href="">Modifier</a>&emsp;
                            <?php   } if (current_user_can('administrator')) { ?>
                                <a href="" onclick="submitDeletion()" class="delete">Supprimer</a>&emsp;
                            <?php   }?>
                            <a href="">Voir</a>
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


    private function getAllMembers() {
        global $wpdb;
        $sql = "SELECT ID, user_login, user_email FROM {$wpdb->prefix}users";
        return $wpdb->get_results($wpdb->prepare($sql));
    }

    private function sens() {
        return (isset($_GET['order']) && $_GET['order'] === "asc") ? "desc" : "asc";
    }
}
?>
<script type="text/javascript">
    function submitDeletion() {
        let form = document.getElementById('deleteMember');
        if (confirm("Etes vous sur de vouloir supprimer ?")) {
            form.submit();
        }
    }
</script>
<style>
    #hide-info {
        display: none;
    }
    #line {
        height: 5vw;
    }
    #line:hover #hide-info {
        display: inherit;
    }
    .name {
        width: 25%;
    }
    .delete {
        color: #d63638;
    }
    .delete:hover {
        color: #8a2424;
    }
    align-right {
        margin-left: 50px;
    }
</style>