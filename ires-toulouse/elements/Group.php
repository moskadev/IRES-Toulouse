<?php

namespace irestoulouse\elements;

use irestoulouse\elements\sql\Database;
use WP_User;

/**
 * Groups have their own name and identifier.
 * Each group can have several users and also multiple responsables
 * managing the same group.
 */
class Group extends IresElement {

    public const NAME_LENGTH = 30;

    public const TYPE_RECHERCHE_ACTION = 0;
    public const TYPE_MANIFESTATION = 1;
    public const TYPE_AUTRE = 2;

    public const MAX_RESPONSABLES = 3;

    public const TYPE_NAMES = [
        self::TYPE_RECHERCHE_ACTION => "Recherche-action",
        self::TYPE_MANIFESTATION => "Manifestation",
        self::TYPE_AUTRE => "Autre"
    ];

    /** @var int */
    private int $type;
    /** @var string */
    private string $creationTime;
    /** @var WP_User */
    private WP_User $creator;

    /**
     * Looking if groups and groups_user table have been created and if they not, create then
     */
    public static function createTable() {
        require_once ABSPATH . "wp-admin/includes/upgrade.php";
        $db = Database::get();

        $charset_collate = $db->get_charset_collate();
        $table_name = $db->prefix . "groups";
        $sql_create_group = "CREATE TABLE $table_name (
                id_group bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name char(" . self::NAME_LENGTH . ") NOT NULL,
                type int(1) NOT NULL DEFAULT " . self::TYPE_RECHERCHE_ACTION . ",
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
                FOREIGN KEY (user_id) REFERENCES wp_users(ID),
                FOREIGN KEY (group_id) REFERENCES wp_groups(id_group),
                PRIMARY KEY (ID)
            ) $charset_collate;";
        maybe_create_table($table_name, $sql_create_user_group);

