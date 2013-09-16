<?php
/**
 * Classloader.php
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */


namespace Molly\library\toolbelt;

use Molly\library\dataloaders\files\exceptions\FileNotFoundException;
use Molly\library\dataloaders\files\FileLoader;

class Classloader extends FileLoader {

    private function __construct() {
        // Simple classloader only tries to load our library
        $this->addExpectedFileLocation("/library");
    }

    public function autoload($class_name) {
        $file_name = $class_name . ".php";
        try {
            $location = $this->locate($file_name);
            if (file_exists($location . $file_name)) {
                include_once($location . $file_name);
            }
        } catch (FileNotFoundException $fnfe) {
            return false;
        }

        return false;
    }
}