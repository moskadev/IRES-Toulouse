<?php

namespace menus;

use irestoulouse\menus\IresMenu;

class UserListMenu extends IresMenu {

    public function __construct() {
        parent::__construct(
            "Liste des profils de l'IRES de Toulouse",
            "Comptes IRES",
            0,
            "dashicons-id-alt",
            3
        );
    }

    public function getContent() : void {
        global $submenu;
        var_dump($submenu['comptes_ires'][0][0]);
    }
}