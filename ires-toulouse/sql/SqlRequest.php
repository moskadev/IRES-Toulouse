<?php

namespace irestoulouse;

interface SqlRequest {

    /**
     * Get all datas from a table
     *
     * @return array
     */
    public static function all() : array;

    /**
     * Register a new element if it doesn't exist
     * Syntax for $values = [row_name => value_to_add]
     *
     * @param string $id element's id to register
     *
     * @return bool true if the element has been registered or
     *              it is already registered
     */
    public static function register(string $id, array ...$values) : bool;

    /**
     * Looking for an element with the indicated id
     *
     * @param string $id the element identifier
     *
     * @return bool true if the same element has not been found
     */
    public static function exists(string $id) : bool;

    /**
     * Delete an existing element
     *
     * @param string $id the element identifier
     *
     * @return bool true if the element has been deleted
     */
    public static function remove(string $id) : bool;
}