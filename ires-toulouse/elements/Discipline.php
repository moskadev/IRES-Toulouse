<?php

namespace irestoulouse\elements;

use irestoulouse\Database;

/**
 * A discipline is only represented by its name and its id
 */
class Discipline extends IresElement{

    /**
     * the table of disciplines
     */
    const REQUEST_TABLE = "disciplines";

    /**
     * @return array all disciplines
     */
    public static function all() : array{
        return Database::all(self::REQUEST_TABLE);
    }

    /**
     * Register a discipline into the database
     * @param Discipline $d the discipline to register
     * @return bool true if it has been registered
     */
    public static function register(Discipline $d) : bool{
        return Database::register(
            self::REQUEST_TABLE, $d->getId(), $d->toArray());
    }

    /**
     * Remove a discipline that has been registered
     * @param string $id the discipline's identifier
     * @return bool true if it has been correctly removed
     */
    public static function remove(string $id) : bool{
        return Database::remove(self::REQUEST_TABLE, $id);
    }

    /**
     * Check if the corresponding discipline's identifier exists
     * @param string $id the identifier to search
     * @return bool true if it has been found
     */
    public static function exists(string $id) : bool{
        return Database::exists(self::REQUEST_TABLE, $id);
    }
}