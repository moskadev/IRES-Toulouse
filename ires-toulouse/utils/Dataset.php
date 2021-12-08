<?php

namespace irestoulouse\utils;

use irestoulouse\elements\UserData;

class Dataset {

    /**
     * Hacky way to get all the da
     * @param UserData $userData the datas to convert
     * @return string
     */
    public static function allFrom(UserData $userData) : string{
        $htmlData = "";
        foreach (UserData::DATAS as $d){
            $functions = get_class_methods($userData);
            $call = null;
            foreach ($functions as $f){
                // TODO change this
                if(stripos(str_replace(["get", "is"], "", $f), $d) === 0){ // completely horrible
                    $call = call_user_func([$userData, $f]);
                    break;
                }
            }

            if($call !== null && !is_array($call)) {
                $htmlData .= "data-$d='" . htmlentities($call) . "' ";
            }
        }
        return $htmlData;
    }
}