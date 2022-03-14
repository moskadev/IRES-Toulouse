<?php

namespace irestoulouse\menus\groups;

use Exception;
use irestoulouse\group\Group;
use irestoulouse\group\GroupFactory;
use irestoulouse\menus\Menu;
use irestoulouse\menus\MenuFactory;
use irestoulouse\menus\MenuIds;
use irestoulouse\menus\users\UserProfileMenu;
use irestoulouse\utils\Identifier;
use irestoulouse\utils\Locker;
use WP_User;

/**
 * All details about the group that has been selected by
 * the user. It only laods if it exists
 *
 * @version 2.0
 */
class GroupDetailsMenu extends Menu {

    /** @var Group|null */
    private ?Group $group = null;
    /** @var bool */
    private bool $addingResponsable;
    /** @var bool */
    private bool $addingMember;

    /**
     * Initializing everything related to its menu
     * We are getting the name from its identifier from the URL
     */
    public function __construct() {
        $groupName = "inconnu";
        if (($group = GroupFactory::fromId($_GET["group"] ?? - 1)) !== null) {
            $groupName = $group->getName();
        }
        parent::__construct(MenuIds::GROUP_DETAILS_MENU, "Détails du groupe",
            "Détails du groupe <b>" . $groupName . "</b>", 0,
            "dashicons-businesswoman", 4
        );
    }

    /**
     * Get the page's url, params can be given
     * @param int $groupId the group's id
     *
     * @return string the full page's URL
     */
    public function getPageUrl(int $groupId = -1) : string {
        return parent::createPageUrl(["group" => $groupId]);
    }

