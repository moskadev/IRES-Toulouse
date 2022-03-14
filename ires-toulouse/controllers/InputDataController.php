<?php

namespace irestoulouse\controllers;

use irestoulouse\data\UserCustomDataFactory;
use irestoulouse\exceptions\InvalidDataValueException;

/**
 * Controller only made to verify the data sent by the server
 * in $_POST and $_GET and checking if it matches some user's data
 * and their regex
 *
 * @version 2.0
 */
class InputDataController extends Controller {

    /**
     * Check each input data that needs to be verified by its regex
     * @throws InvalidDataValueException if the value doesn't match the regex
     */
    public static function checkSentData() : void {
        foreach (array_merge($_POST, $_GET) as $key => $value) {
            $data = UserCustomDataFactory::fromId($key);
            if (!is_array($value) && $data !== null && !$data->matches($value)) {
                throw new InvalidDataValueException($data->getName());
            }
        }
    }
}