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

include_once("php/irestoulouse/menus/IresMenu.php");

use irestoulouse\menus\IresMenu;

if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php");
}

IresMenu::init();

add_action("admin_enqueue_scripts", function () {
    wp_enqueue_style( 'ires-style', '/wp-content/plugins/ires-toulouse/ires.css');
    wp_enqueue_script( 'ires-script', '/wp-content/plugins/ires-toulouse/js/fields.js', [], false, true);
});

