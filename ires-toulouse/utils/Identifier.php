<?php

namespace irestoulouse\utils;

use WP_User;

class Identifier {

    /**
     * The identifier is determined by its name in lowercase.
     * To avoid encoding problems with the database, we
     * change it by ASCII.
     * Then, we replace all types of spaces by underscores.
     * Finally, only the alphanumeric characters and the
     * underscores are kept and the rest is deleted.
     *
     * @param string $string String to convert
     *
     * @return string the generated id
     */
    public static function fromName(string $string) : string {
        return str_replace("-", "_", sanitize_title($string));
    }

    public static function extractLogin(string $fullName) : string{
        if(preg_match("/^[A-ZÀ-ÿa-zà-ÿ\-_0-9]+\s[A-ZÀ-ÿa-zà-ÿ\-_0-9]+\s\([A-ZÀ-ÿa-zà-ÿ\-_0-9]+\)$/", $fullName)){
            $firstPos = stripos($fullName, "(") + 1;
            return substr($fullName, $firstPos, stripos($fullName, ")") - $firstPos);
        }
        return $fullName;
    }

    public static function generateFullName(WP_User $user) : string{
        return $user->last_name . " " . $user->first_name . " (" . $user->user_login . ")";
    }
}