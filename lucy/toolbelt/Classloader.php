<?php
/**
 * Classloader.php
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */


namespace Lucy\toolbelt;

// Basic requires to instantiate this class.

require_once(getcwd() . "/lucy/io/dataloaders/interfaces/Loader.php");
require_once(getcwd() . "/lucy/io/dataloaders/abstracts/AbstractLoader.php");
require_once(getcwd() . "/lucy/io/dataloaders/files/FileLoader.php");
require_once(getcwd() . "/lucy/io/dataloaders/files/exceptions/FileNotFoundException.php");
require_once(getcwd() . "/lucy/io/dataloaders/files/exceptions/ExpectedFileLocationsNotSetException.php");
require_once(getcwd() . "/lucy/exceptions/IllegalArgumentException.php");

use Lucy\io\dataloaders\files\exceptions\FileNotFoundException;
use Lucy\io\dataloaders\files\FileLoader;

class Classloader extends FileLoader {

    function __construct() {
        // Simple classloader only tries to load our lucy
        $this->addExpectedFileLocation("/lucy");
    }

    public function autoload($class_name) {
        if (strpos($class_name, '\\') !== false) {
            // Namespaced classname
            $ns = explode('\\', $class_name);

            // Check if this is our own molly-code
            if ($ns[0] == 'Molly') {
                return $this->automollyload($class_name, $ns);
            } else {
                // This is not something we'll be able to load.
                return false;
            }
        } else {
            $file_name = $class_name . ".php";

            try {
                // Try locating our file
                $location = $this->locate($file_name);

                if (file_exists($location . $file_name)) {
                    /*
                     * Wouldn't it be nice if we could do something like this here:
                     * use $location . $class_name as $class_name;
                     *
                     * Alas; dynamic use statements aren't implemented.
                     */
                    return include_once($location . $file_name);
                }
            } catch (FileNotFoundException $fnfe) {
                echo $fnfe->getMessage();
                return false;
            }

            return false;
        }
    }

    /**
     * Autoloads molly-lucy classes.
     * @param $classname - Classname of the class that should be loaded, for debugging purposes as we use the classname
     * defined by our namespace.
     * @param $namespace - Full namespace.
     * @return bool|mixed returns false on fail, or the inclusion.
     */
    private function automollyload($classname, $namespace) {
        // Unset "molly" it's not needed as a folder.
        unset($namespace[0]);

        // Glue this together with directory seperators.
        $guessed_location = implode(DIRECTORY_SEPARATOR, $namespace);

        // Make it a file by adding .php
        $guessed_location = rtrim($guessed_location, DIRECTORY_SEPARATOR) . ".php";

        // Check if this is a real file
        if (file_exists($guessed_location)) {
            return include_once($guessed_location);
        } else {
            return false;
        }
    }
}

spl_autoload_register(array(Classloader::getInstance(), 'autoload'));
