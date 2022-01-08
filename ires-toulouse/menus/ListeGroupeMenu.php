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

		//var_dump($user);
		//var_dump($groups[0]);
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
		        $message = "Le groupe ".$_POST['nameAddGroup']." a était créé.";
            }
	        //echo "<meta http-equiv='refresh' content='0'>";

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
                    <th scope="col">Responsable</th>
                    <th scope="col">Date de création</th>
                    <th scope="col"></th>
                    <th scope="col">Membres</th>
                </tr>
            </thead>
            <tbody>
<?php
                foreach ($groups as $group) {?>
                <tr>
                    <!-- Name of the group -->
                    <th scope="row" class="text-primary">
                        <?php echo $group['name'] ?>
                    </th>
                    <!-- Name of the responsible -->
                    <td class="">
                        <?php
                            if ($group['name_responsable'] === "" | $group['name_responsable'] == null ) {
                                echo "---";
                            } else {
                                echo $group['name_responsable'];
                            }
                        ?>
                    </td>
                    <!-- Date -->
                    <td>
                        <?php echo $group['time_created'] ?>
                    </td>
                    <td>
                        <form action="" method="post">
                            <button type="submit"
                                    id="delete"
                                    name="delete"
                                    value="<?php echo $group['name'] ?>"
                                    class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Êtes vous sur de vouloir supprimer le groupe : <?php echo $group['name']; ?> ?');">
                                    <?php echo __('Delete') ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <form action="" method="post">
                            <button type="submit" class="btn btn-outline-secondary" name="list<?php echo $group['name'] ?>" value="<?php echo $group['id_group'] ?>">
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                            </button>
                        </form>
                    </td>
                </tr>

                    <?php
                    $id = "list".$group['name'];
                    if (isset($_POST[$id])) {
                        $users = $this->getIdUserGroup($_POST[$id]);
                        foreach ( $users as $user ) {
	                        ?>
                            <tr>
                                <td>
			                        <?php
			                        $last_name = $this->getUser($user['user_id'], "last_name");
                                    echo $last_name[0]['meta_value'];
			                        $first_name = $this->getUser($user['user_id'], "first_name");
			                        echo " ".$first_name[0]['meta_value'];
			                        ?>
                                </td>
                                <td colspan="3"></td>
                                <td>
                                    <form action="" method="post">
                                        <button type="submit"
                                                id="deleteMember"
                                                name="deleteMember"
                                                value="<?php echo $user['user_id'].".".$group['id_group'] ?>"
                                                class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('Êtes vous sur de vouloir retirer <?php echo $first_name[0]['meta_value']." ".$last_name[0]['meta_value']; ?> du groupe <?php echo $group['name']; ?> ?');">
                                                    <?php echo __('Remove') ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
	                        <?php
                        }
                        ?>
                        <?php
                    }
                    ?>

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
                    <td>Membres</td>
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

	/**
	 * @param $group_id the id of the group
	 *
	 * @return array|object|null
	 */
    private function getIdUserGroup($group_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups_users WHERE group_id = %d", $group_id),
            ARRAY_A);
    }

	/**
	 * @param $userId
	 *
	 * @return array|object|null
	 */
    private function getUser($userId, $metaKey) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->prefix}usermeta WHERE user_id = %d AND meta_key = %s", $userId, $metaKey),
            ARRAY_A);
    }

    private function deleteUserGroup($userId, $groupId) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("DELETE FROM {$wpdb->prefix}groups_users WHERE user_id = %d AND group_id = %d", $userId, $groupId));
    }

	/**
	 * Looking if groups and groups_user table have been created and if they not, create then
	 *
	 * @param string $inputId
	 * @return string input's value
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
                user_id bigint(20) UNSIGNED NOT NULL,
                group_id bigint(20) UNSIGNED NOT NULL,
                FOREIGN KEY (user_id) REFERENCES wp_users(ID),
                FOREIGN KEY (group_id) REFERENCES wp_groups(id_group)
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
}