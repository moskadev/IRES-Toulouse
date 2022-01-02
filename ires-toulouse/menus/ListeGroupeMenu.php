<?php

namespace irestoulouse\menus;

include_once("IresMenu.php");

wp_register_style('prefix_bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
wp_enqueue_style('prefix_bootstrap');
class ListeGroupeMenu extends IresMenu {

	public function __construct() {
		parent::__construct("Liste des groupes", // Page title when the menu is selected
			"Liste des groupes", // Name of the menu
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
		if(isset($_POST['delete'])) {
			$this->delete_group($_POST['delete']);
			echo "<meta http-equiv='refresh' content='0'>";
		}

		if (isset($_POST['deleteMember'])) {
			$str = explode(".", $_POST['deleteMember']);
			$this->deleteUserGroup($str[0], $str[1]);
		}

		$groups = self::getGroups();
		//var_dump($groups[0]); ?>
        <div>
            <h1 class="wp-heading-inline">Groupes</h1>
            <a href="http://localhost/wordpress/wp-admin/admin.php?page=crer_un_groupe" class="page-title-action">Ajouter</a>
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
                            <button type="submit" id="delete" name="delete" value="<?php echo $group['name'] ?>" class="btn btn-outline-danger btn-sm"><?php echo __('Delete') ?></button>
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
			                        $data = $this->getUser($user['user_id'], "last_name");
                                    echo $data[0]['meta_value'];
			                        $data = $this->getUser($user['user_id'], "first_name");
			                        echo " ".$data[0]['meta_value'];
			                        ?>
                                </td>
                                <td colspan="3"></td>
                                <td>
                                    <form action="" method="post">
                                        <button type="submit" id="deleteMember" name="deleteMember" value="<?php echo $user['user_id'].".".$group['id_group'] ?>" class="btn btn-outline-danger btn-sm"><?php echo __('Remove') ?></button>
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
}