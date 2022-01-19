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
        $user = wp_get_current_user();
		$groups = self::getGroups();

		if(isset($_POST['delete'])) {
			$this->delete_group($_POST['delete']);
			echo "<meta http-equiv='refresh' content='0'>";
		}

		if (isset($_POST['deleteMember'])) {
			$str = explode(".", $_POST['deleteMember']);
			$this->deleteUserGroup($str[0], $str[1]);
		}

        if (isset($_POST['addGroup']) && isset($_POST['nameAddGroup'])) {
	        $message = "Impossible de créer le groupe.";
            $type_message = "error";
	        $this->create_table();
	        if ($this->insert_data_group(esc_attr($_POST['nameAddGroup']))) {
		        $type_message = "updated";
		        $message = "Le groupe ".$_POST['nameAddGroup']." a été créé.";
            }

            // Rafraichir la page pour afficher le nouveau groupe
	        // echo "<meta http-equiv='refresh' content='0'>";

            ?>
            <!-- Affichage du message d'erreur ou de réussite en cas d'ajout d'un groupe -->
            <div id="message" class="<?php echo "$type_message";?> notice is-dismissible">
                <p><strong><?php echo "$message"; ?></strong></p>
            </div>
            <?php
        }

		?>
        <div>
            <h1 class="wp-heading-inline">Groupes</h1>

            <?php
            /**
             * Ajout d'un groupe si l'utilisateur est administrateur
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
                                <input type="text" class="to-fill" name="nameAddGroup" placeholder="Nom du groupe">
                            </div>
                            <div class="col">
                                <input type="submit" name="addGroup" value="Ajouter" class="btn btn-outline-primary">
                            </div>
                        </div>
                    </div>
                </form>

                <?php
            } // End if

            ?>
        </div>


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
                foreach ($groups as $group) {
	                $users = self::getIdUserGroup($group['id_group']);
                    $list_user = [];
                    foreach ($users as $usr) {
                        array_push($list_user, $usr['user_id']);
                    }

	                $results = $this->getIdResponsable($group['id_group']);
	                $id_resp = [];
	                foreach ($results as $result)
		                array_push($id_resp, (int) $result['user_id']);
                    ?>
                <tr class="<?php if (in_array(get_current_user_id(), $list_user)) echo "table-primary"; ?>">
                    <!-- Name of the group -->
                    <th scope="row" class="text-primary">
                        <a class="text-decoration-none" href="<?php echo get_site_url() ?>/wp-admin/admin.php?page=details&group=<?php echo $group['name'] ?>">
	                        <?php echo $group['name'] ?>
                        </a>
                    </th>
                    <!-- Name of the responsible -->
                    <td class="">
	                    <?php
	                    foreach ($id_resp as $resp) {
		                    $first_name = self::getUser($resp, "first_name");
		                    $last_name = self::getUser($resp, "last_name");
		                    echo $first_name[0]['meta_value']." ".$last_name[0]['meta_value'];
                            if (sizeof($id_resp) > 1)
                                echo ", ";
	                    }
	                    ?>
                    </td>
                    <!-- Date -->
                    <td>
                        <?php echo $group['time_created'] ?>
                    </td>
                    <td>
                        <?php
                        if (current_user_can('administrator') || (current_user_can('responsable') && self::userIsResponsableGroup($user->ID, $group['id_group']))) {
                        ?>
                            <form action="" method="post">
                                <button type="button"
                                        id="modify"
                                        name="modify"
                                        value="<?php echo $group['name'] ?>"
                                        class="btn btn-outline-secondary btn-sm"
                                        onclick="location.href='<?php echo get_site_url() ?>/wp-admin/admin.php?page=details&group=<?php echo $group['name'] ?>'">
                                    Modifier
                                </button>
                                <?php
                                if (current_user_can('administrator')) {
                                    ?>
                                    <button type="submit"
                                            id="delete"
                                            name="delete"
                                            value="<?php echo $group['name'] ?>"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Êtes vous sur de vouloir supprimer le groupe : <?php echo $group['name']; ?> ?');">
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
            } // end foreach
?>
            </tbody>
<?php       if (sizeof($groups) === 0) {?>
            <tr>
                <td colspan="4"><?php _e("No existing group") ?></td>
            </tr>
<?php       } // endif
            if (sizeof($groups) > 9) { ?>
            <tfoot>
                <tr>
                    <td>Nom</td>
                    <td>Responsable</th>
                    <td>Date de création</th>
                    <td></td>
                </tr>
            </tfoot>
<?php       } // endif ?>
        </table>
<?php
	} // end function

	/**
	 * Check if a group already exist in database
	 *
	 * @param $groupName name of the group to create
	 * @return bool return true if the group exist, otherwise return false
	 */
	function groupExist($wpdb, $table_name, $groupName): bool
	{
		$sql = "SELECT * FROM $table_name WHERE name='$groupName'";
		return count($wpdb->get_results($sql)) == 0;
	}

	/**
	 * @return array|object|null all the groups available
	 */
	public static function getGroups() {
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups ORDER BY name"),
			ARRAY_A);
	}

	/**
	 * Delete a groups if he exist in database
	 *
     * TODO ajouter la suppression de tous les utilisateurs de ce groupe
     *
	 * @param string $inputId
	 * @return true|false if group deleted or not
	 */
	function delete_group ($nameGroup) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'groups';

		// If the name group is present 0 times in the database$
		if (!($this->groupExist($wpdb, $table_name, $nameGroup))) {
			$wpdb->delete(
				$table_name,
				['name'=>$nameGroup],
				['%s']
			);
			return true;
		}
        return false;
	}

    private function deleteUserGroup($userId, $groupId) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("DELETE FROM {$wpdb->prefix}groups_users WHERE user_id = %d AND group_id = %d", $userId, $groupId));
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
		$table_name = $wpdb->prefix . 'groups';

		// If the name group is present 0 times in the database$
		if ($this->groupExist($wpdb, $table_name, $nameGroup)) {
			$creator_id = get_current_user_id();
			$wpdb->insert(
				$table_name,
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
	 * @param $group_id integer id of the group
	 *
	 * @return array|object|null all the users in the group given in parameter
	 */
	private function getIdUserGroup( int $group_id) {
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups_users WHERE group_id = %d", $group_id),
			ARRAY_A);
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
}