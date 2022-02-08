<?php

namespace irestoulouse\menus\groups;

use irestoulouse\elements\Group;
use irestoulouse\menus\IresMenu;

class GroupListMenu extends IresMenu {

    public function __construct() {
        parent::__construct("Liste des groupes de l'IRES de Toulouse", // Page title when the menu is selected
            "Groupes", // Name of the menu
            0, // Menu access security level
            "dashicons-businesswoman", // Menu icon
            3 // Page position in the list
        );
    }

    /**
     * Contents of the "Create a group" menu
     * Allows to :
     *      - create a group of user if you are admin
     */
    function getContent() : void {
        $this->showMessages();

        /*
         * Formulaire pour ajouter un groupe
         *  - Nom du groupe
         *  - Bouton ajouter
         */
        if (current_user_can('administrator')) {
            ?>
            <form action="" method="post">
                <div class="container">
                    <div class="row">
                        <div class="col-2">
                            <label for="addGroup">Ajouter un groupe :</label>
                        </div>
                        <div class="col">
                            <input type="text" id="addGroup" class="to-fill"
                                   name="nameAddGroup" placeholder="Nom du groupe">
                        </div>
                        <div class="col">
                            <input type="submit" name="addGroup" value="Ajouter"
                                   class="btn btn-outline-primary">
                        </div>
                    </div>
                </div>
            </form>

            <?php
        } // End if

        /*
         * Affichage des groupes auquel l'utilisateur appartient
         *
         * Possibilité de l'afficher si il y a plus de 9 groupes créé afin d'alléger la page :
         * && count($groups) > 9
         */
        if (count(Group::getUserGroups(wp_get_current_user())) > 0) {
            ?>
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Responsable(s)</th>
                    <th scope="col">Date de création</th>
                    <th scope="col"></th>
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

        <h3>Groupes : </h3>
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th scope="col">Nom</th>
                <th scope="col">Responsable(s)</th>
                <th scope="col">Date de création</th>
                <th scope="col"></th>
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
            } // end foreach ?>
            </tbody>
            <?php /*
            * Affichage d'un message si aucun groupe n'existe
            */
            if (count($groups) === 0) { ?>
                <tr>
                    <td colspan="4"><?php _e("No existing group") ?></td>
                </tr>
            <?php } // endif

            /*
             * Affichage du bas de page si il y a plus de 9 groupes
             */
            if (count($groups) > 9) { ?>
                <tfoot>
                <tr>
                    <td>Nom</td>
                    <td>Responsable</td>
                    <td>Date de création</td>
                    <td></td>
                </tr>
                </tfoot>
            <?php } // endif ?>
        </table> <!-- Fin du tableau de l'affichage de tous les groupes -->
        <?php
    } // end function getContent()

    /**
     * Show depending on the content that has been sent
     * different error/warning/success messages
     */
    private function showMessages(){
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
            ?>
            <form action="" method="post" id="message">
                <input type="hidden" name="message" value="<?php echo $message ?>">
                <input type="hidden" name="type" value="<?php echo $type_message ?>">
            </form>

            <!-- Envoi du formulaire caché -->
            <script type="text/javascript">
                document.getElementById('message').submit(); // SUBMIT FORM
            </script>
            <?php
        }

        /*
         * Ajoute un groupe si possible
         */
        if (isset($_POST['addGroup']) && isset($_POST['nameAddGroup'])) {
            $message = "Impossible de créer le groupe.";
            $type_message = "error";

            Group::createTable();
            if (Group::register(esc_attr($_POST['nameAddGroup']))) {
                $type_message = "updated";
                $message = "Le groupe " . $_POST['nameAddGroup'] . " a été créé.";
            }
            ?>
            <form action="" method="post" id="message">
                <input type="hidden" name="message" value="<?php echo $message ?>">
                <input type="hidden" name="type" value="<?php echo $type_message ?>">
            </form>

            <!-- Envoi du formulaire caché -->
            <script type="text/javascript">
                document.getElementById('message').submit(); // SUBMIT FORM
            </script>
            <?php
        }

        /*
         * Affichage d'un message
         */
        if (isset($_POST['message']) && isset($_POST['type'])) { ?>
            <!-- Affichage du message d'erreur ou de réussite en cas d'ajout d'un utilisateur au groupe -->
            <div id="message" class="<?php echo $_POST['type']; ?> notice is-dismissible">
                <p><strong><?php echo stripslashes($_POST['message']); ?></strong></p>
            </div>
            <?php
        }
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
                   href="<?php echo get_site_url() ?>/wp-admin/admin.php?page=details_du_groupe&group=<?php echo $group->getId() ?>">
                    <?php echo $group->getName() ?>
                </a>
            </th>
            <!-- Name of the user in charge of the group -->
            <td class="">
                <?php
                $i = 0;
                foreach ($responsables as $resp) {
                    $i ++;
                    echo $resp->first_name . " " . $resp->last_name;
                    if (count($responsables) > 1 && $i < count($responsables)) {
                        echo ", ";
                    }
                }
                ?>
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
                                onclick="location.href='<?php echo get_site_url() ?>/wp-admin/admin.php?page=details_du_groupe&group=<?php echo $group->getId() ?>'">
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