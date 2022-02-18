<?php

namespace irestoulouse\menus\groups;

use irestoulouse\elements\Group;
use irestoulouse\menus\IresMenu;

class GroupListMenu extends IresMenu {

    public function __construct() {
        parent::__construct("Liste des groupes de l'IRES de Toulouse", // Page title when the menu is selected
            "Groupes IRES", // Name of the menu
            0, // Menu access security level
            "dashicons-businesswoman", // Menu icon
            3 // Page position in the list
        );
    }

    /**
     * Show depending on the content that has been sent
     * different error/warning/success messages
     */
    public function analyzeSentData() : void {
        $message = $type_message = "";

        /*
         * Supprime un groupe
         */
        if (isset($_POST['delete']) && ($deletedGroup = Group::fromId($_POST['delete'])) !== null) {
            $message = "Le groupe " . $deletedGroup->getName() . " n'a pas pu être supprimé.";
            $type_message = "error";
            if (Group::delete($deletedGroup->getId())) {
                $message = "Le groupe " . $deletedGroup->getName() . " a été supprimé.";
                $type_message = "updated";
            }
        }

        /*
         * Ajoute un groupe si possible
         */
        if (isset($_POST['addGroup']) && isset($_POST['nameAddGroup']) && isset($_POST['typeAddGroup'])) {
            $message = "Impossible de créer le groupe " . esc_attr($_POST['nameAddGroup']);
            $type_message = "error";

            Group::createTable();
            if (Group::register(esc_attr($_POST['nameAddGroup']), intval(esc_attr($_POST['typeAddGroup'])))) {
                $type_message = "updated";
                $message = "Le groupe de " . Group::TYPE_NAMES[$_POST['typeAddGroup']] .
                    ", dénommé " . $_POST['nameAddGroup'] . ", a été créé.";
            }
        }

        /*
         * Affichage d'un message
         */
        if (!empty($message) && !empty($type_message)) { ?>
            <!-- Affichage du message d'erreur ou de réussite en cas d'ajout d'un utilisateur au groupe -->
            <div id="message" class="<?php echo $type_message ?> notice is-dismissible">
                <p><strong><?php echo $message; ?></strong></p>
            </div>
            <?php
        }
    }

    /**
     * Contents of the "Create a group" menu
     * Allows to :
     *      - create a group of user if you are admin
     */
    function getContent() : void {
        /*
         * Formulaire pour ajouter un groupe
         *  - Nom du groupe
         *  - Bouton ajouter
         */
        if (current_user_can('administrator')) { ?>
            <form action="" method="post">
                <div class="container">
                    <div class="row">
                        <div class="col-2">
                            <label for="addGroup">Ajouter un groupe :</label>
                        </div>
                        <div class="col">
                            <input type="text" id="addGroup" class="form-control h-100"
                                   name="nameAddGroup" placeholder="Nom du groupe">
                        </div>
                        <div class="col">
                            <select class="form-control h-100" name="typeAddGroup"> <?php
                                foreach (Group::TYPE_NAMES as $type => $name){?>
                                    <option value="<?php echo $type?>"><?php echo $name?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col">
                            <input type="submit" name="addGroup" value="Ajouter"
                                   class="btn btn-outline-primary">
                        </div>
                    </div>
                </div>
            </form> <?php
        }

        /*
         * Affichage des groupes auquel l'utilisateur appartient
         *
         * Possibilité de l'afficher si il y a plus de 9 groupes créé afin d'alléger la page :
         * && count($groups) > 9
         */
        if (count(Group::getUserGroups(wp_get_current_user())) > 0) { ?>
            <h2 class="title-label">Vos groupes : </h2>
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Type</th>
                    <th scope="col">Responsable(s)</th>
                    <th scope="col">Date de création</th>
                </tr>
                </thead>
                <tbody>
                <?php
                /*
                 * Affichage de chaque ligne
                 */
                foreach (Group::getUserGroups(wp_get_current_user()) as $group) {
                    self::printGroup($group);
                }
                ?>
                </tbody>
            </table>
            <?php
        }
        ?>

        <h2 class="title-label">Groupes : </h2>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Type</th>
                    <th scope="col">Responsable(s)</th>
                    <th scope="col">Date de création</th>
                </tr>
            </thead>
            <tbody>
                <?php
                /*
                 * Affichage de tous les groupes
                 */
                $groups = Group::all();
                foreach ($groups as $group) {
                    self::printGroup($group);
                }
                /*
                 * Affichage d'un message si aucun groupe n'existe
                 */
                if (count($groups) === 0) { ?>
                    <tr>
                        <td colspan="4"><?php _e("No existing group") ?></td>
                    </tr> <?php
                } ?>
            </tbody>
            <?php

            /*
             * Affichage du bas de page si il y a plus de 9 groupes
             */
            if (count($groups) > 9) { ?>
                <tfoot>
                    <tr>
                        <th scope="col">Nom</th>
                        <th scope="col">Type</th>
                        <th scope="col">Responsable(s)</th>
                        <th scope="col">Date de création</th>
                    </tr>
                </tfoot>
            <?php }  ?>
        </table> <!-- Fin du tableau de l'affichage de tous les groupes -->
        <?php
    }

    /**
     * Print the row of a table for the group given in parameter
     *
     * @param $group Group the group to print
     */
    private function printGroup(Group $group) {
        $user = wp_get_current_user();
        $users = $group->getUsers();

        $responsables = $group->getResponsables(); ?>
        <tr class="<?php if (in_array($user, $users)) {
            echo "table-primary";
        } ?>">
            <!-- Name of the group -->
            <th scope="row" class="text-primary">
                <a class="text-decoration-none"
                   href="/wp-admin/admin.php?page=details_du_groupe&group=<?php echo $group->getId() ?>">
                    <?php echo $group->getName() ?>
                </a>
            </th>
            <!-- Group's type -->
            <td> <?php echo Group::TYPE_NAMES[$group->getType()] ?></td>
            <!-- Name of the users in charge of the group -->
            <td> <?php
                echo implode(", ", array_map(function($u) {
                    return $u->first_name . " " . $u->last_name;
                }, $responsables));  ?>
            </td>
            <!-- Date -->
            <td>
                <?php echo $group->getCreationTime() ?>
            </td>
            <td>
                <?php
                if (current_user_can('administrator') || (current_user_can('responsable') && $group->isUserResponsable($user))) {
                    ?>
                    <form action="" method="post">
                        <button type="button"
                                id="modify"
                                name="modify"
                                value="<?php echo $group->getId() ?>"
                                class="btn btn-outline-secondary btn-sm"
                                onclick="location.href='/wp-admin/admin.php?page=details_du_groupe&group=<?php echo $group->getId() ?>'">
                            Modifier
                        </button>
                        <?php
                        if (current_user_can('administrator')) {
                            ?>
                            <button type="submit"
                                    id="delete"
                                    name="delete"
                                    value="<?php echo $group->getId() ?>"
                                    class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Êtes vous sur de vouloir supprimer le groupe : <?php echo $group->getName(); ?> ?');">
                                <?php echo __('Delete') ?>
                            </button>
                            <?php
                        }
                        ?>
                    </form>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php
    }
}