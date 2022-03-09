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
        $unwanted = [
            "Š" =>"S", "š" => "s", "Ž" =>"Z", "ž" =>"z", "À" =>"A", "Á" =>"A", "Â" =>"A", 
            "Ã" =>"A", "Ä" =>"A", "Å" =>"A", "Æ" =>"A", "Ç" =>"C", "È" =>"E", "É" =>"E",
            "Ê" =>"E", "Ë" =>"E", "Ì" =>"I", "Í" =>"I", "Î" =>"I", "Ï" =>"I", "Ñ" =>"N", 
            "Ò" =>"O", "Ó" =>"O", "Ô" =>"O", "Õ" =>"O", "Ö" =>"O", "Ø" =>"O", "Ù" =>"U",
            "Ú" =>"U", "Û" =>"U", "Ü" =>"U", "Ý" =>"Y", "Þ" =>"B", "ß" =>"Ss", "à" =>"a", 
            "á" =>"a", "â" =>"a", "ã" =>"a", "ä" =>"a", "å" =>"a", "æ" =>"a", "ç" =>"c",
            "è" =>"e", "é" =>"e", "ê" =>"e", "ë" =>"e", "ì" =>"i", "í" =>"i", "î" =>"i", 
            "ï" =>"i", "ð" =>"o", "ñ" =>"n", "ò" =>"o", "ó" =>"o", "ô" =>"o", "õ" =>"o",
            "ö" =>"o", "ø" =>"o", "ù" =>"u", "ú" =>"u", "û" =>"u", "ý" =>"y", "þ" =>"b", 
            "ÿ" =>"y"
        ];
        return preg_replace(["/\s+/", "/\W+/"], ["_", ""],
            strtr(strtolower($string), $unwanted)
        );
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