    /**
     * We are checking if the group in the URL exists. If so, we
     * check if a member has been added or removed, same thing for
     * responsables
     *
     * @param array $params $_GET and $_POST combined
     */
    public function analyzeParams(array $params) : void {
        $this->addingResponsable = isset($params["newResponsable"]);
        $this->addingMember = isset($params["addMember"]);

        if (($this->group = GroupFactory::fromId($params["group"])) !== null) {
            $message = $type_message = "";
            /*
             * Post a message if a member is added
             */
            if (strlen($newMemberLogin = trim($params['submitMember'] ?? "")) > 0) {
                $message = "Erreur, l'utilisateur $newMemberLogin n'a pas pu être ajouté car il est déjà présent dans le groupe.";
                $type_message = "error";

                if (($newMember = get_user_by("login", Identifier::extractLogin($newMemberLogin))) === false) {
                    $message = "Erreur, l'utilisateur $newMemberLogin n'a pas pu être ajouté car il n'existe pas.";
                } else if ($this->group->addUser($newMember)) {
                    $message = "L'utilisateur $newMemberLogin a été ajouté au groupe " . $this->group->getName() . ".";
                    $type_message = "updated";
                }
            }

            /*
             * Post a message if a member is removed from the group
             */
            if (strlen($remove = trim($params['removeMember'] ?? "")) > 0) {
                $message = "Une erreur s'est produite lors de la suppression d'un membre.";
                $type_message = "error";
                try {
                    if ($this->group->removeUser($user = get_userdata($remove))) {
                        $message = "L'utilisateur {$user->user_login} a été supprimé du groupe.";
                        $type_message = "updated";
                    }
                } catch (Exception $e) {
                    // message already set
                }
            }

            /*
             * Post a message if a responsable is deleted
             */
            if (strlen($delete = trim($params['deleteResp'] ?? "")) > 0) {
                $message = "Une erreur s'est produite lors de la suppression d'un responsable.";
                $type_message = "error";
                try {
                    $deletedResponsable = get_userdata($delete);
                    if ($deletedResponsable !== false && $this->group->removeResponsable($deletedResponsable)) {
                        $message = $deletedResponsable->user_login . " a été retiré des responsables du groupe.";
                        $type_message = "updated";
                    }
                } catch (Exception $e) {
                    // message already set
                }
            }

            /*
             * Post a message if a new responsable is attempted to be created
             */
            if (strlen($newResponsableLogin = trim($params['submitResponsable'] ?? "")) > 0) {
                $newResponsable = get_user_by("login", Identifier::extractLogin($newResponsableLogin));

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
        } else {
            $message = "Le groupe à modifier n'existe pas";
            $type_message = "error";
        }
        $this->showNoticeMessage($type_message, $message);
    }

    /**
     * If the group hasn't been found, the page won't
     * be loaded
     * If the group exists, an administrator has the possibility to :
     * - add or remove a member from the group
     * - add or remove a responsable from the group
     * A responsable can only remove/add a member
     * Everyone can check each users' profile
     */
    public function showContent() : void {
        if ($this->group === null) {
            return;
        }
        $responsables = $this->group->getResponsables();
        /** @var UserProfileMenu $menuProfile */
        $menuProfile = MenuFactory::fromId(MenuIds::USER_PROFILE_MENU); ?>

        <!-- Confirmation popup for deletion of a user from the group -->
        <div class="popup">
            <div class="popup-element">
                <div class="popup-header">
                    <p class="title popup-title"></p>
                    <button data-close-button class="close-button">&times;</button>
                </div>
                <div class="popup-body">
                    <p id="text">Êtes-vous sûr de vouloir retirer cet utilisateur de ce
                        groupe ?</p>
                    <form action="" method="post">
                        <input type="hidden" id="removeMember" name="removeMember"
                               value="">
                        <input type="hidden" id="deleteResp" name="deleteResp" value="">
                        <button class="confirm-delete button-primary button-delete"
                                type="submit">Confirmer
                        </button>
                        <button class="button-secondary" type="button" data-close-button>
                            Annuler
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Back button & page title -->
        <form action="<?php echo MenuFactory::fromId(MenuIds::GROUP_LIST_MENU)->getPageUrl() ?>"
              method="post">
            <button class="button-secondary">< Retour à la page des groupes</button>
        </form>

        <h3>Légende : </h3>
        <p>
            <mark class="underline-blue">Le surlignage bleu permet de repérer votre
                profil
            </mark>
            <br>
            <mark class="underline-orange">Le surlignage orange permet de repérer les
                profils des responsables
            </mark>
        </p>

        <h2 class="title-label">Responsable<?php if (count($responsables) >= 2)
                echo "s" ?> :</h2>

        <!-- Button for adding a new responsable -->
        <?php
        $this->addingResponsableForm($responsables);
        $this->printResponsables($menuProfile, $responsables); ?>

        <h2 class="title-label">Membre<?php if (count($this->group->getUsers()) >= 2)
                echo "s" ?> :</h2>

        <!-- Display a "Add member" button if the user is a responsable or administrator -->
        <?php
        $this->addingMemberForm();
        $this->printMembers($menuProfile);

    }

    /**
     * Creating a new button to indicate that we want to add
     * a new responsable. If it has been clicked, you can type
     * the user's full name, add it or cancel your action.
     *
     * @param WP_User[] $responsables all responsables
     */
    private function addingResponsableForm(array $responsables) : void{
        if (count($responsables) < Group::MAX_RESPONSABLES && current_user_can("administrator")) { ?>
            <form action="" method="post"> <?php
            if ($this->addingResponsable) { ?>
                <div class="input-register-container input-register-3">

                    <input type="text" class="search-field"
                           placeholder="Identifiant du responsable à ajouter"
                           name="submitResponsable">
                    <button class="button-primary" type="submit">Ajouter</button>
                    <button class="button-secondary" type="button" onclick="reloadPage()">
                        Annuler
                    </button>
                </div>
                <?php
            } else { ?>
                <button type="submit" name="newResponsable"
                        class="button-primary menu-submit button-large">
                    <span class="dashicons dashicons-businesswoman"></span>
                    Ajouter un nouveau responsable
                </button>
            <?php } ?>
            </form><?php
        }
    }

    /**
     * Creating a list with all responsables
     *
     * @param UserProfileMenu $profileMenu the menu to check user's profiles
     * @param array $responsables all responsables
     */
    public function printResponsables(UserProfileMenu $profileMenu, array $responsables) : void {
        if (count($responsables) > 0) { ?>
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
                    <tr class="<?php echo $resp->ID === get_current_user_id() ? "row-hover" : "is-resp" ?>">
                        <td><?php echo $last_name ?></td>
                        <td><?php echo $first_name ?></td>
                        <td><?php echo $resp->user_login ?></td>
                        <td class="hide-actions">
                            <form action="" method="post">
                                <button type="button" class="button-secondary"
                                        onclick="location.href='<?php echo $profileMenu->getPageUrl($resp->ID, Locker::STATE_UNLOCKABLE) ?>'">
                                    Voir
                                </button> <?php
                                if (current_user_can("administrator")) { ?>
                                    <button type="button" class="button-secondary"
                                            onclick="location.href='<?php echo $profileMenu->getPageUrl($resp->ID, Locker::STATE_UNLOCKED) ?>'">
                                        Modifier
                                    </button>
                                <button type="button" value="<?php echo $resp->ID; ?>"
                                        name="deleteResp"
                                        class="button-secondary button-secondary-delete"
                                        onclick="setResponsableInfo('<?php echo $resp->ID ?>', '<?php echo $resp->user_login ?>')"
                                        data-popup-target>
                                        Supprimer
                                    </button><?php
                                } ?>
                            </form>
                        </td>
                    </tr> <?php
                }
                ?>
                </tbody>
            </table> <?php
        } else { ?>
            <p>Aucun responsable n'est dans ce groupe</p> <?php
        }
    }

