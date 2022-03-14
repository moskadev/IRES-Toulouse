<?php

namespace irestoulouse\sql;

/**
 * IRES de Toulouse's database
 *
 * @version 2.0
 */
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