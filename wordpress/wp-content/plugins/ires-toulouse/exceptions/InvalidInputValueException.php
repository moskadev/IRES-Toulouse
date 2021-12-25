<?php

namespace irestoulouse\exceptions;

class InvalidInputValueException extends \Exception {

    /**
     * @param string $inputName
     */
    public function __construct(string $inputName) {
        parent::__construct("La valeur saisie pour $inputName est incorrecte");
    }
}