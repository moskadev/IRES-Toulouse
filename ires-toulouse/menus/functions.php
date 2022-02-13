<?php
add_action('wp_ajax_nopriv_autocompleteSearch', 'awp_autocomplete_search');
add_action('wp_ajax_autocompleteSearch', 'awp_autocomplete_search');
function awp_autocomplete_search()
{

    // TODO : si responsable -> peut modifier que ses membres
    // TODO : si admin -> peut modifier sauf admin
    check_ajax_referer('autocompleteSearchNonce', 'security');
    global $wpdb;
    $current_user = get_current_user();
    $current_user_id = $current_user->ID;
    $search_term = $_REQUEST['term'];
    if (!isset($_REQUEST['term'])) {
        echo json_encode([]);
    }
    if(current_user_can('administrator')) {
        $suggestions = $wpdb->get_results("SELECT {$wpdb->prefix}users.ID AS id,{$wpdb->prefix}users.user_login AS label 
                                                FROM {$wpdb->prefix}users
                                                JOIN {$wpdb->prefix}usermeta 
                                                ON {$wpdb->prefix}usermeta.user_id={$wpdb->prefix}users.ID 
                                                JOIN {$wpdb->prefix}groups_users
                                                ON {$wpdb->prefix}groups_users.user_id={$wpdb->prefix}usermeta.user_id
                                                WHERE {$wpdb->prefix}groups_users.user_id NOT IN(
                                                    SELECT wp_groups_users.user_id
                                                    FROM wp_usermeta
                                                    JOIN wp_groups_users
                                                    ON wp_usermeta.user_id = wp_groups_users.user_id
                                                    WHERE meta_key LIKE 'wp_capabilities'
                                                    AND meta_value LIKE '%administrator%')
                                                GROUP BY {$wpdb->prefix}users.ID");
    } else {
        $suggestions = $wpdb->get_results($wpdb->prepare("SELECT user_id as id, user_login as label  FROM {$wpdb->prefix}groups_users 
                                        JOIN {$wpdb->prefix}users ON {$wpdb->prefix}users.ID=user_id WHERE group_id IN ( SELECT group_id FROM {$wpdb->prefix}groups_users WHERE user_id=%d AND is_responsable=1)", $current_user_id,$current_user_id));
    }
    echo json_encode($suggestions);
    wp_die();
}