<?php

namespace irestoulouse;

/**
 * An element can be anything relating to anything else,
 * like a data for a user, a group, a class, etc.
 *
 * @version 2.0
 */
class Element {

    /** @var string */
    protected string $name;

    /** @var mixed */
    protected $id;

    /**
     * The element is initialized with an identifier and
     * an optional name
     *
     * @param string $name the element's name
     * @param mixed $id the element's identifier
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