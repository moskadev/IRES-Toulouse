<?php

namespace irestoulouse\exceptions;

use Exception;

/**
 * Called when an incorrect value has been entered
 * for a custom user's data
 *
 * @version 2.0
 */
class InvalidDataValueException extends Exception {

    /**
     * @param string $dataName the data's name
     */
    public function __construct(string $dataName) {
        parent::__construct("La donnée saisie pour $dataName est incorrecte");
    }
}