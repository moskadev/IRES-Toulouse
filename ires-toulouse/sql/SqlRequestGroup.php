<?php

namespace irestoulouse;

interface SqlRequestGroup {

    /***
     * Check if a group already exist in database
     *
     * @param $groupName name of the group to create
     * @return bool return true if the group exist, otherwise return false
     */
    public static function groupNotExist(string $wpdb, string $table_name, string $groupName): bool
    {
        $sql = "SELECT * FROM $table_name WHERE name='$groupName'";
        return count($wpdb->get_results($sql)) == 0;
    }

    /**
     * Create a groups if it doesn't already exist in database
     *
     * @param string $name
     */
    public static function insert_data_group(string $nameGroup) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'groups';

        // If the name group is present 0 times in the database$
        if ($this->groupNotExist($wpdb, $table_name,$nameGroup)) {
            $creator_id = get_current_user_id();
            $wpdb->insert(
                $table_name,
                array(
                    'name'=>$nameGroup,
                    'creator_id'=>$creator_id
                ),
                array( '%s','%d')
            );
        }
    }
    
}