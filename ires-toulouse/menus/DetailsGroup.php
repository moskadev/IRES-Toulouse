<?php

namespace irestoulouse\menus;

wp_register_style('prefix_bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
wp_register_style('icon_bootstrap', '//cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css');
wp_enqueue_style('prefix_bootstrap');
wp_enqueue_style('icon_bootstrap');
class DetailsGroup extends IresMenu {

	public function __construct() {
		parent::__construct("Détails", // Page title when the menu is selected
			"details", // Name of the menu
			0, // Menu access security level
			"dashicons-businesswoman", // Menu icon
			4 // Page position in the list
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getContent(): void {
        $group = self::getParam("group");
		$id_group = $this->getIdGroup($group);
		$id_group = $id_group[0]['id_group'];

        $users = $this->getIdUserGroup($id_group);

        $results = $this->getIdResponsable($id_group);
        $id_resp = [];
        foreach ($results as $result)
	        array_push($id_resp, (int) $result['user_id']);

        //var_dump($id_resp);

		if (isset($_POST['submitMember']) && isset($_POST['nameMember']) && $_POST['nameMember'] != "") {
			$user_login = $_POST['nameMember'];
			$user_id = $this->getIdUser($user_login);
			$user_id = $user_id[0]['ID'];

			$message = "Erreur, l'identifiant ".$user_login." n'a pas pu être ajouté car il est déjà présent dans le groupe.";
			$type_message = "error";
			if (!get_user_by( 'id', $user_id )) {
				$message = "Erreur, l'identifiant ".$user_login." n'a pas pu être ajouté car il n'existe pas.";
			} elseif ($this->addUserGroup($user_id, $id_group) && get_user_by( 'id', $user_id )) {
				$message = "L'identifiant ".$user_login." a été ajouté au groupe ".$group.".";
				$type_message = "updated";
			}
			//echo "<meta http-equiv='refresh' content='0'>";
		}

        if (isset($_POST['remove'])) {
            self::deleteUserGroup($_POST['remove'], $id_group);
	        echo "<meta http-equiv='refresh' content='0'>";
        }

		if ( isset( $_POST['deleteResp'] ) ) {
			if (self::deleteResponsableGroup( $_POST['deleteResp'], $id_group )) {
                $message = $_POST['deleteResp']." a été retiré des responsables du groupe.";
                $type_message = "updated";
            }
		}

        if (isset ($_POST['submitResponsable'])) {
            $user_login = $_POST['nameResponsable'];
	        $user = get_user_by( 'login', $user_login );

	        $message = "Erreur, l'identifiant ".$user_login." n'a pas pu être ajouté car il est déjà responsable.";
	        $type_message = "error";
            if (sizeof($id_resp) >= 2) {
                $message = "Erreur, il ne peut y avoir plus de 2 responsables.";
                $type_message = "error";
            } elseif (!get_user_by( 'login', $user_login )) {
		        $message = "Erreur, l'identifiant ".$user_login." n'a pas pu être ajouté car il n'existe pas.";
	        } elseif ($this->addResponsableGroup($user->ID, $id_group)) {
		        $message = "L'identifiant ".$user_login." a été ajouté en tant que responsable du groupe ".$group.".";
		        $type_message = "updated";
	        }
        }

        if ((isset($_POST['submitMember']) && isset($_POST['nameMember']) && $_POST['nameMember'] != "") || isset($_POST['deleteResp']) || isset($_POST['submitResponsable']) ) {?>
            <!-- Affichage du message d'erreur ou de réussite en cas d'ajout d'un utilisateur au groupe -->
            <div id="message" class="<?php echo "$type_message";?> notice is-dismissible">
                <p><strong><?php echo "$message"; ?></strong></p>
            </div>
        <?php
        }?>

        <div class="row">
            <div class="col-auto">
                <button onclick="location.href='<?php echo get_site_url() ?>/wp-admin/admin.php?page=groupes';" type="button" value="" name="back" class="btn btn-outline-secondary rounded-circle" style="width: 48px; height: 48px"><i class="bi bi-arrow-left"></i></button>
            </div>
            <div class="col-auto">
                <h1 class="wp-heading-inline">Détails du groupe : <b><?php echo $group; ?></b></h1>
            </div>
        </div>
        <hr>

		<form action="" method="post">
            <div class="container">
                <div class="row">
                    <div class="col-3">
                        <label for="addGroup">Responsable<?php if (sizeof($id_resp) >= 2) echo "s"; ?> du groupe :</label>
                    </div>
                        <div class="col">
                            <table class="table table-hover">
                                <?php
                                foreach ($id_resp as $resp) { ?>
                                    <tr>
                                        <td>
                                            <?php
                                            $first_name = self::getUser($resp, "first_name");
                                            $last_name = self::getUser($resp, "last_name");
                                            echo $first_name[0]['meta_value']." ".$last_name[0]['meta_value'];
                                            ?>
                                        </td>
                                        <?php
                                        /**
                                         * Affichage des boutons modifier
                                         */
                                        if (isset($_POST['modifResponsable'])) { ?>
                                            <form action="" method="post">
                                                <td>
                                                    <button type="submit" value="<?php echo $resp; ?>" name="deleteResp" class="btn btn-outline-danger btn-sm">Supprimer</button>
                                                </td>
                                                <?php if (sizeof($id_resp) < 2) { ?>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" placeholder="Identifiant" name="nameResponsable">
                                                    <div class="input-group-append">
                                                        <button class="input-group-text btn-primary" name="submitResponsable">Ajouter</button>
                                                    </div>
                                                </div>
                                                <?php } ?>
                                            </form>
                                <?php   } ?>
                                    </tr>
                        <?php   }
                                if ((sizeof($id_resp) === 0) && isset($_POST['modifResponsable'])) { ?>
                                    <form action="" method="post">
                                        <div class="col-xs-3">
                                            <input type="text" name="nameResponsable" placeholder="Identifiant">
                                        </div>
                                        <div class="col-xs-1">
                                            <button type="submit" name="submitResponsable" class="btn btn-primary">Ajouter</button>
                                        </div>
                                    </form>
	                            <?php } ?>
                            </table>
                        </div>
            <?php       /**
	                     * Affichage du bouton "Modifier" pour changer les responsables
	                     */
                        if (!isset($_POST['modifResponsable']) && current_user_can('administrator')) { ?>
                            <div class="col">
                                <button type="submit" value="" name="modifResponsable" class="btn btn-outline-secondary btn-sm">Modifier</button>
                            </div>
            <?php       }  elseif (isset($_POST['modifResponsable']) && current_user_can('administrator')) {?>
                            <div class="col">
                                <button type="submit" value="" name="" class="btn btn-outline-secondary btn-sm">Annuler</button>
                            </div>
            <?php       }?>


                </div>
            </div>
        </form>



        <h1 class="wp-heading-inline">Membres du groupe :</h1><br>
        <?php
        if (current_user_can('responsable') || current_user_can('administrator')) {
            if (isset($_POST['addMember'])) {?>
                <form action="" method="post">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Identifiant" name="nameMember">
                        <div class="input-group-append">
                            <button class="input-group-text btn-primary" name="submitMember">Ajouter</button>
                        </div>
                    </div>
                </form>
            <?php
            } else { ?>
                <form action="" method="post">
                    <button type="submit" class="btn btn-primary btn-sm" name="addMember">Ajouter un membre</button>
                </form>
        <?php
            }
        }
        ?>
        <table class="table table-striped table-hover">
            <thead>
                <th scope="row">Nom</th>
                <th scope="row">Prénom</th>
                <th scope="row"></th>
                <th scope="row"></th>
                <th scope="row"></th>
            </thead>
            <tbody>
<?php	// Affichage de tous les utilisateurs du groupe
                foreach ( $users as $user ) {
                    //var_dump($user);
                    $first_name = self::getUser($user['user_id'], "first_name"); $first_name = $first_name[0]['meta_value'];
                    $last_name = self::getUser($user['user_id'], "last_name"); $last_name = $last_name[0]['meta_value'];?>
                    <tr class="<?php if (get_current_user_id() == $user['user_id']) echo "table-primary"; ?>">
                        <td class="">
                            <?php echo $first_name; ?>
                        </td>
                        <td>
                            <?php echo $last_name; ?>
                        </td>
                        <td colspan="2"></td>
                        <td>
                            <?php
                            if (current_user_can('administrator') ||
                                (current_user_can('responsable')  && self::userIsResponsableGroup(get_current_user_id(), $id_group))) { ?>
                            <form action="" method="post">
                                <button type="submit"
                                        class="btn btn-outline-secondary btn-sm"
                                        onclick="">
                                    Modifier
                                </button>
                                <?php
                                if (!(get_current_user_id() == $user['user_id'])) {
                                    ?>
                                    <button type="submit"
                                            id="remove"
                                            name="remove"
                                            value="<?php echo $user['user_id'] ?>"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Êtes vous sur de vouloir retirer <?php echo $first_name." ".$last_name ?> du groupe : <?php echo $group; ?> ?');">
                                        <?php echo __('Remove') ?>
                                    </button>
                                    <?php
                                }
                                ?>
                            </form>
                            <?php } // end if?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
	}

	public static function getParam($name) {
		if (isset($_GET[$name])) return $_GET[$name];
		if (isset($_POST[$name])) return $_POST[$name];
		return null;
	}

	/**
	 * @param $group_name string name of the group
	 *
	 * @return array|object|null
	 */
	private function getIdGroup( string $group_name) {
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare("SELECT id_group FROM {$wpdb->prefix}groups WHERE name = %s", $group_name),
			ARRAY_A);
	}

    /**
     * @return array|null with all the groups where a member is responsable
     */
    private function getGroupsWhereIdIsResponsable($user_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT id_group FROM {$wpdb->prefix}groups JOIN {$wpdb->prefix}groups_users ON id_group = group_id WHERE is_responsable = 1 AND user_id = %d", $user_id), ARRAY_A);
    }

	/**
	 * @param int $group_id
	 *
	 * @return array|object|null l'id du ou des responsables du groupe
	 */
	function getIdResponsable(int $group_id) {
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->prefix}groups_users WHERE group_id = %d AND is_responsable = 1", $group_id),
			ARRAY_A);
	}

	/**
	* @param $group_id integer id of the group
	*
	* @return array|object|null all the users in the group given in parameter
	*/
	private function getIdUserGroup( int $group_id) {
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups_users WHERE group_id = %d", $group_id),
			ARRAY_A);
	}

    private function getIdUser(string $user_login) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}users WHERE user_login = %s", $user_login), ARRAY_A);
    }

	/**
	 * @param $userId
	 * @param $metaKey
	 *
	 * @return array|object|null
	 */
	private function getUser($userId, $metaKey) {
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}usermeta WHERE user_id = %d AND meta_key = %s", $userId, $metaKey),
			ARRAY_A);
	}

	/**
     * Vérifie que l'utilisateur n'est pas déjà dans le groupe
	 * @param $user_id
	 * @param $group_id
	 *
	 * @return bool
	 */
    private function userIsInGroup($user_id, $group_id) : bool {
        $list_user = [];
        $users = $this->getIdUserGroup($group_id);
        foreach ($users as $user) {
            array_push($list_user, $user['user_id']);
        }
        if (in_array($user_id, $list_user))
            return true;
        return false;
    }

	/**
	 * @param $user_id
	 * @param $group_id
	 *
	 * @return bool
	 */
	private function userIsResponsableGroup( $user_id, $group_id ): bool {
        $list_user = [];
        $users = $this->getIdResponsable($group_id);
        foreach ($users as $user) {
            array_push($list_user, $user['user_id']);
        }
        if (in_array($user_id, $list_user))
            return true;
        return false;
	}

	/**
     * Ajoute un responsable s'il est déjà dans le groupe
     * Ajoute l'utilisateur dans le groupe en tant que responsable s'il n'est pas déjà présent
	 * @param $user_id
	 * @param $group_id
	 *
	 * @return bool
	 */
	private function addResponsableGroup($user_id, $group_id): bool {
		global $wpdb;
        if (!self::userIsInGroup($user_id, $group_id)) {
		    $wpdb->get_results($wpdb->prepare("INSERT INTO {$wpdb->prefix}groups_users (user_id, group_id, is_responsable) VALUES (%d, %d, '1')", $user_id, $group_id));
            $user = get_user_by("id", $user_id);
            $user->set_role("responsable");
            return true;
	    } elseif (!self::userIsResponsableGroup($user_id, $group_id)) {
	        $wpdb->get_results($wpdb->prepare("UPDATE {$wpdb->prefix}groups_users SET is_responsable = '1' WHERE user_id = %d AND group_id = %d", $user_id, $group_id));
            $user = get_user_by("id", $user_id);
            $user->set_role("responsable");
            return true;
        }
        return false;
	}

	/**
	 * @param $user_id
	 * @param $group_id
	 *
	 * @return bool
	 */
    private function deleteResponsableGroup($user_id, $group_id): bool {
	    global $wpdb;
	    if (self::userIsInGroup($user_id, $group_id) && self::userIsResponsableGroup($user_id, $group_id)) {
            $user = get_user_by("id", $user_id);
            if (sizeof(self::getGroupsWhereIdIsResponsable($user_id)) < 2) {
                $user->set_role("subscriber");
            }
            $wpdb->get_results($wpdb->prepare("UPDATE {$wpdb->prefix}groups_users SET is_responsable = '0' WHERE user_id = %d AND group_id = %d", $user_id, $group_id));

            return true;
	    }
	    return false;
    }

	/**
	 * Ajoute l'utilisateur s'il n'est pas déjà présent et s'il existe
	 * @param $user_id
	 * @param $group_id
	 *
	 * @return bool
	 */
	private function addUserGroup($user_id, $group_id): bool {
		global $wpdb;
		if (!self::userIsInGroup($user_id, $group_id)) {
			$wpdb->get_results($wpdb->prepare("INSERT INTO {$wpdb->prefix}groups_users (user_id, group_id, is_responsable) VALUES (%d, %d, '0')", $user_id, $group_id));
			return true;
		}

		return false;
	}

	/**
     * Supprime l'utilisateur du groupe fournit en paramètre
	 * @param $userId
	 * @param $groupId
	 *
	 * @return array|object|null
	 */
	private function deleteUserGroup($userId, $groupId) {
		global $wpdb;
        if ($this->userIsResponsableGroup($userId, $groupId)) {
            self::deleteResponsableGroup($userId, $groupId);
        }
		return $wpdb->get_results($wpdb->prepare("DELETE FROM {$wpdb->prefix}groups_users WHERE user_id = %d AND group_id = %d", $userId, $groupId));
	}
}