<?php

namespace irestoulouse\menus\groups;

use irestoulouse\elements\Group;
use irestoulouse\menus\IresMenu;

class GroupDetailsMenu extends IresMenu {
    
    private ?Group $group = null;

    public function __construct() {
        parent::__construct("Détails du groupe", // Page title when the menu is selected
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
            if (isset($_POST['submitMember']) && isset($_POST['nameMember']) && $_POST['nameMember'] != "") {
                $newMemberLogin = $_POST['nameMember'];

                $message = "Erreur, l'utilisateur $newMemberLogin n'a pas pu être ajouté car il est déjà présent dans le groupe.";
                $type_message = "error";
                if (($newMember = get_user_by("login", $_POST['nameMember'])) === false) {
                    $message = "Erreur, l'utilisateur $newMemberLogin n'a pas pu être ajouté car il n'existe pas.";
                } else if ($this->group->addUser($newMember)) {
                    $message = "L'utilisateur $newMemberLogin a été ajouté au groupe " . $this->group->getName() . ".";
                    $type_message = "updated";
                }
            }

            /*
             * Poste un message si un membre est retiré du groupe
             */
            if (isset($_POST['removeMember'])) {
                $this->group->removeUser(get_userdata($_POST['removeMember']));

                $message = "L'utilisateur a été supprimé du groupe.";
                $type_message = "updated";
            }

            /*
             * Poste un message si un responsable est supprimé
             */
            if (isset($_POST['deleteResp'])) {
                $deletedResponsable = get_userdata($_POST['deleteResp']);
                if ($deletedResponsable !== false && $this->group->removeResponsable($deletedResponsable)) {
                    $message = $deletedResponsable->user_login . " a été retiré des responsables du groupe.";
                    $type_message = "updated";
                }
            }

            /*
             * Poste un message si un nouveau responsable est tenté d'être créé
             */
            if (isset($_POST['submitResponsable'])) {
                $newResponsableLogin = $_POST['submitResponsable'];
                $newResponsable = get_user_by("login", $newResponsableLogin);

                $message = "Erreur, l'utilisateur $newResponsableLogin n'a pas pu être ajouté car il est déjà responsable.";
                $type_message = "error";
                if ($newResponsable === false) {
                    $message = "Erreur, l'utilisateur $newResponsableLogin n'a pas pu être ajouté car il n'existe pas.";
                } else if ($this->group->addResponsable($newResponsable)) {
                    $message = "L'utilisateur $newResponsableLogin a été ajouté en tant que responsable du groupe {$this->group->getName()}.";
                    $type_message = "updated";
                } else if (count($this->group->getResponsables()) >= 3) {
                    $message = "Erreur, il ne peut y avoir plus de 3 responsables.";
                    $type_message = "error";
                }
            }
        } catch (\Exception $e){
            $message = "Le groupe à modifier n'existe pas";
            $type_message = "error";
        }
        if($this->group === null){
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
        $responsables = $this->group->getResponsables();  ?>

        <!-- Bouton retour & titre de la page -->
        <div class="row">
            <div class="col-auto">
                <form action="<?php echo home_url("/wp-admin/admin.php?page=groupes_ires") ?>"
                      method="post">
                    <button type="submit" value="" name="back"
                            class="btn btn-outline-secondary rounded-circle"
                            style="width: 48px; height: 48px"><i
                                class="bi bi-arrow-left"></i></button>
                </form>
            </div>
            <div class="col-auto">
                <h1 class="wp-heading-inline"><b><?php echo $this->group->getName(); ?></b>
                </h1>
            </div>
        </div>
        <hr>

        <!-- Affichage des responsables -->
        <form action="" method="post">
            <div class="container">
                <div class="row">
                    <div class="col-3">
                        <label for="addGroup">Responsable<?php if (count($responsables) >= 2) {
                                echo "s";
                            } ?> du groupe :</label>
                    </div>
                    <div class="col-7">
                        <table class="table table-hover">
                            <?php
                            foreach ($responsables as $resp) {
                                $fullName = $resp->first_name . " " .
                                    $resp->last_name .
                                    " (" . $resp->user_login . ")"; ?>
                                <tr>
                                    <td class="col-9"><?php echo $fullName ?></td>
                                    <?php
                                    /**
                                     * Affichage des boutons modifier
                                     */
                                    if (isset($_POST['modifResponsable']) && current_user_can("administrator")) { ?>
                                        <td class="col-3">
                                            <form action="" method="post">
                                                <button type="submit"
                                                        value="<?php echo $resp->ID; ?>"
                                                        name="deleteResp"
                                                        class="btn btn-outline-danger btn-sm"
                                                        onclick="return confirm('Êtes vous sur de vouloir supprimer le responsable <?php echo $fullName ?> ?');">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </td>
                                        <?php
                                    } ?>
                                </tr> <!-- Fin de ligne pour chaque responsable -->
                                <?php
                            } // end foreach
                            /**
                             * Affichage de l'ajout d'un nouveau responsable si le nb de responsable < 3
                             */
                            if (isset($_POST['modifResponsable']) && count($responsables) < 3 &&
                                (current_user_can("administrator") ||
                                    $this->group->isUserResponsable(wp_get_current_user()))) { ?>
                                <form action="" method="post">
                                    <tr>
                                        <td class="col-3">
                                            <input type="text" class="col-5"
                                                   placeholder="Nouveau responsable"
                                                   name="submitResponsable"
                                        </td>
                                        <td class="col-3">
                                            <button class="btn btn-primary">Ajouter</button>
                                        </td>
                                    </tr>
                                </form>
                                <?php
                            } ?>
                        </table>
                    </div>
                    <?php /**
                     * Affichage du bouton "Modifier" pour changer les responsables
                     */
                    if (!isset($_POST['modifResponsable']) &&
                        current_user_can('administrator') &&
                        count($responsables) > 0) { ?>
                        <div class="col">
                            <button type="submit" value="" name="modifResponsable"
                                    class="btn btn-outline-secondary btn-sm">Modifier
                            </button>
                        </div>
                        <?php /**
                         * Affichage du bouton "Annuler" si modifier à été cliqué
                         */
                    } else if (isset($_POST['modifResponsable']) && current_user_can('administrator')) { ?>
                        <div class="col">
                            <button type="submit" value="" name=""
                                    class="btn btn-outline-secondary btn-sm">Annuler
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </form>


        <h1 class="wp-heading-inline">Membres du groupe :</h1><br>

        <!-- Affichage d'un bouton "Ajouter membre" si l'utilisateur est responsable ou administrateur -->
        <?php
        if ((current_user_can('responsable') &&
                $this->group->isUserResponsable(wp_get_current_user())) ||
            current_user_can('administrator')
        ) {
            if (isset($_POST['addMember'])) { ?>
                <form action="" method="post">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control"
                               placeholder="Identifiant"
                               name="nameMember">
                        <div class="input-group-append">
                            <button class="input-group-text btn-primary"
                                    name="submitMember">Ajouter
                            </button>
                        </div>
                    </div>
                </form>
                <?php
            } else { ?>
                <form action="" method="post">
                    <button type="submit" class="btn btn-primary btn-sm"
                            name="addMember">
                        Ajouter un membre
                    </button>
                </form>
                <?php
            }
        }
        ?>

        <!-- Affichage de la liste des membres du groupe -->
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th scope="row">Nom</th>
                <th scope="row">Prénom</th>
                <th scope="row">Identifiant</th>
                <th scope="row"></th>
                <th scope="row"></th>
            </tr>
            </thead>
            <tbody> <?php // Affichage de tous les utilisateurs du groupe
            foreach ($this->group->getUsers() as $user) {
                $first_name = $user->first_name;
                $last_name = $user->last_name; ?>
                <tr class="<?php if (get_current_user_id() === $user->ID) echo "table-primary"; ?>">
                    <td><?php echo $first_name; ?></td>
                    <td><?php echo $last_name; ?></td>
                    <td><?php echo $user->user_login; ?>
                    </td>
                    <td colspan="1"></td>
                    <td>
                        <div class="row">
                            <div class="col float-right">
                                <?php
                                if (current_user_can('administrator') ||
                                    $this->group->isUserResponsable(wp_get_current_user())) { ?>
                                    <button type="submit" class="btn btn-outline-secondary btn-sm"
                                            onclick="location.href = '<?php echo home_url("/wp-admin/admin.php?page=mon_profil_ires&user_id=" . $user->ID . "&lock=0") ?>'">
                                        Modifier
                                    </button>
                            </div>
                            <div class="col float-left">
                                <?php
                                if ($this->group->isUserResponsable(wp_get_current_user())
                                    || current_user_can('administrator')) {
                                    ?>
                                    <form action="" method="post">
                                        <button type="submit"
                                                id="removeMember"
                                                name="removeMember"
                                                value="<?php echo $user->ID ?>"
                                                class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('Êtes vous sur de vouloir retirer <?php echo $first_name . " " . $last_name ?> du groupe : <?php echo $this->group->getName(); ?> ?');">
                                            Retirer
                                        </button>
                                    </form>
                                    <?php
                                }
                                ?>
                                <?php } // end if?>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
        
    }
}