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

require_once("menus/IresMenu.php");
require_once("menus/AddUserMenu.php");
require_once("menus/ModifyUserDataMenu.php");
require_once("menus/InformationUserMenu.php");
require_once("menus/ListeGroupeMenu.php");
require_once("menus/DetailsGroup.php");


require_once("elements/IresElement.php");
require_once("elements/Discipline.php");
require_once("elements/IresElement.php");
require_once("elements/input/UserInputData.php");

require_once("exceptions/InvalidInputValueException.php");

require_once("sql/Database.php");
require_once("sql/SqlRequest.php");

require_once("utils/Identifier.php");
require_once("utils/Dataset.php");

use irestoulouse\elements\Discipline;
use irestoulouse\elements\input\UserInputData;
use irestoulouse\menus\IresMenu;

register_activation_hook( __FILE__, function (){
    add_role( "responsable", "Responsable", [
        "read" => true,
        "level_0" => true,
        "level_1" => true,
        "level_2" => true
    ]);
});
register_deactivation_hook( __FILE__, function () {
    remove_role("responsable");
});

UserInputData::registerExtraMetas(get_current_user_id());
IresMenu::init();

$dis = [
    "Mathématiques",
    "Physique-Chimie",
    "SVT",
    "SNT",
    "NSI",
    "Technologie",
    "Informatique",
    "Documentation",
    "STI"
];
foreach ($dis as $d){
    Discipline::register(new Discipline($d));
}

//TODO do not use bruteforced paths
add_action("admin_enqueue_scripts", function () {
    wp_enqueue_style("ires-style", "/wp-content/plugins/ires-toulouse/style/ires.css");
    wp_enqueue_script("ires-script-fields", "/wp-content/plugins/ires-toulouse/js/fields.js", [], false, true);
});

