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
        echo "ok";
    }
}