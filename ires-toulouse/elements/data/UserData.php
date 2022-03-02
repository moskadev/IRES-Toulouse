<?php

namespace irestoulouse\elements\input;

use Exception;
use irestoulouse\elements\Group;
use irestoulouse\elements\IresElement;
use WP_User;

class UserData extends IresElement {

    public const VALUE_TYPE_INT = 0;
    public const VALUE_TYPE_FLOAT = 1;
    public const VALUE_TYPE_STRING = 2;
    public const VALUE_TYPE_BOOL = 3;

    public const IDS = [
        "name",
        "formType",
        "id",
        "description",
        "parent",
        "uppercase",
        "required",
        "regex",
        "extraData",
        "disabled",
        "wordpressMeta"
    ];
    public const FORM_TYPES = [
        "label",
        "text",
        "email",
        "checklist",
        "radio",
        "dropdown"
    ];
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
     * Register all new metas for the IRES Toulouse to the user
     *
     * @param int $userId the user's id
     */
    public static function registerExtraMetas(int $userId) : void {
        foreach (self::all(false) as $meta) {
            if (($user = get_userdata($userId)) !== false) {
                $meta->register($user);
            }
        }
    }

    /**
     * @return UserData[] all the user's necessary data
     */
    public static function all(bool $labelIncluded = false) : array {
        $userData = [];
        $jsonData = json_decode(file_get_contents(__DIR__ . "/user_data.json"), true);
        foreach ($jsonData as $d) {
            if ($labelIncluded || $d["formType"] !== "label") {
                $userData[] = new UserData(...array_values(UserData::formatData($d)));
            }
        }
        return $userData;
    }

    /**
     * Find the user input's data from its ID
     *
     * @param string $searchedId the data identifier
     *
     * @return UserData|null the user data which can be null
     */
    public static function fromId(string $searchedId) : ?UserData {
        $filter = array_filter(self::all(false),
            function ($a) use ($searchedId) {
                return $a->getId() === $searchedId;
            }
        );
        return array_values($filter)[0] ?? null;
    }

    /**
     * Should be used only to organize parameters when creating a new
     * UserInputData class from a JSON object
     *
     * @param array $data the data to convert
     *
     * @return array the organized data
     */
    private static function formatData(array $data) : array {
        $newData = [];
        foreach (self::IDS as $valid) {
            $newData[$valid] = null; // init a value to avoid exceptions
        }
        foreach ($data as $key => $d) {
            $newData[$key] = $d;
        }

        return $newData;
    }

    /**
     * @return string
     */
    public function getFormType() : string {
        return $this->formType;
    }

    /**
     * @return string the default value depending on its type
     */
    public function getDefaultValue() : string {
        $valueType = $this->getValueType();
        switch ($valueType) {
            case self::VALUE_TYPE_INT:
                return "0";
            case self::VALUE_TYPE_FLOAT:
                return "0.0";
            case self::VALUE_TYPE_BOOL:
                return "non";
            default:
                return "";
        }
    }

    /**
     * @return int the type of the input
     */
    public function getValueType() : int {
        // TODO check type from json
        //if($this->formType === "text"){
        //    return self::VALUE_TYPE_INT;
        //} else if(is_float($this->formType)){
        //    return self::VALUE_TYPE_FLOAT;
        if ($this->formType === "radio") {
            return self::VALUE_TYPE_BOOL;
        }

        return self::VALUE_TYPE_STRING;
    }

    /**
     * Looking for the value to put in the input
     * Special check for the WordPress original meta
     *
     * @return string input's value
     */
    public function getValue(WP_User $user) : string {
        $dataId = $this->id;
        return $this->wordpressMeta ?
            ($user->$dataId ?? "") :
            get_user_meta($user->ID, $dataId, true);
    }

    /**
     * @param $value
     * @param WP_User $user
     *
     * @throws Exception
     */
    public function updateValue($value, WP_User $user){
        /*
         * Some values can be arrays of multiple values, so we stick them with a comma
         * For others, nothing changes
         */
        $value = implode(",", !is_array($value) ? [$value] : $value);

        /*
         * We are still trying to save it, some WordPress metadata appears
         * in the new ones like first_name or last_name
         */
        if(!$this->isDisabled()) {
            update_user_meta($user->ID, $this->id, $value);
        }

        if($this->wordpressMeta) {
            $userId = wp_update_user(["ID" => $user->ID, $this->id => $value]);
            if (is_wp_error($userId)) {
                throw new Exception($user->user_login . " : " .
                    $userId->get_error_message());
            }
        }
    }

    /**
     * @param WP_User $user the user where the new meta will be registered
     *
     * @return bool true if the meta has been registered
     */
    public function register(WP_User $user) : bool {
        if($this->wordpressMeta){
            return true;
        }
        if(add_user_meta($user->ID, $this->id, $this->getDefaultValue(), true) !== false){
            return true;
        }
        return false;
    }

    /**
     * @param WP_User $user the user where the meta will be deleted
     *
     * @return bool true if the meta has been deleted
     */
    public function delete(WP_User $user) : bool{
        if($this->wordpressMeta){
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
     * @return bool true if the metadata has been found
     */
    public function containsExtraData(string $dataToAnalyse, WP_User $user) : bool {
        return in_array($dataToAnalyse, explode(",", $this->getValue($user)));
    }

    /**
     * Extra data are given in string and can't be stored in the JSON file
     * and have to be dynamically converted here
     * @param $user WP_User|null
     *
     * @return array converted extra data
     */
    public function getExtraData(?WP_User $user = null) : array {
        // TODO refaire les disciplines et appliquer les manifestations
        if($this->extraData === "user_groups"){
            return Group::getUserGroups($user);
        }
        return is_string($this->extraData) ? [] : ($this->extraData ?? []);
    }

    /**
     * @return bool
     */
    public function isDisabled() : bool {
        return $this->disabled;
    }

    /**
     * @return bool
     */
    public function isWordpressMeta() : bool {
        return $this->wordpressMeta;
    }

    /**
     * @return array
     */
    public function toArray() : array {
        $array = parent::toArray();
        foreach (self::IDS as $d){
            $array[$d] = $this->$d;
        }
        return $array;
    }
}