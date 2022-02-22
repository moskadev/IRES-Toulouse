<?php

namespace irestoulouse\controllers;

use irestoulouse\elements\input\UserData;
use irestoulouse\exceptions\InvalidInputValueException;
use WP_User;

class UserInputData extends Controller {

    /** @var WP_User */
    private WP_User $user;

    public function __construct(WP_User $user) {
        $this->user = $user;
    }

    /**
     * Check each input data that needs to be verified by its regex
     * @throws InvalidInputValueException if the value doesn't match the regex
     */
    public static function checkSentData() : void {
        foreach ($_POST as $key => $value) {
            $data = UserData::fromId($key);
            if (!is_array($value) && $data !== null && !$data->matches($value)) {
                throw new InvalidInputValueException($data->getName());
            }
        }
    }
}