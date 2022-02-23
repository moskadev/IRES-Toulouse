<?php

namespace irestoulouse\elements;

class IresElement {

    /** @var string */
    protected string $name;

    /** @var mixed */
    protected $id;

    /**
     * @param string $name
     * @param mixed $id
     */
    public function __construct($id, string $name = "") {
        $this->name = $name;
        $this->id = $id;
    }

    /**
     * @return string[]
     */
    public function toArray() : array {
        return [
            "name" => $this->getName(),
            "id" => $this->getId()
        ];
    }

    /**
     * @return string the element's name
     */
    public function getName() : string {
        return $this->name;
    }

    /**
     * @return mixed the element's identifier
     */
    public function getId() {
        return $this->id;
    }

}