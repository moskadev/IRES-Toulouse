<?php

namespace irestoulouse\menus;

include_once("IresMenu.php");

wp_register_style('prefix_bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
wp_enqueue_style('prefix_bootstrap');
class ListeGroupeMenu extends IresMenu {

	public function __construct() {
		parent::__construct("Groupes", // Page title when the menu is selected
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
	function getContent(): void {
        /*
         * Supprime un groupe
         */
		if(isset($_POST['delete'])) {
            $message = "Le groupe ".$_POST['delete']." n'a pas pu être supprimé.";
            $type_message = "error";
            if (self::delete_group($_POST['delete'])) {
                $message = "Le groupe ".$_POST['delete']." a été supprimé.";
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
	        $this->create_table();
	        if ($this->insert_data_group(esc_attr($_POST['nameAddGroup']))) {
		        $type_message = "updated";
		        $message = "Le groupe ".$_POST['nameAddGroup']." a été créé.";
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
        if (isset($_POST['message']) && isset($_POST['type'])) {?>
            <!-- Affichage du message d'erreur ou de réussite en cas d'ajout d'un utilisateur au groupe -->
            <div id="message" class="<?php echo $_POST['type'];?> notice is-dismissible">
                <p><strong><?php echo stripslashes($_POST['message']); ?></strong></p>
            </div>
            <?php
        }

        /*
         * Formulaire pour ajouter un groupe
         *  - Nom du groupe
         *  - Bouton ajouter
         */
        if (current_user_can('administrator')) {
            ?>
            <div>
                <form action="" method="post">
                    <div class="container">
                        <div class="row">
                            <div class="col-2">
                                <label for="addGroup">Ajouter un groupe :</label>
                            </div>
                            <div class="col">
                                <input type="text" id="addGroup" class="to-fill" name="nameAddGroup" placeholder="Nom du groupe">
                            </div>
                            <div class="col">
                                <input type="submit" name="addGroup" value="Ajouter" class="btn btn-outline-primary">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <?php
        } // End if

        /*
         * Affichage des groupes auquel l'utilisateur appartient
         *
         * Possibilité de l'afficher si il y a plus de 9 groupes créé afin d'alléger la page :
         * && sizeof($groups) > 9
         */
        if (self::userIsInAGroup(get_currentuserinfo()->ID) ) {
            ?>
            <h1 class="wp-heading-inline">Vos Groupes</h1>
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
                foreach (self::getGroupWhereIsUser(get_currentuserinfo()->ID) as $group) {
                    self::printGroup($group);
                }
                ?>
                </tbody>
            </table>
            <?php
        }
        ?>


        <h1 class="wp-heading-inline">Lite des Groupes</h1>
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
                $groups = self::getGroups();
                foreach ($groups as $group) {
                    self::printGroup($group);
                } // end foreach ?>
            </tbody>
<?php      /*
            * Affichage d'un message si aucun groupe n'existe
            */
            if (sizeof($groups) === 0) { ?>
            <tr>
                <td colspan="4"><?php _e("No existing group") ?></td>
            </tr>
<?php       } // endif

            /*
             * Affichage du bas de page si il y a plus de 9 groupes
             */
            if (sizeof($groups) > 9) { ?>
            <tfoot>
                <tr>
                    <td>Nom</td>
                    <td>Responsable</td>
                    <td>Date de création</td>
                    <td></td>
                </tr>
            </tfoot>
<?php       } // endif ?>
        </table> <!-- Fin du tableau de l'affichage de tous les groupes -->
<?php
	} // end function getContent()

    /**
	 * Check if a group already exist in database
	 *
	 * @param $groupName string name of the group to create
	 * @return bool return true if the group exist, otherwise return false
	 */
	private function groupExist(string $groupName): bool {
        global $wpdb;
		return !empty($wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups WHERE name = %s", $groupName)));
	}

    /**
	 * @return array|object|null all the groups available
	 */
	private function getGroups() {
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups ORDER BY name"));
	}

    /**
     * Delete a groups if he exist in database
     *
     * @param $nameGroup
     * @return void if group deleted or not
     */
	private function delete_group ($nameGroup) : bool {
		global $wpdb;

		if (self::groupExist($nameGroup)) {
            $groupId = self::getGroupByName($nameGroup);
			$wpdb->delete(
                $wpdb->prefix.'groups',
				['name'=>$nameGroup],
				['%s']
			);
            $wpdb->get_results($wpdb->prepare("DELETE FROM {$wpdb->prefix}groups_users WHERE group_id = %d", $groupId[0]->id_group));
            return true;
		}
        return false;
    }

    /**
	 * Looking if groups and groups_user table have been created and if they not, create then
	 *
	 */
	function create_table() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'groups';
		$sql_create_group = "CREATE TABLE $table_name (
                id_group bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name char(30) NOT NULL,
                time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                creator_id bigint(20) UNSIGNED NOT NULL,
                FOREIGN KEY (creator_id) REFERENCES wp_users(ID),
                PRIMARY KEY  (id_group) 
            ) $charset_collate;";
		maybe_create_table($table_name, $sql_create_group );
		$table_name = $wpdb->prefix . 'groups_users';

		$sql_create_user_group = "CREATE TABLE $table_name (
                ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id bigint(20) UNSIGNED NOT NULL,
                group_id bigint(20) UNSIGNED NOT NULL,
                is_responsable int(1) NOT NULL DEFAULT '0',
                FOREIGN KEY (user_id) REFERENCES wp_users(ID),
                FOREIGN KEY (group_id) REFERENCES wp_groups(id_group),
                PRIMARY KEY (ID)
            ) $charset_collate;";
		maybe_create_table($table_name, $sql_create_user_group );
	}

    /**
	 * Create a groups if it doesn't already exist in database
	 *
	 * @param $nameGroup
	 *
	 * @return true | false true if the group is created, else false
	 */
	function insert_data_group($nameGroup): bool {
		global $wpdb;
		if (!(self::groupExist($nameGroup))) {
			$creator_id = get_current_user_id();
			$wpdb->insert(
                $wpdb->prefix.'groups',
				array(
					'name'=>$nameGroup,
					'creator_id'=>$creator_id
				),
				array( '%s','%d')
			);

            return true;
		}
        return false;
	}

    /**
     * Get all the user->id in a group
     * @param int $groupId id of the group where search the users
     * @return array|object|null all the user->id
     */
    private function getUserInGroup(int $groupId) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups_users WHERE group_id = %d", $groupId));
    }

    /**
     * Get all the information about a group by his name
     * @param string $groupName
     * @return array|object|null all the users in the group given in parameter
     */
    private function getGroupByName(string $groupName) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups WHERE name = %s", $groupName));
    }

    /**
     * Get the id of the user (if exist) in charge of the group given in parameter
	 * @param int $group_id the id of the group
	 * @return array|object|null id of the user(s) in charge of the group
	 */
	function getIdResponsable(int $group_id) {
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare("SELECT user_id FROM {$wpdb->prefix}groups_users WHERE group_id = %d AND is_responsable = 1", $group_id));
	}

    /**
     * Check if a user is in charge of the group
     * @param $user_id int the id of the user
     * @param $group_id int the id of the group
     * @return bool true if the user is in charge of the group, else false
     */
    private function userIsResponsableGroup(int $user_id, int $group_id ): bool {
        $list_user = [];
        $users = $this->getIdResponsable($group_id);
        foreach ($users as $user) {
            array_push($list_user, $user->user_id);
        }
        if (in_array($user_id, $list_user))
            return true;
        return false;
    }

    /**
     * Check if the user is in a group
     * @param $userId int the id of the user
     * @return bool true if the user is in a group, else false
     */
    private function userIsInAGroup(int $userId) : bool {
        global $wpdb;
        foreach (self::getGroups() as $group) {
            $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups_users WHERE group_id = %d AND user_id = %d", $group->id_group, $userId));
            if (!empty($result)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $userId int the id of the user
     * @return array|object|null all the group(s) of a user
     */
    private function getGroupWhereIsUser(int $userId) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups JOIN {$wpdb->prefix}groups_users ON group_id = id_group WHERE user_id = %d", $userId));
    }

    /**
     * Print the row of a table for the group given in parameter
     * @param $group object the group to print
     */
    private function printGroup(object $group) {
        $user = wp_get_current_user();
        $users = self::getUserInGroup($group->id_group);
        $list_user = [];
        foreach ($users as $usr) {
            array_push($list_user, $usr->user_id);
        }

        $results = $this->getIdResponsable($group->id_group);
        $id_resp = [];
        foreach ($results as $result)
            array_push($id_resp, (int) $result->user_id);
        ?>
        <tr class="<?php if (in_array(get_current_user_id(), $list_user)) echo "table-primary"; ?>">
            <!-- Name of the group -->
            <th scope="row" class="text-primary">
                <a class="text-decoration-none" href="<?php echo get_site_url() ?>/wp-admin/admin.php?page=details&group=<?php echo $group->name ?>">
                    <?php echo $group->name ?>
                </a>
            </th>
            <!-- Name of the user in charge of the group -->
            <td class="">
                <?php
                $i = 0;
                foreach ($id_resp as $resp) {
                    $i++;
                    echo get_user_meta($resp, "first_name")[0]." ".get_user_meta($resp, "last_name")[0];
                    if (count($id_resp) > 1 && $i < count($id_resp))
                        echo ", ";
                }
                ?>
            </td>
            <!-- Date -->
            <td>
                <?php echo $group->time_created ?>
            </td>
            <td>
                <?php
                if (current_user_can('administrator') || (current_user_can('responsable') && self::userIsResponsableGroup($user->ID, $group->id_group))) {
                    ?>
                    <form action="" method="post">
                        <button type="button"
                                id="modify"
                                name="modify"
                                value="<?php echo $group->name ?>"
                                class="btn btn-outline-secondary btn-sm"
                                onclick="location.href='<?php echo get_site_url() ?>/wp-admin/admin.php?page=details&group=<?php echo $group->name ?>'">
                            Modifier
                        </button>
                        <?php
                        if (current_user_can('administrator')) {
                            ?>
                            <button type="submit"
                                    id="delete"
                                    name="delete"
                                    value="<?php echo $group->name ?>"
                                    class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Êtes vous sur de vouloir supprimer le groupe : <?php echo $group->name; ?> ?');">
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