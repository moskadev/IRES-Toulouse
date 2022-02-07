<?php

namespace irestoulouse\elements;

use irestoulouse\sql\Database;

class Group extends IresElement {

	private string $creationTime;
	private \WP_User $creator;
	
	/**
	 * Looking if groups and groups_user table have been created and if they not, create then
	 *
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
		maybe_create_table($table_name, $sql_create_group );
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
		maybe_create_table($table_name, $sql_create_user_group );
	}

	/**
	 * Create a groups if it doesn't already exist in database
	 *
	 *
	 * @return true | false true if the group is created, else false
	 */
	public static function register(string $name): bool {
		$db = Database::get();

		if (self::fromName($name) === null) {
			$db->insert(
				$db->prefix.'groups',
				['name' => $name, 'creator_id '=> get_current_user_id()],
				['%s', '%d']
			);
			return true;
		}
		return false;
	}
	
	/**
	 * Check if a group already exist in database
	 *
	 * @return bool return true if the group exist, otherwise return false
	 */
	public static function exists(int $id): bool {
		$db = Database::get();
		return count($db->get_results(
				$db->prepare("SELECT * FROM {$db->prefix}groups WHERE id_group = %d", $id)
			)) > 0;
	}

	/**
	 * @return Group[] all the groups available
	 */
	public static function all() : array{
		$db = Database::get();
		return array_map(function ($group) {
			return new Group(
				$group["id_group"],
				$group["name"],
				$group["time_created"],
				get_user_by("id", $group["creator_id"])
			);
		}, $db->get_results($db->prepare("SELECT * FROM {$db->prefix}groups ORDER BY name")));
	}

	/**
	 * Delete a groups if he exist in database
	 *
	 * @return bool true if group deleted or not
	 */
	public static function delete(int $id) : bool {
		$db = Database::get();

		if (self::exists($id)) {
			$db->delete( $db->prefix.'groups', ['group_id' => $id], ['%s']);
			$db->get_results($db->prepare("DELETE FROM {$db->prefix}groups_users WHERE group_id = %d", $id));
			return true;
		}
		return false;
	}

	/**
	 * @return array|null with all the groups where a member is responsable
	 */
	public static function getAllWhereUserResponsable(\WP_User $user) {
		$db = Database::get();
		return $db->get_results(
			$db->prepare("SELECT id_group FROM {$db->prefix}groups JOIN {$db->prefix}groups_users ON id_group = group_id WHERE is_responsable = 1 AND user_id = %d", $user->ID),
			ARRAY_A
		);
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
	 * @return string
	 */
	public function getCreationTime(): string {
		return $this->creationTime;
	}

	/**
	 * @return \WP_User
	 */
	public function getCreator(): \WP_User {
		return $this->creator;
	}

    /**
     * Get all the user->id in a group
     * @return \WP_User[] all the users
     */
    public function getUsers(): array {
	    $db = Database::get();
        return array_map(function ($u) {
			return get_user_by("id", $u["user_id"]);
        }, $db->get_results(
	        $db->prepare("SELECT * FROM {$db->prefix}groups_users WHERE group_id = %d", $this->id)
        ));
    }

    /**
     * Get all the information about a group by his name
     * @param string $groupName
     * @return Group|null all the users in the group given in parameter
     */
    public static function fromName(string $groupName) : ?Group {
	    $db = Database::get();
        $group = array_map(function ($group) {
	        return new Group(
		        $group["id_group"],
		        $group["name"],
		        $group["time_created"],
		        get_user_by("id", $group["creator_id"])
	        );
        }, $db->get_results($db->prepare("SELECT * FROM {$db->prefix}groups WHERE name = %s", $groupName)));
		return count($group) > 0 ? $group[0] : null;
    }

    /**
     * Get the id of the user (if exist) in charge of the group given in parameter
     * @return \WP_User[] id of the user(s) in charge of the group
     */
    public function getResponsables() {
	    $db = Database::get();
        return array_map(function ($u) {
			return get_user_by("id", $u["user_id"]);
        }, $db->get_results(
			$db->prepare("SELECT user_id FROM {$db->prefix}groups_users WHERE group_id = %d AND is_responsable = 1",
				$this->id)
        ));
    }

    /**
     * Check if a user is in charge of the group
     * @return bool true if the user is in charge of the group, else false
     */
    public function isUserResponsable(\WP_User $search): bool {
        return count(array_filter($this->getResponsables(), function ($u) use ($search){
			return $search->ID === $u->ID;
		})) > 0;
    }

    /**
     * Check if the user is in a group
     * @param $userId int the id of the user
     * @return bool true if the user is in a group, else false
     */
    public function userExists(\WP_User $search) : bool {
		return count(array_filter($this->getUsers(), function ($u) use ($search){
			return $search->ID === $u->ID;
		})) > 0;
    }

    /**
     * @param $userId int the id of the user
     * @return array|object|null all the group(s) of a user
     */
    public static function getGroupWhereIsUser(int $userId) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}groups JOIN {$wpdb->prefix}groups_users ON group_id = id_group WHERE user_id = %d", $userId));
    }

    /** =================== */

    /**
     * Ajoute un responsable s'il est déjà dans le groupe
     * Ajoute l'utilisateur dans le groupe en tant que responsable s'il n'est pas déjà présent
     * @param $user_id
     * @param $group_id
     *
     * @return bool
     */
    public function addResponsable(\WP_User $user): bool {
	    $db = Database::get();
        if (!$this->userExists($user)) {
	        $db->get_results($db->prepare("INSERT INTO {$db->prefix}groups_users (user_id, group_id, is_responsable) VALUES (%d, %d, '1')", $user->ID, $this->id));
            $user->set_role("responsable");
            return true;
        }
		if (!$this->isUserResponsable($user)) {
			$db->get_results($db->prepare("UPDATE {$db->prefix}groups_users SET is_responsable = '1' WHERE user_id = %d AND group_id = %d", $user->ID, $this->id));
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
    public function removeResponsable(\WP_User $user): bool {
	    $db = Database::get();
        if ($this->userExists($user) && $this->isUserResponsable($user)) {
            if (count(self::getAllWhereUserResponsable($user)) < 2) {
                $user->set_role("subscriber");
            }
	        $db->get_results(
				$db->prepare("UPDATE {$db->prefix}groups_users SET is_responsable = '0' WHERE user_id = %d AND group_id = %d", 
					$user->ID, $this->id));
            return true;
        }
        return false;
    }

    /**
     * Ajoute l'utilisateur s'il n'est pas déjà présent et s'il existe
     * @param \WP_User $user
     *
     * @return bool
     */
    public function addUser(\WP_User $user): bool {
	    $db = Database::get();
        if (!$this->userExists($user)) {
	        $db->get_results($db->prepare("INSERT INTO {$db->prefix}groups_users (user_id, group_id, is_responsable) VALUES (%d, %d, '0')", $user->ID));
            return true;
        }
        return false;
    }

	/**
	 * @param \WP_User $user
	 *
	 * @return bool
	 */
    public function removeUser(\WP_User $user): bool {
	    $db = Database::get();
        if ($this->isUserResponsable($user)) {
            $this->removeResponsable($user);
        }
        $db->query(
			$db->prepare("DELETE FROM {$db->prefix}groups_users WHERE user_id = %d AND group_id = %d",
				$user->ID, $this->id)
        );
    }
}
