<?php

namespace irestoulouse;

interface SqlRequest {

    /**
     * Get all datas from a table
     *
     * @param string $table
     * @return array
     */
    public static function all(string $table): array;

    /**
     * Register a new element if it doesn't exist
     * Syntax for $values = [row_name => value_to_add]
     *
     * @param string $table the table's name
     * @param string $id element's id to register
     * @return bool true if the element has been registered or
     *              it is already registered
     */
    public static function register(string $table, string $id, array ...$values): bool;

    /**
     * Looking for an element with the indicated id
     *
     * @param string $table the table's name
     * @param string $id the element identifier
     * @return bool true if the same element has not been found
     */
    public static function exists(string $table, string $id): bool;

    /**
     * Delete an existing element
     *
     * @param string $table the table's name
     * @param string $id the element identifier
     * @return bool true if the element has been deleted
     */
    public static function remove(string $table, string $id): bool;
}