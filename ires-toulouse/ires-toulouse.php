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
require_once("menus/AffectionRoleMenu.php");

require_once("elements/IresElement.php");
require_once("elements/Discipline.php");
require_once("elements/IresElement.php");
require_once("elements/user/UserData.php");

require_once("sql/Database.php");
require_once("sql/SqlRequest.php");

require_once("utils/Identifier.php");
require_once("utils/Dataset.php");

use irestoulouse\elements\UserData;
use irestoulouse\menus\IresMenu;

register_activation_hook( __FILE__, function (){
    add_role( 'responsable', 'Responsable', array('level_0' => true) );
});
register_deactivation_hook( __FILE__, function () {
    remove_role('responsable');
});

UserData::registerExtraMetas(get_current_user_id());
IresMenu::init();

add_action("admin_enqueue_scripts", function () {
    wp_enqueue_style( 'ires-style', '/wp-content/plugins/ires-toulouse/ires.css');
    wp_enqueue_script( 'ires-script', '/wp-content/plugins/ires-toulouse/js/fields.js', [], false, true);
});

