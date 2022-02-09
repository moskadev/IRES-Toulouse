<?php

namespace menus;

use irestoulouse\menus\IresMenu;

class UserListMenu extends IresMenu {

    public function __construct() {
        $hasAboveRole = current_user_can('responsable') || current_user_can('administrator');
        parent::__construct(
            "Liste des profils de l'IRES de Toulouse",
            $hasAboveRole ? "Comptes IRES" : "Profil IRES",
            0,
            "dashicons-id-alt",
            3
        );
    }

    public function getContent() : void {

    }
}