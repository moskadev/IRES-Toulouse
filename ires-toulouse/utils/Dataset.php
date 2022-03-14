<?php

namespace irestoulouse\utils;

use irestoulouse\data\UserCustomData;

/**
 * Data conversion related to HTML
 *
 * @version 2.0
 */
class Dataset {

    /**
     * Convert the given user's data to an HTML format
     *
     * @param UserCustomData $d the data that should be converted
     *
     * @return string all HTML datasets
     */
    public static function allFrom(UserCustomData $d) : string {
        $datasetHtml = "";
        foreach ($d->toArray() as $name => $value) {
            $datasetHtml .= "data-$name='" . htmlspecialchars(is_array($value) ? implode(",", $value) : $value) . "' ";
        }

        return $datasetHtml;
    }
}