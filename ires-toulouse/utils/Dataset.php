<?php

namespace irestoulouse\utils;

use irestoulouse\elements\UserData;

class Dataset {

    public static function allFrom(UserData $userData) : string{
        $htmlData = "";
        foreach (UserData::DATAS as $d){
            $functions = get_class_methods($userData);
            $call = null;
            foreach ($functions as $f){
                if(stripos(str_replace(["get", "is"], "", $f), $d) === 0){
                    $call = call_user_func([$userData, $f]);
                    break;
                }
            }

            if($call !== null && !is_array($call)) {
                $htmlData .= "data-$d='" . htmlspecialchars($call) . "' ";
            }
        }
        return $htmlData;
    }
}