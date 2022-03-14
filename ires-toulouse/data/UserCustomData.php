<?php

namespace irestoulouse\data;

use irestoulouse\Element;
use irestoulouse\exceptions\InvalidDataValueException;
use irestoulouse\group\GroupFactory;
use WP_User;

/**
 * A user's data is a custom metadata for the IRES of Toulouse
 *
 * @version 2.0
 */
class UserCustomData extends Element {

    /** @var string */
    private string $formType;
    /** @var string */
    private string $description;
    /** @var string */
    private string $parent;
    /** @var bool */
    private bool $uppercase;
    /** @var bool */
    private bool $required;
    /** @var string */
    private string $regex;
    /** @var mixed */
    private $extraData;
    /** @var bool */
    private bool $disabled;
    /** @var bool */
    private bool $wordpressMeta;

    /**
     * Initializing user's data, identified by multiple other data
     *
     * @param string $name
     * @param string $formType
     * @param string|null $id
     * @param string|null $description
     * @param string|null $parent
     * @param bool|null $uppercase
     * @param bool|null $required
     * @param string|null $regex
     * @param null $extraData
     * @param bool|null $disabled
     * @param bool|null
     */
    public function __construct(
        string $name, string $formType,
        ?string $id, ?string $description, ?string $parent = null,
        ?bool $uppercase = null, ?bool $required = null,
        ?string $regex = null, $extraData = null, ?bool $disabled = null,
        ?bool $wordpressMeta = null
    ) {
        parent::__construct($id, $name);
        $this->parent = $parent ?? "";
        $this->formType = $formType;
        $this->description = $description ?? "";
        $this->uppercase = $uppercase ?? false;
        $this->required = $required ?? false;
        $this->regex = $regex ?? "";
        $this->extraData = $extraData;
        $this->disabled = $disabled ?? false;
        $this->wordpressMeta = $wordpressMeta ?? false;
    }

    /**
     * @return string the form's type
     */
    public function getFormType() : string {
        return $this->formType;
    }

    /**
     * Update the old value by a new one to a given user
     *
     * @param mixed $value the new value to update
     * @param WP_User $user the user's data that should be modified
     *
     * @throws InvalidDataValueException if there was an error
     *                                   during the modification
     */
    public function updateValue($value, WP_User $user) : void {
        /*
         * Some values can be arrays of multiple values, so we stick them with a comma
         * For others, nothing changes
         */
        $value = implode(",", !is_array($value) ? [$value] : $value);

        /*
         * We are still trying to save it, some WordPress metadata appears
         * in the new ones like first_name or last_name
         */
        if (!$this->isDisabled() && metadata_exists("user", $user->ID, $this->id)) {
            update_user_meta($user->ID, $this->id, $value);
        }

        if ($this->wordpressMeta) {
            $userId = wp_update_user(["ID" => $user->ID, $this->id => $value]);
            if (is_wp_error($userId)) {
                throw new InvalidDataValueException($this->name);
            }
        }
    }

    /**
     * @return bool
     */
    public function isDisabled() : bool {
        return $this->disabled;
    }

    /**
     * @param WP_User $user the user where the new meta will be registered
     *
     * @return bool true if the meta has been registered
     */
    public function register(WP_User $user) : bool {
        if ($this->wordpressMeta) {
            return true;
        }
        if (add_user_meta($user->ID, $this->id, $this->getDefaultValue(), true) !== false) {
            return true;
        }

        return false;
    }

    /**
     * @return string the default value depending on its type
     */
    public function getDefaultValue() : string {
        $valueType = $this->getValueType();
        switch ($valueType) {
            case UserCustomDataType::VALUE_TYPE_INT:
                return "0";
            case UserCustomDataType::VALUE_TYPE_FLOAT:
                return "0.0";
            case UserCustomDataType::VALUE_TYPE_BOOL:
                return "non";
            default:
                return "";
        }
    }

    /**
     * @return int the value's type of the data
     */
    public function getValueType() : int {
        // TODO check type from json, they are still unnecessary
        //if($this->formType === "text"){
        //    return self::VALUE_TYPE_INT;
        //} else if(is_float($this->formType)){
        //    return self::VALUE_TYPE_FLOAT;
        if ($this->formType === "radio") {
            return UserCustomDataType::VALUE_TYPE_BOOL;
        }

        return UserCustomDataType::VALUE_TYPE_STRING;
    }

    /**
     * @param WP_User $user the user where the meta will be deleted
     *
     * @return bool true if the meta has been deleted
     */
    public function delete(WP_User $user) : bool {
        if ($this->wordpressMeta) {
            return true;
        }

        return delete_user_meta($user->ID, $this->id);
    }

    /**
     * @return string
     */
    public function getDescription() : string {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getParent() : string {
        return $this->parent;
    }

    /**
     * @return bool
     */
    public function isUppercase() : bool {
        return $this->uppercase;
    }

    /**
     * @return bool
     */
    public function isRequired() : bool {
        return $this->required;
    }

    /**
     * @return string
     */
    public function getRegex() : string {
        return $this->regex;
    }

    /**
     * If the regex exists, check if the values matches it
     *
     * @param string $value to check
     *
     * @return bool true if it matches
     */
    public function matches(string $value) : bool {
        return empty($this->regex) ||
            (!$this->required && empty($value)) ||
            preg_match("/^$this->regex$/", $value);
    }

    /**
     * @param string $dataToAnalyse metadata that should be checked
     *
     * @return bool true if the metadata has been found
     */
    public function containsExtraData(string $dataToAnalyse, WP_User $user) : bool {
        return in_array($dataToAnalyse, explode(",", $this->getValue($user)));
    }

    /**
     * Get the data's value
     * Special check for the WordPress original meta
     *
     * @return string data's value
     */
    public function getValue(WP_User $user) : string {
        $dataId = $this->id;

        return $this->wordpressMeta ?
            ($user->$dataId ?? "") :
            get_user_meta($user->ID, $dataId, true);
    }

    /**
     * Extra data are given in string and can't be stored in the JSON file
     * and have to be dynamically converted here
     *
     * @param $user WP_User|null
     *
     * @return array converted extra data
     */
    public function getExtraData(?WP_User $user = null) : array {
        // TODO refaire les disciplines et appliquer les manifestations
        if ($this->extraData === "user_groups") {
            return GroupFactory::getUserGroups($user);
        }

        return is_string($this->extraData) ? [] : ($this->extraData ?? []);
    }

    /**
     * @return bool
     */
    public function isWordpressMeta() : bool {
        return $this->wordpressMeta;
    }

    /**
     * @return array get all data into an array
     */
    public function toArray() : array {
        $array = parent::toArray();
        foreach (UserCustomDataType::IDS as $d) {
            $array[$d] = $this->$d;
        }

        return $array;
    }
}