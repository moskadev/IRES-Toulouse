<?php

namespace irestoulouse\elements;

use irestoulouse\utils\Identifier;

class IresElement {

    /** @var string */
    protected string $name;

    /** @var string */
    protected string $id;

    /**
     * Initialization of the name and generation of the
     * discipline identifier
     *
     * @param string $name
     * @param string|null $id
     */
    public function __construct(string $name, ?string $id = null) {
        $this->name = $name;
        $this->id = $id ?? Identifier::fromName($name);
    }

    /**
     * @return string the name of the discipline
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string the discipline identifier
     */
    public function getId(): string {
        return $this->id;
    }

    public function toArray() : array {
        return [
            "name" => $this->getName(),
            "id" => $this->getId()
        ];
    }

}