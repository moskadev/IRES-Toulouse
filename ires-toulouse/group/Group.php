<?php

namespace irestoulouse\group;

use irestoulouse\Element;
use irestoulouse\exceptions\InvalidGroupException;
use irestoulouse\sql\Database;
use WP_User;

/**
 * Groups have their own name and identifier.
 * Each group can have several users and also multiple responsables
 * managing the same group.
 *
 * @version 2.0
 */
class Group extends Element {

    public const MAX_RESPONSABLES = 3;
    public const NAME_LENGTH = 30;

    /** @var int */
    private int $type;
    /** @var string */
    private string $creationTime;
    /** @var WP_User */
    private WP_User $creator;

    /**
     * Initializing a group which can be recognized by its name or
     * identifier. It also has a type defined in GroupType
     *
     * @param int $id
     * @param string $name
     * @param int $type
     * @param string $creationTime
     * @param WP_User $creator
     *
     * @throws InvalidGroupException
     */
    public function __construct(int $id, string $name, int $type, string $creationTime, WP_User $creator) {
        if (!GroupFactory::isValid($name, $type)) {
            throw new InvalidGroupException($name, $type);
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
     * Adds a manager if he is already in the group
     * Adds the user to the group as a manager if he is not already there
     *
     * @param WP_User $user
     *
     * @return bool true if the responsable was added successfully
     */
    public function addResponsable(WP_User $user) : bool {
        $this->addUser($user);
        if (!$this->isUserResponsable($user) && count($this->getResponsables()) < self::MAX_RESPONSABLES) {
            $db = Database::get();
            $user->add_role("responsable");

            return $db->update($db->prefix . "groups_users",
                    ["is_responsable" => "1"],
                    ["user_id" => $user->ID, "group_id" => $this->id]
                ) !== false;
        }

        return false;
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
                    "user_id" => $user->ID,
                    "group_id" => $this->id,
                    "is_responsable" => "0"
                ]) !== false;
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
            )
        )
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
            )
        )
        );
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
                    ["user_id" => $user->ID, "group_id" => $this->id]
                ) !== false;
        }

        return false;
    }

    /**
     * Remove a user as a responsable from this group
     *
     * @param WP_User $user the responsable to be removed
     * @param bool $removeRole true if the WP role should be removed
     *
     * @return bool true if the responsable has been removed
     */
    public function removeResponsable(WP_User $user, bool $removeRole = true) : bool {
        if ($this->userExists($user) && $this->isUserResponsable($user)) {
            $db = Database::get();
            $request = $db->update($db->prefix . "groups_users",
                ["is_responsable" => "0"],
                ["user_id" => $user->ID, "group_id" => $this->id]
            );
            if ($removeRole && count(GroupFactory::allWhereUserResponsable($user)) === 0) {
                $user->remove_role("responsable");
            }

            return $request !== false;
        }

        return false;
    }
}