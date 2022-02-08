<?php

namespace irestoulouse\elements;

use irestoulouse\sql\Database;

/**
 * Groups have their own name and identifier.
 * Each group can have several users and also multiple responsables
 * managing the same group.
 */
class Group extends IresElement {

    /** @var string */
    private string $creationTime;
    /** @var \WP_User */
    private \WP_User $creator;

    /**
     * Looking if groups and groups_user table have been created and if they not, create then
     */
    public static function createTable() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $db = Database::get();

        $charset_collate = $db->get_charset_collate();
        $table_name = $db->prefix . 'groups';
        $sql_create_group = "CREATE TABLE $table_name (
                id_group bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name char(30) NOT NULL,
                time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                creator_id bigint(20) UNSIGNED NOT NULL,
                FOREIGN KEY (creator_id) REFERENCES wp_users(ID),
                PRIMARY KEY  (id_group) 
            ) $charset_collate;";
        maybe_create_table($table_name, $sql_create_group);
        $table_name = $db->prefix . 'groups_users';

        $sql_create_user_group = "CREATE TABLE $table_name (
                ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id bigint(20) UNSIGNED NOT NULL,
                group_id bigint(20) UNSIGNED NOT NULL,
                is_responsable int(1) NOT NULL DEFAULT '0',
                FOREIGN KEY (user_id) REFERENCES wp_users(ID),
                FOREIGN KEY (group_id) REFERENCES wp_groups(id_group),
                PRIMARY KEY (ID)
            ) $charset_collate;";
        maybe_create_table($table_name, $sql_create_user_group);
    }

    /**
     * Create a groups if it doesn't already exist in database
     *
     * @param string $name the group's name
     *
     * @return bool true if the group is created, else false
     */
    public static function register(string $name) : bool {
        $db = Database::get();
        if (self::fromName($name) === null) {
            $db->insert(
                $db->prefix . 'groups',
                ['name' => $name, 'creator_id' => get_current_user_id()],
                ['%s', '%d']
            );
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
        return Group::fromId($id) !== null;
    }

    /**
     * @return Group[] all the groups available
     */
    public static function all() : array {
        $db = Database::get();

        return array_map(function ($group) {
            return new Group(
                $group->id_group,
                $group->name,
                $group->time_created,
                get_user_by("id", $group->creator_id)
            );
        }, $db->get_results($db->prepare("SELECT * FROM {$db->prefix}groups ORDER BY name")));
    }

    /**
     * Delete a groups if it exists in the database
     *
     * @param int $id the group's id
     *
     * @return bool true if group deleted or not
     */
    public static function delete(int $id) : bool {
        $db = Database::get();

        if (self::exists($id)) {
            $db->delete($db->prefix . 'groups', ['id_group' => $id], ['%s']);
            $db->get_results($db->prepare("DELETE FROM {$db->prefix}groups_users WHERE group_id = %d",
                $id)
            );
            return true;
        }
        return false;
    }

    /**
     * @param $user \WP_User the user that we're looking for
     *
     * @return Group[] all the group(s) of this user
     */
    public static function getUserGroups(\WP_User $user) : array {
        $db = Database::get();
        $request = $db->get_results($db->prepare("SELECT * FROM {$db->prefix}groups JOIN {$db->prefix}groups_users ON group_id = id_group WHERE user_id = %d",
            $user->ID)
        );
        if (count($request) === 0) {
            return [];
        }

        return array_map(function ($group) {
            return self::fromName($group->name);
        }, $request);
    }


    /**
     * @return array|null containing all the groups where an user is responsable
     */
    public static function getAllWhereUserResponsable(\WP_User $user) {
        $db = Database::get();

        return $db->get_results(
            $db->prepare("SELECT id_group FROM {$db->prefix}groups JOIN {$db->prefix}groups_users ON id_group = group_id WHERE is_responsable = 1 AND user_id = %d",
                $user->ID
            ),
            ARRAY_A
        );
    }

    /**
     * Get all the users in a group
     * @return \WP_User[] all the users
     */
    public function getUsers() : array {
        $db = Database::get();

        return array_map(function ($u) {
            return get_user_by("id", $u->user_id);
        }, $db->get_results(
            $db->prepare("SELECT * FROM {$db->prefix}groups_users WHERE group_id = %d",
                $this->id
            ))
        );
    }

    /**
     * @param string $groupName the group's name that we're looking for
     *
     * @return Group|null the group found by its name
     */
    public static function fromName(string $groupName) : ?Group {
        $group = array_filter(self::all(), function ($g) use ($groupName) {
            return $g->name === $groupName;
        });
        return $group[array_key_first($group)] ?? null;
    }

    /**
     * @param string $id the group's id that we're looking for
     *
     * @return Group|null the group found by its id
     */
    public static function fromId(string $id) : ?Group {
        $group = array_filter(self::all(), function ($g) use ($id) {
            return $g->id === $id;
        });
        return $group[array_key_first($group)] ?? null;
    }

    /**
     * @param string $id
     * @param string $name
     * @param string $creationTime
     * @param \WP_User $creator
     */
    public function __construct(string $id, string $name, string $creationTime, \WP_User $creator) {
        parent::__construct($id, $name);
        $this->creationTime = $creationTime;
        $this->creator = $creator;
    }

    /**
     * @return string the creation time
     */
    public function getCreationTime() : string {
        return $this->creationTime;
    }

    /**
     * @return \WP_User the group's creator
     */
    public function getCreator() : \WP_User {
        return $this->creator;
    }

    /**
     * Get the users (if exist) in charge of the group given in parameter
     * @return \WP_User[] the user(s) in charge of the group
     */
    public function getResponsables() {
        $db = Database::get();

        return array_map(function ($u) {
            return get_user_by("id", $u->user_id);
        }, $db->get_results(
                $db->prepare("SELECT user_id FROM {$db->prefix}groups_users WHERE group_id = %d AND is_responsable = 1",
                    $this->id
                )
            )
        );
    }

    /**
     * Check if a user is in charge of the group
     * @return bool true if the user is in charge of the group
     */
    public function isUserResponsable(\WP_User $search) : bool {
        $responsable = array_filter($this->getResponsables(), function ($u) use ($search) {
            return $search->ID === $u->ID;
        });
        return isset($responsable[array_key_first($responsable)]);
    }

    /**
     * Check if the user is in a group
     *
     * @param $search $search the id of the user
     *
     * @return bool true if the user is in a group
     */
    public function userExists(\WP_User $search) : bool {
        $user = array_filter($this->getUsers(), function ($u) use ($search) {
            return $search->ID === $u->ID;
        });
        return isset($user[array_key_first($user)]);
    }

    /**
     * Adds a manager if he is already in the group
     * Adds the user to the group as a manager if he is not already there
     *
     * @param \WP_User $user
     *
     * @return bool true if the responsable was added successfully
     */
    public function addResponsable(\WP_User $user) : bool {
        $db = Database::get();
        if (!$this->userExists($user)) {
            $db->get_results($db->prepare("INSERT INTO {$db->prefix}groups_users (user_id, group_id, is_responsable) VALUES (%d, %d, '1')",
                $user->ID,
                $this->id)
            );
            $user->set_role("responsable");

            return true;
        }
        if (!$this->isUserResponsable($user)) {
            $db->get_results($db->prepare("UPDATE {$db->prefix}groups_users SET is_responsable = '1' WHERE user_id = %d AND group_id = %d",
                $user->ID,
                $this->id)
            );
            $user->set_role("responsable");

            return true;
        }

        return false;
    }

    /**
     * @param \WP_User $user
     *
     * @return bool
     */
    public function removeResponsable(\WP_User $user) : bool {
        if ($this->userExists($user) && $this->isUserResponsable($user)) {
            $db = Database::get();

            if (count(self::getAllWhereUserResponsable($user)) < 2) {
                $user->set_role("subscriber");
            }
            $db->get_results(
                $db->prepare("UPDATE {$db->prefix}groups_users SET is_responsable = '0' WHERE user_id = %d AND group_id = %d",
                    $user->ID,
                    $this->id
                )
            );
            return true;
        }

        return false;
    }

    /**
     * Ajoute l'utilisateur s'il n'est pas déjà présent et s'il existe
     *
     * @param \WP_User $user
     *
     * @return bool
     */
    public function addUser(\WP_User $user) : bool {
        if (!$this->userExists($user)) {
            $db = Database::get();
            $db->get_results($db->prepare("INSERT INTO {$db->prefix}groups_users (user_id, group_id, is_responsable) VALUES (%d, %d, '0')",
                $user->ID, $this->id)
            );
            return true;
        }
        return false;
    }

    /**
     * Remove the user from this group
     *
     * @param \WP_User $user the user that we wanted to be removed
     *
     * @return bool true if the removing was a success
     */
    public function removeUser(\WP_User $user) : bool {
        if ($this->userExists($user)) {
            if ($this->isUserResponsable($user)) {
                $this->removeResponsable($user);
            }
            $db = Database::get();
            $db->query(
                $db->prepare("DELETE FROM {$db->prefix}groups_users WHERE user_id = %d AND group_id = %d",
                    $user->ID,
                    $this->id
                )
            );

            return true;
        }

        return false;
    }
}
