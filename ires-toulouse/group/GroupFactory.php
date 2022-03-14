<?php

namespace irestoulouse\group;

use Exception;
use irestoulouse\exceptions\InvalidGroupException;
use irestoulouse\sql\Database;
use WP_User;

/**
 * Management of groups with multiple methods like
 * their initializing, registration, removing, etc..
 *
 * @version 2.0
 */
class GroupFactory {

    /**
     * Looking if groups and groups_user table have been
     * created and if they not, create then
     */
    public static function init() {
        require_once ABSPATH . "wp-admin/includes/upgrade.php";
        $db = Database::get();

        $charset_collate = $db->get_charset_collate() . " ENGINE = innoDB";
        $table_name = $db->prefix . "groups";
        $sql_create_group = "CREATE TABLE $table_name (
                id_group bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name char(" . Group::NAME_LENGTH . ") NOT NULL,
                type int(1) NOT NULL DEFAULT " . GroupType::RECHERCHE_ACTION . ",
                time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                creator_id bigint(20) UNSIGNED NOT NULL,
                FOREIGN KEY (creator_id) REFERENCES wp_users(ID),
                PRIMARY KEY  (id_group) 
            ) $charset_collate;";
        maybe_create_table($table_name, $sql_create_group);
        $table_name = $db->prefix . "groups_users";

        $sql_create_user_group = "CREATE TABLE $table_name (
                ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id bigint(20) UNSIGNED NOT NULL,
                group_id bigint(20) UNSIGNED NOT NULL,
                is_responsable int(1) NOT NULL DEFAULT '0',
                FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE,
                FOREIGN KEY (group_id) REFERENCES wp_groups(id_group) ON DELETE CASCADE,
                PRIMARY KEY (ID)
            ) $charset_collate;";
        maybe_create_table($table_name, $sql_create_user_group);

        global $wp_roles;
        // remove responsable from all groups from users who do not have
        // a responsable role in the wp database
        // responsable are not remove if the role responsable doesn't exist
        // to avoid losing all data in groups
        foreach (get_users() as $user) {
            if (isset($wp_roles->get_names()["responsable"]) &&
                !user_can($user, "responsable")) {
                foreach (self::allWhereUserResponsable($user) as $g) {
                    $g->removeResponsable($user, false);
                }
            }
        }
    }

    /**
     * @return Group[] containing all the groups where the given user is responsable
     */
    public static function allWhereUserResponsable(WP_User $user) : array {
        $groups = [];
        foreach (self::getUserGroups($user) as $g) {
            if (in_array($user, $g->getResponsables())) {
                $groups[] = $g;
            }
        }

        return $groups;
    }

    /**
     * @param $user WP_User the user that we're looking for
     *
     * @return Group[] all the group(s) of this user
     */
    public static function getUserGroups(WP_User $user) : array {
        $db = Database::get();
        $request = $db->get_results($db->prepare(
            "SELECT * FROM {$db->prefix}groups JOIN {$db->prefix}groups_users ON group_id = id_group WHERE user_id = %d",
            $user->ID)
        );
        if (count($request) === 0) {
            return [];
        }

        return array_map(function ($group) {
            return self::fromId($group->id_group);
        }, $request);
    }

    /**
     * @param int $id the group's id that we're looking for
     *
     * @return Group|null the group found by its id
     */
    public static function fromId(int $id) : ?Group {
        $group = array_filter(self::all(), function ($g) use ($id) {
            return $g->getId() === $id;
        });
        try {
            return $group[array_key_first($group)] ?? null;
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     * @return Group[] all the groups available
     */
    public static function all() : array {
        $db = Database::get();
        $groups = [];
        foreach ($db->get_results($db->prepare("SELECT * FROM {$db->prefix}groups ORDER BY name")) as $group) {
            try {
                $groups[] = new Group(
                    $group->id_group,
                    $group->name,
                    $group->type ?? GroupType::RECHERCHE_ACTION,
                    $group->time_created,
                    get_userdata($group->creator_id)
                );
            } catch (InvalidGroupException $exception) {
                // ignored
            }
        }

        return $groups;
    }

    /**
     * Create a groups if it doesn't already exist in database
     *
     * @param string $name the group's name
     *
     * @return bool true if the group is created, else false
     */
    public static function register(string $name, int $type = GroupType::RECHERCHE_ACTION) : bool {
        if (self::isValid($name, $type) &&
            self::fromName($name) === null &&
            self::fromName(strtolower($name)) === null) {
            $db = Database::get();

            return $db->insert($db->prefix . "groups", [
                    "name" => $name,
                    "type" => $type,
                    "creator_id" => get_current_user_id()
                ]) !== false;
        }

        return false;
    }

    /**
     * Checks if the name and type that have been given are correct if
     * name >=
     *
     * @param string $name the group's name to check
     * @param int $type the type to check
     *
     * @return bool true if it exists
     */
    public static function isValid(string $name, int $type) : bool {
        return strlen($name) <= Group::NAME_LENGTH &&
            in_array($type, array_keys(GroupType::NAMES));
    }

    /**
     * @param string $groupName the group's name that we're looking for
     *
     * @return Group|null the group found by its name
     */
    public static function fromName(string $groupName) : ?Group {
        $group = array_filter(self::all(), function ($g) use ($groupName) {
            return strtolower($g->getName()) === strtolower($groupName);
        });

        return $group[array_key_first($group)] ?? null;
    }

    /**
     * Delete a groups if it exists in the database
     *
     * @param int $id the group's id
     *
     * @return bool true if group deleted or not
     */
    public static function delete(int $id) : bool {
        if (self::exists($id)) {
            foreach (($g = self::fromId($id))->getResponsables() as $r) {
                $g->removeResponsable($r);
            }
            $db = Database::get();
            $db->delete($db->prefix . "groups", ["id_group" => $id]);
            // not necessary on delete cascade
            // $db->delete($db->prefix . "groups_users", ["group_id" => $id]);
            return true;
        }

        return false;
    }

    /**
     * Check if a group already exist in database
     *
     * @param int $id the group's id
     *
     * @return bool true if the group exist, otherwise return false
     */
    public static function exists(int $id) : bool {
        return self::fromId($id) !== null;
    }

    /**
     * Get all the users for where $user_id is responsable
     * @return WP_User[] all the id of the users
     */
    public static function getVisibleUsers(WP_User $from) : array {
        if (user_can($from, "administrator")) {
            return get_users();
        }
        $users = [];
        foreach (self::allWhereUserResponsable($from) as $group) {
            foreach ($group->getUsers() as $u) {
                if (!in_array($u, $users)) {
                    $users[] = $u;
                }
            }
        }
        $users = array_filter($users, function ($u) use ($from) {
            return !user_can($u, "administrator") ||
                !user_can($u, "responsable");
        });
        if (!in_array($from, $users)) {
            $users[] = $from;
        }

        return $users;
    }
}