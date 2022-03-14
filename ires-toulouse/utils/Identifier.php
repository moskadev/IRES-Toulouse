<?php

namespace irestoulouse\utils;

use WP_User;

/**
 * Where everything can be "identified" from an element
 *
 * @version 2.0
 */
class Identifier {

    public const REGEX_FULL_NAME = "/^[A-ZÀ-ÿa-zà-ÿ\-_0-9]+\s[A-ZÀ-ÿa-zà-ÿ\-_0-9]+\s\([A-ZÀ-ÿa-zà-ÿ\-_0-9]+\)$/";

    /**
     * From the given full name (which can be generated with generateFullName()),
     * we are extracting the login's string in brackets
     * @param string $fullName the full name from the next method
     *
     * @return string the extracted login's string in brackets
     */
    public static function extractLogin(string $fullName) : string {
        if (preg_match(self::REGEX_FULL_NAME, $fullName)) {
            $firstPos = stripos($fullName, "(") + 1;
            return substr($fullName, $firstPos, stripos($fullName, ")") - $firstPos);
        }
        return $fullName;
    }

    /**
     * Generating the user's full name from its last name, first name
     * and login.
     *
     * @param WP_User $user the user that we want his full name to
     *                      be generated
     * @return string the fully generated name from the given user
     */
    public static function generateFullName(WP_User $user) : string {
        return $user->last_name . " " . $user->first_name . " (" . $user->user_login . ")";
    }
}