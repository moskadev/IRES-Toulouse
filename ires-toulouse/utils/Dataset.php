<?php

namespace irestoulouse\utils;

use irestoulouse\elements\input\UserData;

class Dataset {

    /**
     * Hacky way to get all the data
     *
     * @param UserData $userData the datas to convert
     *
     * @return string
     */
    public static function allFrom(UserData $userData) : string {
        $datasetHtml = "";
        foreach ($userData->toArray() as $name => $value) {
            $datasetHtml .= "data-$name='" . htmlspecialchars(is_array($value) ? implode(",", $value) : $value) . "' ";
        }

        return $datasetHtml;
    }
}