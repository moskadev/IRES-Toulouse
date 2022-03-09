<?php

namespace irestoulouse\menus\groups;

use irestoulouse\elements\Group;
use irestoulouse\menus\IresMenu;
use irestoulouse\utils\Locker;

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
        if (strlen($delete = trim($_POST['delete'] ?? "")) > 0 &&
            ($deletedGroup = Group::fromId($delete)) !== null) {
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
        if (Group::isValid($nom = trim($_POST['addGroup'] ?? ""), $type = $_POST['typeAddGroup'] ?? -1)) {
            $message = "Impossible de créer le groupe " . esc_attr($nom);
            $type_message = "error";
            try {
                if(Group::register(esc_attr($nom), intval(esc_attr($type)))){
                    $create = Group::fromName(esc_attr($nom));

                    $type_message = "updated";
                    $message = "Le groupe de " . $create->getName() .
                        ", dénommé <a href=" . home_url("/wp-admin/admin.php?page=details_du_groupe&group=" . $create->getId()) .
                        ">" . $create->getName() . "</a>, a été créé.";
                }
            } catch (\Exception $e){
                // do nothing, the error message is already set
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
    function getContent() : void { ?>
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
                        <button class="confirm-delete button-primary button-delete" type="submit">Confirmer</button>
                        <button class="button-secondary" type="button" data-close-button>Annuler</button>
                    </form>
                </div>
            </div>
        </div>

        <h3>Légende : </h3>
        <p>
            <mark class="underline-blue">Le surlignage bleu signifie que vous êtes membre du groupe</mark><br>
            <mark class="underline-orange">Le surlignage orange signifie que vous êtes membre et responsable du groupe</mark>
        </p>

        <?php
        /*
         * Formulaire pour ajouter un groupe
         *  - Nom du groupe
         *  - Bouton ajouter
         */
        if (current_user_can('administrator')) { ?>
            <form action="" method="post"> <?php
                if(isset($_POST["submitGroup"])){?>
                    <div class="input-register-container input-register-4">
                        <input type="text" name="addGroup" placeholder="Nom du groupe">
                        <select name="typeAddGroup"> <?php
                            foreach (Group::TYPE_NAMES as $type => $name){?>
                                <option value="<?php echo $type?>"><?php echo $name?></option>
                            <?php } ?>
                        </select>
                        <button class="button-primary" type="submit">Ajouter</button>
                        <button class="button-secondary" type="button" onclick="reloadPage()">Annuler</button>
                    </div>
                <?php } else {?>
                    <button type="submit" name="submitGroup"
                            class="button-primary menu-submit button-large">
                        <span class="dashicons dashicons-groups"></span>
                        Ajouter un nouveau groupe
                    </button>
                <?php }
                ?>
            </form> <?php
        }

        /*
         * Affichage des groupes auquel l'utilisateur appartient
         *
         * Possibilité de l'afficher si il y a plus de 9 groupes créé afin d'alléger la page :
         * && count($groups) > 9
         */
        if (count(Group::getUserGroups(wp_get_current_user())) > 0) { ?>
            <h2 class="title-label">Vos groupes : </h2> <?php
            self::printGroups(Group::getUserGroups(wp_get_current_user()));
        }  ?>

        <h2 class="title-label">Groupes : </h2> <?php
        self::printGroups(Group::all());
    }

    /**
     * Print the row of a table for the group given in parameter
     *
     * @param $groups Group[] groups to print
     */
    private function printGroups(array $groups) {
        $currentUser = wp_get_current_user();
        if (count($groups) > 0) {?>
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
                    $respNames = array_map(function($u) {
                        return "<a href='" . home_url("/wp-admin/admin.php?page=mon_profil_ires&user_id=" . $u->ID .
                                "&lock=" . Locker::STATE_LOCKED) . "'>" . $u->first_name . " " . $u->last_name . "</a>";
                    }, $group->getResponsables()); ?>
                    <tr class="<?php
                        if ($group->isUserResponsable($currentUser)) echo "is-resp ";
                        else if ($group->userExists($currentUser)) echo "row-hover"?>">
                        <!-- Name of the group -->
                        <th>
                            <a class="text-decoration-none"
                               href="<?php echo home_url("/wp-admin/admin.php?page=details_du_groupe&group=" . $group->getId()) ?>">
                                <?php echo $group->getName() ?>
                            </a>
                        </th>
                        <!-- Group's type -->
                        <td> <?php echo Group::TYPE_NAMES[$group->getType()] ?></td>
                        <!-- Name of the users in charge of the group -->
                        <td> <?php echo implode(", ", $respNames) ?></td>
                        <!-- Date -->
                        <td><?php echo $group->getCreationTime() ?></td>
                        <td class="hide-actions">
                            <?php
                            if (current_user_can('administrator') || $group->isUserResponsable($currentUser)) {?>
                                <form method="post">
                                <button type="button" id="modify" name="modify" value="<?php echo $group->getId() ?>"
                                        class="button-secondary"
                                        onclick="location.href='<?php echo home_url("/wp-admin/admin.php?page=details_du_groupe&group=" . $group->getId()) ?>'">
                                    Modifier
                                </button> <?php
                                if (current_user_can('administrator')) {?>
                                <button type="button" id="delete" name="" value="<?php echo $group->getId() ?>"
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
                <?php }  ?>
            </table> <?php
        } else { ?>
            <p>Aucun groupe n'existe</p>
        <?php }
    }
}