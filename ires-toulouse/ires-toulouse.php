<?php

/*
Plugin Name: IRES Toulouse
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: 1.0
Author: Yohann MAY, Maxime MOSKALYK, Robin FOUGERON, Teo EMIROT
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

require_once("controllers/Controller.php");
require_once("controllers/EmailSender.php");
require_once("controllers/UserConnection.php");
require_once("controllers/UserInputData.php");

require_once("elements/IresElement.php");
require_once("elements/Discipline.php");
require_once("elements/Group.php");
require_once("elements/IresElement.php");
require_once("elements/data/UserData.php");

require_once("menus/IresMenu.php");
require_once("menus/groups/GroupListMenu.php");
require_once("menus/groups/GroupDetailsMenu.php");
require_once("menus/UserRegisterMenu.php");
require_once("menus/UserProfileMenu.php");
require_once("menus/UserListMenu.php");

require_once("exceptions/InvalidInputValueException.php");
require_once("exceptions/FailedUserRegistrationException.php");

require_once("elements/sql/Database.php");
require_once("elements/sql/SqlRequest.php");

require_once("utils/Identifier.php");
require_once("utils/Dataset.php");

include_once(__DIR__ . "/../../../wp-includes/pluggable.php");
include_once(__DIR__ . "/../../../wp-includes/functions.php");

use irestoulouse\elements\input\UserData;
use irestoulouse\menus\IresMenu;

register_activation_hook(__FILE__, function () {
    add_role("responsable", "Responsable", [
        "read" => true,
        "level_0" => true,
        "level_1" => true,
        "level_2" => true,
        "level_3" => true
    ]);
    add_role("direction", "Direction IRES", [
        "read" => true,
        "level_0" => true,
        "level_1" => true,
        "level_2" => true
    ]);
});
register_deactivation_hook(__FILE__, function () {
    remove_role("responsable");
    remove_role("direction");
});

UserData::registerExtraMetas(get_current_user_id());
IresMenu::init();

add_action("admin_enqueue_scripts", function () {
    wp_enqueue_style("ires-style", "/wp-content/plugins/ires-toulouse/style/ires.css");
    wp_enqueue_script("ires-script", "/wp-content/plugins/ires-toulouse/js/script.js", [], false, true);
    wp_enqueue_script("ires-script-fields", "/wp-content/plugins/ires-toulouse/js/fields.js", [], false, true);
    wp_enqueue_script("ires-script-popups", "/wp-content/plugins/ires-toulouse/js/popups.js", [], false, true);
});