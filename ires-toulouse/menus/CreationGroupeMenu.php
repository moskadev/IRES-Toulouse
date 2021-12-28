<?php

namespace irestoulouse\menus;

use irestoulouse\elements\input\UserInputData;
use irestoulouse\utils\Dataset;

include_once("IresMenu.php");

class CreationGroupeMenu extends IresMenu
{

    public function __construct() {
        parent::__construct("Créer un groupe", // Page title when the menu is selected
            "Créer un groupe", // Name of the menu
            10, // Menu access security level
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
        if(isset($_POST['group_name'])) {
            $this->create_table();
            $this->insert_data_group(esc_attr($_POST['group_name']));
        }
        ?>
        <form method="post" name="create_group" id="create_group" class="validate" novalidate="novalidate">
            <h1>Créer un groupe d'utilisateurs</h1>
            <table class="form-table" role="presentation">
                <tr class="form-field form-required">
                    <th scope="row"><label for="group_name"><?php _e("Nom du groupe : "); ?> <span
                        class="description"><?php _e("(required)"); ?></span></label></th>
                    <td><input class="to-fill" name="group_name" type="text" id="group_name"/></td>
                </tr>
            </table>
            <?php submit_button(__("Créer groupe"), "primary", "create-group", true, ["id" => "create-group-sub"]);
            ?>
        </form>
        <?php
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

    /***
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
     * Create a groups if it doesn't already exist in database
     *
     * @param string $name
     */
    function insert_data_group($nameGroup) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'groups';

        // If the name group is present 0 times in the database$
        if ($this->groupExist($wpdb, $table_name,$nameGroup)) {
            $creator_id = get_current_user_id();
            $wpdb->insert(
                $table_name,
                array(
                    'name'=>$nameGroup,
                    'creator_id'=>$creator_id
                ),
                array( '%s','%d')
            );
        }
    }
}
?>