<?php

namespace irestoulouse\menus;

include_once("IresMenu.php");

class SuppressionGroupMenu extends IresMenu
{
    public function __construct() {
        parent::__construct("Supprimer un groupe", // Page title when the menu is selected
            "Supprimer un groupe", // Name of the menu
            10, // Menu access security level
            "dashicons-businesswoman", // Menu icon
            3 // Page position in the list
        );
    }

    /**
     * Contents of the "Suppression" menu
     * Allows to :
     *      - delete a group if you are admin
     */
    function getContent(): void {
        if(isset($_POST["group_name"])) {
            $this->delete_group(esc_attr($_POST["group_name"]));
        }
        ?>
        <form method="post" name="delete_group" id="delete_group" class="validate" novalidate="novalidate">
            <h1>Supprimer un groupe</h1>
            <table class="form-table" role="presentation">
                <tr class="form-field form-required">
                    <th scope="row"><label for="group_name"><?php _e("Nom du groupe : "); ?> <span
                                class="description"><?php _e("(required)"); ?></span></label></th>
                    <td><input class="to-fill" name="group_name" type="text" id="group_name"/></td>
                </tr>
            </table>
            <?php submit_button(__("Supprimer groupe"), "primary", "delete-group", true, ["id" => "delete-group-sub"]);
            ?>
        </form>
        <?php
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