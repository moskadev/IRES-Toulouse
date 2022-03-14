<?php

namespace exceptions;

use Exception;

/**
 * Called when a user hasn't been registered
 *
 * @version 2.0
 */
class FailedUserRegistrationException extends Exception {

    /**
     * @param string $firstName the user's first name
     * @param string $lastName the user's last name
     * @param string $reason the reason of this failed registration
     */
    public function __construct(string $firstName, string $lastName, string $reason) {
        parent::__construct("L'utilisateur $firstName $lastName n'a pas pu être enregistré : $reason");
    }

}