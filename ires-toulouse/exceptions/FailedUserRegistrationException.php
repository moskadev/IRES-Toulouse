<?php

namespace exceptions;

use Exception;

class FailedUserRegistrationException extends Exception {

    /**
     * @param string $login
     */
    public function __construct(string $login, string $reason) {
        parent::__construct("L'utilisateur $login n'a pas pu être enregistré : $reason");
    }

}