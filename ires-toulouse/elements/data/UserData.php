<?php

namespace irestoulouse\elements\input;

use irestoulouse\elements\IresElement;
use WP_User;

class UserData extends IresElement {

    public const VALUE_TYPE_INT = 0;
    public const VALUE_TYPE_FLOAT = 1;
    public const VALUE_TYPE_STRING = 2;
    public const VALUE_TYPE_BOOL = 3;

    public const DATAS = [
        "name",
        "formType",
        "id",
        "description",
        "parent",
        "uppercase",
        "required",
        "regex",
        "extraData",
        "disabled"
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
    /** @var array */
    private array $extraData;
    /** @var bool */
    private bool $disabled;

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
     */
    public function __construct(
        string $name, string $formType,
        ?string $id, ?string $description, ?string $parent = null,
        ?bool $uppercase = null, ?bool $required = null,
        ?string $regex = null, $extraData = null, ?bool $disabled = null
    ) {
        parent::__construct($id, $name);
        $this->parent = $parent ?? "";
        $this->formType = $formType;
        $this->description = $description ?? "";
        $this->uppercase = $uppercase ?? false;
        $this->required = $required ?? false;
        $this->regex = $regex ?? "";
        $this->extraData = $this->convertExtraData($extraData);
        $this->disabled = $disabled ?? false;
    }

    /**
     * Extra data are given in string and can't be stored in the JSON file
     * and have to be dynamically converted here
     *
     * @param $dataToConvert string|array desired data to convert
     *
     * @return array converted data
     */
    private function convertExtraData($dataToConvert) : array {
        if ($dataToConvert !== "disciplines") { // TODO groups
            return $dataToConvert ?? [];
        }
        // TODO refaire les disciplines et appliquer les groupes
        //return array_map(function ($d){
        //    return $d["name"];
        //}, Discipline::all());
        return [];
    }

    /**
     * Register all new metas for the IRES Toulouse to the user
     *
     * @param int $userId the user's id
     */
    public static function registerExtraMetas(int $userId) : void {
        foreach (self::all(false) as $m) {
            if ($m->getFormType() === "email") {
                continue;
            }
            add_user_meta($userId, $m->getId(), $m->getDefaultValue(), true);
        }
    }

    /**
     * @return UserData[] all the user's necessary data
     */
    public static function all(bool $labelIncluded = true) : array {
        $datas = [];
        $jsonData = json_decode(file_get_contents(__DIR__ . "/user_data.json"), true);
        foreach ($jsonData as $d) {
            if ($labelIncluded || $d["formType"] !== "label") {
                $datas[] = new UserData(...array_values(UserData::formatData($d)));

            }
        }

        return $datas;
    }

    /**
     * Find the user input's data from its ID
     *
     * @param string $searchedId the data identifier
     *
     * @return UserData|null the user data which can be null
     */
    public static function fromId(string $searchedId) : ?UserData {
        $filter = array_filter(self::all(), function ($a) use ($searchedId) {
            return $a->getId() === $searchedId;
        });

        return array_values($filter)[array_key_first($filter)] ?? null;
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
        foreach (self::DATAS as $valid) {
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
                return "false";
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
     * Special check for the emails which should be checked in
     * the other table of the user
     *
     * @return string input's value
     */
    public function getValue(WP_User $user) : string {
        if (get_user_meta($user->ID, $this->id, true) === false) {
            add_user_meta($user->ID, $this->id, $this->getDefaultValue(), true);
        }
        if ($this->id === "email") {
            $value = $user->user_email;
        } else {
            $value = get_user_meta($user->ID, $this->id, true);
        }

        return $value;
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
     * @return array|null
     */
    public function getExtraData() : ?array {
        return $this->extraData;
    }

    /**
     * @return bool
     */
    public function isDisabled() : bool {
        return $this->disabled;
    }

    /**
     * @return array
     */
    public function toArray() : array {
        return array_merge(parent::toArray(), [
            "parent" => $this->parent,
            "formType" => $this->formType,
            "description" => $this->description,
            "uppercase" => $this->uppercase,
            "required" => $this->required,
            "regex" => $this->regex,
            "extraData" => $this->extraData,
            "disabled" => $this->disabled
        ]);
    }
}