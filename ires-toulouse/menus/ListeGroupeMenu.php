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
		$groups = $this->getGroups();
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
                    <th scope="col">Date création</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
<?php
                foreach ($groups as $group) { ?>
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
                    <td>Date création</th>
                    <td></td>
                </tr>
            </tfoot>
<?php       } // endif ?>
        </table>
<?php
		if(isset($_POST['delete'])) {
			$this->delete_group($_POST['delete']);
			echo "<meta http-equiv='refresh' content='0'>";
			//var_dump($_POST);
        }
	} // end function

	/**
	 * @return array|object|null all the groups available
	 * TODO move to respect MVC
	 */
	private function getGroups() {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups ORDER BY name"),
			ARRAY_A);
	}


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
	 * Delete a groups if he exist in database
	 *
	 * @param string $inputId
	 * @return string input's value
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
		}
	}
}