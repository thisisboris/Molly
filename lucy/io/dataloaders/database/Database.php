<?php
/**
 * Database.php
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */


namespace Lucy\io\database;

use Lucy\exceptions\IllegalArgumentException;
use Lucy\io\dataloaders\database\objects\Connection;

class Database {

    private static $singleton;
    private static $method;

    const PDO = "pdo", MYSQLI = "mysqli", MYSQL = "mysql";

    private static $connections;

    public static function getInstance() {
        if (isset(self::$singleton)) {
            return self::$singleton;
        } else {
            return self::$singleton = new Database();
        }
    }

    protected function setMethod($method) {
        self::$method = $method;
    }

    public static function getMethod() {
        return self::$method;
    }

    private function __construct() {
        // Load our properties
    }

    public function createConnection($classname) {
        // Don't autoload the class. If this is a legit request, the class should already be loaded.
        if (class_exists($classname, false)) {
            if (array_key_exists($classname, self::$connections)) {
                // Let's not overdo it on the connections.
                return self::$connections[$classname];
            } else {

                return self::$connections[$classname] = new Connection($classname);
            }
        } else {
            throw new IllegalArgumentException($classname, "String");
        }
    }

}