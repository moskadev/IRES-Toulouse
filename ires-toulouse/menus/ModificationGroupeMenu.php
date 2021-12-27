<?php

namespace irestoulouse\menus;

include_once("IresMenu.php");

class ModificationGroupeMenu extends IresMenu
{
    public function __construct() {
        parent::__construct("Modifier un groupe", // Page title when the menu is selected
            "Modifier un groupe", // Name of the menu
            10, // Menu access security level
            "dashicons-businesswoman", // Menu icon
            3 // Page position in the list
        );
    }
    /**
     * Contents of the "Modify a group" menu
     * Allows to :
     *      - modify a group of user if you are admin
     */
    function getContent(): void {
        ?>
        <form method="post" name="create_group" id="create_group" class="validate" novalidate="novalidate">
            <h1>Modifier un groupe</h1>
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
}