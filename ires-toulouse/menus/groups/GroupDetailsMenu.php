<?php

namespace irestoulouse\menus\groups;

use irestoulouse\elements\Group;
use irestoulouse\menus\IresMenu;
use irestoulouse\utils\Locker;

class GroupDetailsMenu extends IresMenu {

    /** @var Group|null */
    private ?Group $group = null;

    public function __construct() {
        $groupName = "";
        try {
            if (($group = Group::fromId($_GET["group"] ?? - 1)) !== null) {
                $groupName = $group->getName();
            }
        } catch (\Exception $e) {
        }
        parent::__construct("Détails du groupe <b>" . $groupName . "</b>",
            "Détails du groupe", // Name of the menu
            0, // Menu access security level
            "dashicons-businesswoman", // Menu icon
            4 // Page position in the list
        );
    }

    public function analyzeSentData() : void {
        $message = $type_message = "";

        try {
            $this->group = Group::fromId($_GET["group"]);
            /*
             * Poste un message si un membre est ajouté
             */
            if (strlen($_POST['submitMember'] ?? "") > 0) {
                $newMemberLogin = $_POST['submitMember'];

                $message = "Erreur, l'utilisateur $newMemberLogin n'a pas pu être ajouté car il est déjà présent dans le groupe.";
                $type_message = "error";
                if (($newMember = get_user_by("login", $newMemberLogin)) === false) {
                    $message = "Erreur, l'utilisateur $newMemberLogin n'a pas pu être ajouté car il n'existe pas.";
                } else if ($this->group->addUser($newMember)) {
                    $message = "L'utilisateur $newMemberLogin a été ajouté au groupe " . $this->group->getName() . ".";
                    $type_message = "updated";
                }
            }

            /*
             * Poste un message si un membre est retiré du groupe
             */
            if (strlen($_POST['removeMember'] ?? "") > 0) {
                $message = "Une erreur s'est produite lors de la suppression d'un membre.";
                $type_message = "error";
                try {
                    if ($this->group->removeUser($user = get_userdata($_POST['removeMember']))) {
                        $message = "L'utilisateur {$user->user_login} a été supprimé du groupe.";
                        $type_message = "updated";
                    }
                } catch (\Exception $e) {
                    // message already set
                }
            }

            /*
             * Poste un message si un responsable est supprimé
             */
            if (strlen($_POST['deleteResp'] ?? "") > 0) {
                $message = "Une erreur s'est produite lors de la suppression d'un responsable.";
                $type_message = "error";
                try {
                    $deletedResponsable = get_userdata($_POST['deleteResp']);
                    if ($deletedResponsable !== false && $this->group->removeResponsable($deletedResponsable)) {
                        $message = $deletedResponsable->user_login . " a été retiré des responsables du groupe.";
                        $type_message = "updated";
                    }
                } catch (\Exception $e) {
                    // message already set
                }
            }

            /*
             * Poste un message si un nouveau responsable est tenté d'être créé
             */
            if (strlen($_POST['submitResponsable'] ?? "") > 0) {
                $newResponsableLogin = $_POST['submitResponsable'];
                $newResponsable = get_user_by("login", $newResponsableLogin);

                $message = "Erreur, l'utilisateur $newResponsableLogin n'a pas pu être ajouté car il est déjà responsable.";
                $type_message = "error";
                if ($newResponsable === false) {
                    $message = "Erreur, l'utilisateur $newResponsableLogin n'a pas pu être ajouté car il n'existe pas.";
                } else if ($this->group->addResponsable($newResponsable)) {
                    $message = "L'utilisateur $newResponsableLogin a été ajouté en tant que responsable du groupe {$this->group->getName()}.";
                    $type_message = "updated";
                } else if (count($this->group->getResponsables()) > Group::MAX_RESPONSABLES) {
                    $message = "Erreur, il ne peut y avoir plus de " . Group::MAX_RESPONSABLES . " responsables.";
                    $type_message = "error";
                }
            }
        } catch (\Exception $e) {
            $message = "Le groupe à modifier n'existe pas";
            $type_message = "error";
        }
        if ($this->group === null) {
            $message = "Le groupe à modifier n'existe pas";
            $type_message = "error";
        }

        if (!empty($message) && !empty($type_message)) { ?>
            <div id="message" class="<?php echo $type_message ?> notice is-dismissible">
                <p><strong><?php echo $message ?></strong></p>
            </div><?php
        }
    }

