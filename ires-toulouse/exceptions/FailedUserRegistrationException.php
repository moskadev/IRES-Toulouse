<?php

namespace exceptions;

use Exception;

class FailedUserRegistrationException extends Exception {

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $reason
     */
    public function __construct(string $firstName, string $lastName, string $reason) {
        parent::__construct("L'utilisateur $firstName $lastName n'a pas pu être enregistré : $reason");
    }

}