        // TEMPORAIRE pour l'ajout du type sans casser la table
        if (!isset($db->get_row("SELECT * FROM {$db->prefix}groups")->type)) {
            $db->query("ALTER TABLE {$db->prefix}groups ADD type INT(1) NOT NULL DEFAULT " . self::TYPE_RECHERCHE_ACTION);
        }
    }

    /**
     * Create a groups if it doesn't already exist in database
     *
     * @param string $name the group's name
     *
     * @return bool true if the group is created, else false
     * @throws \Exception
     */
    public static function register(string $name, int $type = self::TYPE_RECHERCHE_ACTION) : bool {
        if (self::isValid($name, $type) &&
            self::fromName($name) === null &&
            self::fromName(strtolower($name)) === null
        ) {
            $db = Database::get();

            return $db->insert($db->prefix . "groups",
                    [
                        "name" => $name,
                        "type" => $type,
                        "creator_id" => get_current_user_id()
                    ]
                ) !== false;
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
        return strlen($name) <= self::NAME_LENGTH &&
            in_array($type, array_keys(self::TYPE_NAMES));
    }

    /**
     * @param string $groupName the group's name that we're looking for
     *
     * @return Group|null the group found by its name
     * @throws \Exception
     */
    public static function fromName(string $groupName) : ?Group {
        $group = array_filter(self::all(), function ($g) use ($groupName) {
            return strtolower($g->name) === strtolower($groupName);
        });

        return $group[array_key_first($group)] ?? null;
    }

    /**
     * @return Group[] all the groups available
     * @throws \Exception
     */
    public static function all() : array {
        $db = Database::get();

        return array_map(function ($group) {
            return new Group(
                $group->id_group,
                $group->name,
                $group->type ?? self::TYPE_RECHERCHE_ACTION,
                $group->time_created,
                get_userdata($group->creator_id)
            );
        }, $db->get_results($db->prepare("SELECT * FROM {$db->prefix}groups ORDER BY name")));
    }

    /**
     * Delete a groups if it exists in the database
     *
     * @param int $id the group's id
     *
     * @return bool true if group deleted or not
     * @throws \Exception
     */
    public static function delete(int $id) : bool {
        if (self::exists($id)) {
            foreach (($g = Group::fromId($id))->getResponsables() as $r) {
                $g->removeResponsable($r);
            }
            $db = Database::get();

            $db->delete($db->prefix . "groups", ["id_group" => $id]);
            $db->delete($db->prefix . "groups_users", ["group_id" => $id]);

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
        try {
            return Group::fromId($id) !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param int $id the group's id that we're looking for
     *
     * @return Group|null the group found by its id
     * @throws \Exception
     */
    public static function fromId(int $id) : ?Group {
        $group = array_filter(self::all(), function ($g) use ($id) {
            return $g->id === $id;
        });

        return $group[array_key_first($group)] ?? null;
    }

    /**
     * Get all the users for who $user_id is responsible
     * @return WP_User[] all the id of the users
     */
    public static function getVisibleUsers(WP_User $from) {
        if (user_can($from, "administrator")) {
            return get_users();
        }
        $users = [];
        foreach (Group::allWhereUserResponsable($from) as $group) {
            foreach ($group->getUsers() as $u){
                if(!in_array($u, $users)){
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

    /**
     * @return array|null containing all the groups where an user is responsable
     */
    public static function allWhereUserResponsable(WP_User $user) : ?array {
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
     * @param int $id
     * @param string $name
     * @param int $type
     * @param string $creationTime
     * @param WP_User $creator
     *
     * @throws \Exception
     */
    public function __construct(int $id, string $name, int $type, string $creationTime, WP_User $creator) {
        if (!self::isValid($name, $type)) {
            throw new \Exception("Le nom du groupe $name ou le type $type est invalide");
        }
        parent::__construct($id, $name);
        $this->type = $type;
        $this->creationTime = $creationTime;
        $this->creator = $creator;
    }

    /**
     * @return int the group's type
     */
    public function getType() : int {
        return $this->type;
    }

    /**
     * @return string the creation time
     */
    public function getCreationTime() : string {
        return $this->creationTime;
    }

    /**
     * @return WP_User the group's creator
     */
    public function getCreator() : WP_User {
        return $this->creator;
    }

    /**
     * Get the users (if exist) in charge of the group given in parameter
     * @return WP_User[] the user(s) in charge of the group
     */
    public function getResponsables() : array {
        $db = Database::get();

        return array_map(function ($u) {
            return get_userdata($u->user_id);
        }, $db->get_results(
            $db->prepare("SELECT user_id FROM {$db->prefix}groups_users WHERE group_id = %d AND is_responsable = 1",
                $this->id
            ))
        );
    }

    /**
     * Adds a manager if he is already in the group
     * Adds the user to the group as a manager if he is not already there
     *
     * @param WP_User $user
     *
     * @return bool true if the responsable was added successfully
     */
    public function addResponsable(WP_User $user) : bool {
        $this->addUser($user);
        // à confirmer si l'utlisateur peut être responsable dans plus de 3 groupes
        //if (count(self::allWhereUserResponsable($user)) > self::MAX_RESPONSABLES) {
        //    return false;
        //}
        if (!$this->isUserResponsable($user) && count($this->getResponsables()) < self::MAX_RESPONSABLES) {
            $db = Database::get();
            $user->add_role("responsable");

            return $db->update($db->prefix . "groups_users",
                    ["is_responsable" => "1"],
                    ["user_id" => $user->ID, "group_id" => $this->id]) !== false;
        }

        return false;
    }

    /**
     * Check if the user is in a group
     *
     * @param $search $search the id of the user
     *
     * @return bool true if the user is in a group
     */
    public function userExists(WP_User $search) : bool {
        $user = array_filter($this->getUsers(), function ($u) use ($search) {
            return $search->ID === $u->ID;
        });

        return isset($user[array_key_first($user)]);
    }

    /**
     * Get all the users in a group
     * @return WP_User[] all the users
     */
    public function getUsers() : array {
        $db = Database::get();

        return array_map(function ($u) {
            return get_userdata($u->user_id);
        }, $db->get_results(
            $db->prepare("SELECT * FROM {$db->prefix}groups_users WHERE group_id = %d",
                $this->id
            ))
        );
    }

    /**
     * Check if a user is in charge of the group
     * @return bool true if the user is in charge of the group
     */
    public function isUserResponsable(WP_User $search) : bool {
        $responsable = array_filter($this->getResponsables(), function ($u) use ($search) {
            return $search->ID === $u->ID;
        });

        return isset($responsable[array_key_first($responsable)]);
    }

    /**
     * Ajoute l'utilisateur s'il n'est pas déjà présent et s'il existe
     *
     * @param WP_User $user
     *
     * @return bool
     */
    public function addUser(WP_User $user) : bool {
        if (!$this->userExists($user)) {
            $db = Database::get();

            return $db->insert($db->prefix . "groups_users", [
                "user_id" => $user->ID, "group_id" => $this->id, "is_responsable" => "0"
                ]) !== false;
        }

        return false;
    }

    /**
     * Remove the user from this group
     *
     * @param WP_User $user the user that we wanted to be removed
     *
     * @return bool true if the removing was a success
     */
    public function removeUser(WP_User $user) : bool {
        if ($this->userExists($user)) {
            $this->removeResponsable($user);

            $db = Database::get();
            return $db->delete($db->prefix . "groups_users",
                    ["user_id" => $user->ID, "group_id" => $this->id]) !== false;
        }

        return false;
    }

    /**
     * @param WP_User $user
     *
     * @return bool
     */
    public function removeResponsable(WP_User $user) : bool {
        if ($this->userExists($user) && $this->isUserResponsable($user)) {
            $db = Database::get();
            $request = $db->update($db->prefix . "groups_users",
                ["is_responsable" => "0"],
                ["user_id" => $user->ID, "group_id" => $this->id]
            );
            if (count(self::allWhereUserResponsable($user)) === 0) {
                $user->remove_role("responsable");
            }

            return $request !== false;
        }

        return false;
    }
}