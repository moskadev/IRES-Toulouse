<?php

namespace irestoulouse\utils;

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
     * @return string the generated id
     */
    public static function fromName(string $string) : string {
        return preg_replace(["/\s+/", "/\W+/"], ["_", ""],
            iconv("UTF-8", "ASCII//TRANSLIT//IGNORE",
                strtolower($string)));
    }
}