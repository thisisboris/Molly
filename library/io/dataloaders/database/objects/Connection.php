<?php
/**
 * Connection.php
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */


namespace Molly\library\io\dataloaders\database\objects;

class Connection {
    private $classname;

    public function __construct($classname) {
        $this->classname = $classname;
    }
}