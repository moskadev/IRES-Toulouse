<?php

namespace irestoulouse\utils;

use irestoulouse\elements\input\UserInputData;

class Dataset {

    /**
     * Hacky way to get all the data
     * @param UserInputData $userData the datas to convert
     * @return string
     */
    public static function allFrom(UserInputData $userData) : string{
        $datasetHtml = "";
        foreach ($userData->toArray() as $name => $value){
            $datasetHtml .= "data-$name='" . htmlspecialchars(is_array($value) ? implode(",", $value) : $value) . "' ";
        }
        return $datasetHtml;
    }
}