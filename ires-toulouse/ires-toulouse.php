<?php

/*
Plugin Name: IRES de Toulouse
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Adding new content related to the IRES de Toulouse
Version: 2.0
Author: Yohann MAY, Maxime MOSKALYK, Robin FOUGERON, Téo EMIROT
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

require_once("controllers/Controller.php");
require_once("controllers/EmailController.php");
require_once("controllers/UserConnectionController.php");
require_once("controllers/InputDataController.php");

require_once("Element.php");
require_once("group/GroupType.php");
require_once("group/Group.php");
require_once("group/GroupFactory.php");
require_once("data/UserCustomDataType.php");
require_once("data/UserCustomData.php");
require_once("data/UserCustomDataFactory.php");

require_once("menus/MenuIds.php");
require_once("menus/Menu.php");
require_once("menus/MenuFactory.php");
require_once("menus/groups/GroupListMenu.php");
require_once("menus/groups/GroupDetailsMenu.php");
require_once("menus/users/UserRegisterMenu.php");
require_once("menus/users/UserProfileMenu.php");
require_once("menus/users/UserListMenu.php");

require_once("exceptions/InvalidDataValueException.php");
require_once("exceptions/FailedUserRegistrationException.php");

require_once("generators/FileGenerator.php");
require_once("generators/excel/ExcelGenerator.php");
require_once("generators/excel/XLSXWriterBufferedWriter.php");
require_once("generators/excel/XLSXWriter.php");
require_once("generators/CsvGenerator.php");

require_once("sql/Database.php");

require_once("utils/Identifier.php");
require_once("utils/Dataset.php");
require_once("utils/Locker.php");

include_once(__DIR__ . "/../../../wp-includes/pluggable.php");
include_once(__DIR__ . "/../../../wp-includes/functions.php");

use irestoulouse\data\UserCustomDataFactory;
use irestoulouse\group\GroupFactory;
use irestoulouse\menus\MenuFactory;
use irestoulouse\sql\Database;

date_default_timezone_set("UTC");

registerAndUnregisterRoles();

// create all custom data for the current user logged
UserCustomDataFactory::registerExtraMetas(get_current_user_id());
// creating new menus
MenuFactory::init();
// creating groups database
GroupFactory::init();

initAutocompletation();
registerStylesAndScripts();
convertAllEngineToInnoDb();

/**
 * Add hookers for roles registration
 */
function registerAndUnregisterRoles() {
    register_activation_hook(__FILE__, function () {
        // register responsable role
        add_role("responsable", "Responsable", [
            "read" => true,
            "level_0" => true,
            "level_1" => true,
            "level_2" => true,
            "level_3" => true
        ]);
        // register direction IRES role
        add_role("direction", "Direction IRES", [
            "read" => true,
            "level_0" => true,
            "level_1" => true,
            "level_2" => true
        ]);
    });
    // remove all created roles on deactivation
    register_deactivation_hook(__FILE__, function () {
        remove_role("responsable");
        remove_role("direction");
    });
}

/**
 * Start all scripts and styles for auto-completation fields
 */
function initAutocompletation() {
    wp_enqueue_script("autocomplete-search", "/wp-content/plugins/ires-toulouse/js/auto-fill-search.js",
        ["jquery", "jquery-ui-autocomplete"], null, true
    );
    wp_localize_script("autocomplete-search", "AutocompleteSearch", [
        "ajax_url" => admin_url("admin-ajax.php"),
        "ajax_nonce" => wp_create_nonce("autocompleteSearchNonce")
    ]);
    $wp_scripts = wp_scripts();
    wp_enqueue_style('jquery-ui-css',
        '//ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-autocomplete']->ver . '/themes/smoothness/jquery-ui.css',
        false, null, false
    );
}

/**
 * Register all custom CSS styles and JS scripts
 */
function registerStylesAndScripts() {
    add_action("admin_enqueue_scripts", function () {
        wp_enqueue_style("ires-style", "/wp-content/plugins/ires-toulouse/style/ires.css");
        wp_enqueue_script("ires-script", "/wp-content/plugins/ires-toulouse/js/script.js", [], false, true);
        wp_enqueue_script("ires-script-fields", "/wp-content/plugins/ires-toulouse/js/fields.js", [], false, true);
        wp_enqueue_script("ires-script-popups", "/wp-content/plugins/ires-toulouse/js/popups.js", [], false, true);
        wp_enqueue_script("ires-script-export", "/wp-content/plugins/ires-toulouse/js/export-data.js", [], false, true);
    });
}

/**
 * Convert all the tables' engine to InnoDB
 */
function convertAllEngineToInnoDb() {
    $list_of_table = Database::get()->get_results("SHOW TABLE STATUS WHERE Engine != 'INNODB'");
    foreach ($list_of_table as $table) {
        $repair_db = Database::get()->query("ALTER TABLE {$table->Name} ENGINE=INNODB");
    }
}