    /**
     * Creating a new button to indicate that we want to add
     * a new member. If it has been clicked, you can type
     * the user's full name, add it or cancel your action.
     */
    private function addingMemberForm() : void {
        if ($this->group->isUserResponsable(wp_get_current_user()) ||
            current_user_can('administrator')) { ?>
            <form action="" method="post"> <?php
            if ($this->addingMember) { ?>
                <div class="input-register-container input-register-3">
                    <input type="text" class="search-field"
                           placeholder="Identifiant du membre à ajouter"
                           name="submitMember">
                    <button class="button-primary" type="submit">Ajouter</button>
                    <button class="button-secondary" type="button" onclick="reloadPage()">
                        Annuler
                    </button>
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
    }

    /**
     * Creating a list with all members
     *
     * @param UserProfileMenu $profileMenu the menu to check user's profiles
     */
    private function printMembers(UserProfileMenu $profileMenu) : void {
        if (count($this->group->getUsers()) > 0) {
            ?>
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
                <tr class="<?php
                if ($user->ID === get_current_user_id()) {
                    echo "row-hover ";
                } else if ($this->group->isUserResponsable($user))
                    echo "is-resp" ?>">
                    <td><?php echo $last_name; ?></td>
                    <td><?php echo $first_name ?></td>
                    <td><?php echo $user->user_login; ?></td>
                    <td class="hide-actions">
                        <form action="" method="post">
                            <button type="button" class="button-secondary"
                                    onclick="location.href='<?php echo $profileMenu->getPageUrl($user->ID, Locker::STATE_UNLOCKABLE) ?>'">
                                Voir
                            </button> <?php
                            if ($this->group->isUserResponsable(wp_get_current_user()) || current_user_can('administrator')) { ?>
                                <button type="button" class="button-secondary"
                                        onclick="location.href='<?php echo $profileMenu->getPageUrl($user->ID, Locker::STATE_UNLOCKED) ?>'">
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
        } else { ?>
            <p>Aucun membre n'est dans ce groupe</p> <?php
        }
    }
}