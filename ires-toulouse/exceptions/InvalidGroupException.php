<?php

namespace irestoulouse\exceptions;

use Exception;

/**
 * Called when a group has an invalid name or type entered
 *
 * @version 2.0
 */
class InvalidGroupException extends Exception {

    /**
     * @param string $name group's name
     * @param int $type group's type
     */
    public function __construct(string $name, int $type) {
        parent::__construct("Le nom du groupe $name ou le type $type est invalide");
    }
}