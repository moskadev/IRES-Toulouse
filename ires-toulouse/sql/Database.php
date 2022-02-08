<?php

namespace irestoulouse\sql;

class Database {

    const PREFIX = "ires_";

    /**
     * @return mixed wordpress database
     */
    public static function get(){
        global $wpdb;
        return $wpdb;
    }

    /**
     * Get all data from a table
     *
     * @param string $table
     * @return array
     */
    public static function all(string $table): array{
        return self::get()->get_results("SELECT * FROM " . self::PREFIX . $table, ARRAY_A) ?? [];
    }

    /**
     * Register a new element if it doesn't exist
     * Syntax for $values = [row_name => value_to_add]
     *
     * @param string $table the table's name
     * @param string $id element's id to register
     * @return bool true if the element has been registered or
     *              it is already registered
     */
    public static function register(string $table, string $id, array ...$values): bool{
        if(!self::exists($table, $id)){
            $wpdb = self::get();
            $stringValues = "";
            foreach ($values as $v){
                $stringValues .= self::valuef($v);
            }
            $insertDis = $wpdb->prepare("INSERT INTO " . self::PREFIX . $table .
                " ('" . implode("','", array_keys($values)) ."') VALUES 
                ($stringValues)", ...$values);
            return $wpdb->query($insertDis) > 0;
        }
        return false;
    }

    /**
     * Looking for an element with the indicated id
     *
     * @param string $table the table's name
     * @param string $id the element identifier
     * @return bool true if the same element has not been found
     */
    public static function exists(string $table, string $id): bool{
        $wpdb = self::get();
        // we prepare the query where we're looking for the same element
        $searchDis = $wpdb->prepare("SELECT id FROM " . self::PREFIX . $table .
            " WHERE id='%s'", $id);
        return $wpdb->query($searchDis) > 0;
    }

    /**
     * Delete an existing element
     *
     * @param string $table the table's name
     * @param string $id the element identifier
     * @return bool true if the element has been deleted
     */
    public static function remove(string $table, string $id): bool{
        if(self::exists($table, $id)){
            $wpdb = self::get();
            $removeDis = $wpdb->prepare(
                "DELETE FROM " . self::PREFIX . $table . " WHERE id='%s'", $id);
            return $wpdb->query($removeDis) > 0;
        }
        return false;
    }

    /**
     * @param mixed $v value to look for the type
     * @return string type for printf()
     */
    public static function valuef($v) : string{
        return "%" . ((is_int($v) ? "d" : is_float($v)) ? "f" : "s");
    }
}