    /**
     * @inheritDoc
     */
    public function getContent() : void { 
        var_dump($_POST);       
        if ($this->group === null) {
            return;
        }
        $responsables = $this->group->getResponsables(); ?>

        <!-- Confirmation popup for deletion of a user from the group -->
        <div class="popup">
            <div class="popup-element">
                <div class="popup-header">
                    <p class="title popup-title"></p>
                    <button data-close-button class="close-button">&times;</button>
                </div>
                <div class="popup-body">
                    <p id="text">Êtes-vous sûr de vouloir retirer cet utilisateur de ce groupe ?</p>
                    <form action="" method="post">
                        <input type="hidden" id="removeMember" name="removeMember" value="">
                        <input type="hidden" id="deleteResp" name="deleteResp" value="">
                        <button class="confirm-delete button-primary button-delete" type="submit">Confirmer</button>
                        <button class="button-secondary" type="button" data-close-button>Annuler</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bouton retour & titre de la page -->
        <form action="<?php echo home_url("/wp-admin/admin.php?page=groupes_ires") ?>" method="post">
            <button class="button-secondary">< Retour à la page des groupes</button>
        </form>

        <h2 class="title-label">Responsable<?php if (count($responsables) >= 2)
                echo "s" ?> :</h2>

        <!-- Bouton de l'ajout d'un nouveau responsable -->
        <?php
        if (count($responsables) < Group::MAX_RESPONSABLES && (current_user_can("administrator") ||
                $this->group->isUserResponsable(wp_get_current_user()))) { ?>
            <form action="" method="post"> <?php
                if (isset($_POST["modifResponsable"])) { ?>
                    <div class="input-register-container input-register-3">
                        <input type="text" placeholder="Identifiant du responsable à ajouter"
                               name="submitResponsable">
                        <button class="button-primary" type="submit">Ajouter</button>
                        <button class="button-secondary" type="submit">Annuler</button>
                    </div>
                <?php
                } else { ?>
                    <button type="submit" name="modifResponsable"
                            class="button-primary menu-submit button-large">
                        <span class="dashicons dashicons-businesswoman"></span>
                        Ajouter un nouveau responsable
                    </button>
                <?php } ?>
            </form><?php
        }

        if(count($responsables) > 0){ ?>
            <!-- Affichage des responsables -->
            <table class="widefat data-table striped">
                <thead>
                    <tr>
                        <th class="row-title">Nom</th>
                        <th class="row-title">Prénom</th>
                        <th class="row-title">Identifiant</th>
                        <th class="row-title" colspan="3"></th>
                    </tr>
                </thead>
                <tbody> <?php // Affichage de tous les utilisateurs du groupe
                    foreach ($this->group->getResponsables() as $resp) {
                        $first_name = $resp->first_name;
                        $last_name = $resp->last_name; ?>
                        <tr class="<?php if($resp->ID === get_current_user_id()) echo "row-hover" ?>">
                            <td><?php echo $first_name ?></td>
                            <td><?php echo $last_name ?></td>
                            <td><?php echo $resp->user_login ?></td>
                            <td class="hide-actions">
                                <form action="" method="post">
                                    <button type="button" class="button-secondary"
                                            onclick="location.href='<?php echo home_url("/wp-admin/admin.php?page=mon_profil_ires&user_id=" . $resp->ID .
                                                "&lock=" . Locker::STATE_UNLOCKABLE) ?>'">
                                        Voir
                                    </button> <?php
                                    if (current_user_can("administrator")) { ?>
                                        <button type="button" class="button-secondary"
                                                onclick="location.href='<?php echo home_url("/wp-admin/admin.php?page=mon_profil_ires&user_id=" . $resp->ID .
                                                    "&lock=" . Locker::STATE_UNLOCKED) ?>'">
                                            Modifier
                                        </button>
                                        <button type="button" value="<?php echo $resp->ID; ?>"
                                                name="deleteResp"
                                                class="button-secondary button-secondary-delete"
                                                onclick="setResponsableInfo('<?php echo $resp-> ID ?>', '<?php echo $resp->user_login ?>')"
                                                data-popup-target>
                                            Supprimer
                                        </button><?php
                                    } ?>
                                </form>
                            </td>
                        </tr><?php
                    }
                    ?>
                </tbody>
            </table> <?php
        } ?>

        <h2 class="title-label">Membre<?php if (count($this->group->getUsers()) >= 2)
                echo "s" ?> :</h2>

        <!-- Affichage d'un bouton "Ajouter membre" si l'utilisateur est responsable ou administrateur -->
        <?php
        if ($this->group->isUserResponsable(wp_get_current_user()) ||
            current_user_can('administrator')) { ?>
            <form action="" method="post"> <?php
                if (isset($_POST["addMember"])) { ?>
                    <div class="input-register-container input-register-3">
                        <input type="text" placeholder="Identifiant du membre à ajouter"
                               name="submitMember">
                        <button class="button-primary" type="submit">Ajouter</button>
                        <button class="button-secondary" type="submit">Annuler</button>
                    </div>
                <?php } else { ?>
                    <button type="submit" name="addMember"
                            class="button-primary menu-submit button-large">
                        <span class="dashicons dashicons-admin-users"></span>
                        Ajouter un nouveau membre
                    </button>
                <?php } ?>
            </form><?php
        }


        if(count($this->group->getUsers()) > 0){?>
            <!-- Affichage de la liste des membres du groupe -->
            <table class="widefat data-table striped">
                <thead>
                <tr>
                    <th class="row-title">Nom</th>
                    <th class="row-title">Prénom</th>
                    <th class="row-title">Identifiant</th>
                    <th class="row-title" colspan="3"></th>
                </tr>
                </thead>
                <tbody> <?php // Affichage de tous les utilisateurs du groupe
                foreach ($this->group->getUsers() as $user) {
                    $first_name = $user->first_name;
                    $last_name = $user->last_name; ?>
                    <tr class="<?php if($user->ID === get_current_user_id()) echo "row-hover" ?>">
                        <td><?php echo $first_name; ?></td>
                        <td><?php echo $last_name; ?></td>
                        <td><?php echo $user->user_login; ?></td>
                        <td class="hide-actions">
                            <form action="" method="post">
                                <button type="button" class="button-secondary"
                                        onclick="location.href='<?php echo home_url("/wp-admin/admin.php?page=mon_profil_ires&user_id=" . $user->ID .
                                            "&lock=" . Locker::STATE_UNLOCKABLE) ?>'">
                                    Voir
                                </button> <?php
                                if ($this->group->isUserResponsable(wp_get_current_user()) || current_user_can('administrator')) { ?>
                                    <button type="button" class="button-secondary"
                                            onclick="location.href='<?php echo home_url("/wp-admin/admin.php?page=mon_profil_ires&user_id=" . $user->ID .
                                                "&lock=" . Locker::STATE_UNLOCKED) ?>'">
                                        Modifier
                                    </button>
                                    <button type="button" value="<?php echo $user->ID ?>"
                                            class="button-secondary button-secondary-delete"
                                            onclick="setDeletionInfo('<?php echo $user->ID ?>', '<?php echo $user->user_login ?>')"
                                            data-popup-target>
                                        Retirer
                                    </button> <?php
                                } ?>
                            </form>
                        </td>
                    </tr><?php
                } ?>
                </tbody>
            </table> <?php
        }
    }
}