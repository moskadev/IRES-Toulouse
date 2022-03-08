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

require_once("generators/FileGenerator.php");
require_once("generators/ExcelGenerator.php");
require_once("generators/CsvGenerator.php");

require_once("elements/sql/Database.php");

require_once("utils/Identifier.php");
require_once("utils/Dataset.php");
require_once("utils/Locker.php");

include_once(__DIR__ . "/../../../wp-includes/pluggable.php");
include_once(__DIR__ . "/../../../wp-includes/functions.php");

use irestoulouse\elements\data\UserData;
use irestoulouse\elements\Group;
use irestoulouse\menus\IresMenu;

date_default_timezone_set("UTC");

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
Group::init();

wp_enqueue_script("autocomplete-search", "/wp-content/plugins/ires-toulouse/js/auto-fill-search.js",
    ["jquery", "jquery-ui-autocomplete"], null, true);
wp_localize_script("autocomplete-search", "AutocompleteSearch", [
    "ajax_url" => admin_url("admin-ajax.php"),
    "ajax_nonce" => wp_create_nonce("autocompleteSearchNonce")
]);
$wp_scripts = wp_scripts();
wp_enqueue_style('jquery-ui-css',
    '//ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-autocomplete']->ver . '/themes/smoothness/jquery-ui.css',
    false, null, false
);

add_action("admin_enqueue_scripts", function () {
    wp_enqueue_style("ires-style", "/wp-content/plugins/ires-toulouse/style/ires.css");
    wp_enqueue_script("ires-script", "/wp-content/plugins/ires-toulouse/js/script.js", [], false, true);
    wp_enqueue_script("ires-script-fields", "/wp-content/plugins/ires-toulouse/js/fields.js", [], false, true);
    wp_enqueue_script("ires-script-popups", "/wp-content/plugins/ires-toulouse/js/popups.js", [], false, true);
    wp_enqueue_script("ires-script-export", "/wp-content/plugins/ires-toulouse/js/export-data.js", [], false, true);
});