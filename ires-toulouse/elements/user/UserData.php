<?php

namespace irestoulouse\elements;

class UserData extends IresElement {

    public const DATAS = ["name", "type", "id",
        "parent", "uppercase", "required", "regex",
        "extraData", "disabled"];
    public const TYPES = ["label", "text", "email",
        "checklist", "checkbox", "dropdown"];

    /**
     * @return UserData[] all the user's necessary data
     */
    public static function all(bool $labelIncluded = true) : array{
        $datas = [];
        $jsonData = json_decode(file_get_contents(__DIR__ . "/user_data.json"), true);
        foreach ($jsonData as $d){
            if($labelIncluded || $d["type"] !== "label"){
                $datas[] = new UserData(...array_values(UserData::formatData($d)));
            }
        }
        return $datas;
    }

    /**
     * Register all new metas for the IRES Toulouse to the user
     *
     * @param int $userId the user id
     */
    public static function registerExtraMetas(int $userId) : void{
        foreach (self::all(false) as $m){
            add_user_meta($userId, $m->getId(), "", true);
        }
    }

    /**
     * Find the user's data from its ID
     *
     * @param string $id the data identifier
     * @return UserData|null the user data which can be null
     */
    public static function fromId(string $id) : ?UserData{
        return array_filter(self::all(), function ($a) use ($id){
            return $a->getId() === $id;
        })[0];
    }

    /**
     * @param array $data
     * @return array
     */
    public static function formatData(array $data) : array{
        $newData = [];
        foreach (self::DATAS as $valid){
            $newData[$valid] = null; // init a value to avoid exceptions
        }
        foreach ($data as $key => $d){
            $newData[$key] = $d;
        }
        return $newData;
    }

    /** @var string */
    private string $type;
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
     * @param string $type
     * @param string|null $id
     * @param string|null $parent
     * @param bool|null $uppercase
     * @param bool|null $required
     * @param string|null $regex
     * @param null $extraData
     * @param bool|null $disabled
     */
    public function __construct(string $name, string $type,
                                ?string $id, ?string $parent = null,
                                ?bool $uppercase = null, ?bool $required = null,
                                ?string $regex = null, $extraData = null, ?bool $disabled = null) {
        parent::__construct($name, $id);
        $this->parent = $parent ?? "";
        $this->type = $type;
        $this->uppercase = $uppercase ?? false;
        $this->required = $required ?? false;
        $this->regex = $regex ?? "";
        $this->extraData = $this->analyzeData($extraData);
        $this->disabled = $disabled ?? false;
    }

    public function analyzeData($data) : array{
        if($data !== "disciplines"){ // TODO groups
            return $data ?? [];
        }
        return array_map(function ($d){
            return $d["name"];
        }, Discipline::all());
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getParent(): string {
        return $this->parent;
    }

    /**
     * @return bool
     */
    public function isUppercase(): bool {
        return $this->uppercase;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool {
        return $this->required;
    }

    /**
     * @return string
     */
    public function getRegex(): string {
        return $this->regex;
    }

    /**
     * @param string $input
     * @return bool
     */
    public function matches(string $input): bool{
        return preg_match($this->regex, $input);
    }

    /**
     * @return array|null
     */
    public function getExtraData(): ?array {
        return $this->extraData;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool {
        return $this->disabled;
    }
}