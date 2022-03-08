<?php

namespace irestoulouse\elements\sql;

class Database {

    const PREFIX = "ires_";

    /**
     * @return mixed wordpress database
     */
    public static function get() {
        global $wpdb;
        return $wpdb;
    }
}