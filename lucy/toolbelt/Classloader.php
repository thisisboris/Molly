<?php
/**
 * Classloader.php
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Lucy\toolbelt;

// Basic requires to instantiate this class.
require_once(realpath(dirname(__FILE__) . "/../exceptions/abstracts/AbstractException.php"));
require_once(realpath(dirname(__FILE__) . "/../exceptions/IllegalArgumentException.php"));

require_once(realpath(dirname(__FILE__) . "/../io/dataloaders/interfaces/Loader.php"));
require_once(realpath(dirname(__FILE__) . "/../io/dataloaders/abstracts/AbstractLoader.php"));
require_once(realpath(dirname(__FILE__) . "/../io/dataloaders/files/FileLoader.php"));
require_once(realpath(dirname(__FILE__) . "/../io/dataloaders/files/exceptions/FileNotFoundException.php"));
require_once(realpath(dirname(__FILE__) . "/../io/dataloaders/files/exceptions/ExpectedFileLocationsNotSetException.php"));

use Lucy\io\dataloaders\files\FileLoader;

class Classloader extends FileLoader {

    protected function __construct() {
        // Simple classloader only tries to load our library
        $this->addExpectedFileLocation("/");
    }

    public function autoload($class_name) {
        $debug = false;

        if ($debug) echo 'Looking for ' . $class_name . '<br/>';

        if (strpos($class_name, '\\') !== false) {
            if ($debug)  echo $class_name . ' has a namespace<br/>';
            // Namespaced classname
            $ns = explode('\\', $class_name);

            // Check if this is our own Molly-code
            if ($ns[0] === 'Lucy') {
                if ($debug)  echo $class_name . ' is one of Lucys classes :D<br/>';
                return $this->automollyload($class_name, $ns);
            } else {
                if ($debug)  echo $class_name . ' isn\t a Lucy class :(<br/>';
                // Maybe if we just do this, we could find what we need.
                $class_name = str_replace('\\', '/', $class_name);
                return $this->autoload($class_name);
            }
        } else {
            if ($debug)  echo 'Looking for the namespaceless ' . $class_name . '<br/>';

            foreach (explode(PATH_SEPARATOR, get_include_path()) as $includePath) {
                $file = rtrim($includePath, '/') . '/' . $class_name . '.php';
                if (file_exists($file)) {
                    if ($debug)  echo 'Found ' . $class_name . ' in the include path: ' . $includePath . '<br/>';
                    return include_once($file);
                }
            }

            if (file_exists($class_name . '.php')) {
                return include_once($class_name . '.php');
            } else {
                // This is not something we'll be able to load.
                return false;
            }
        }

        /**
         *
        else if (strpos($class_name, '_') !== false) {

            // I'm not going to load you.
            return false;
        } else {
            $file_name = $class_name . ".php";

            try {
                // Try locating our file
                $location = $this->locate($file_name);

                if (file_exists($location . $file_name)) {

                    return include_once($location . $file_name);
                }
            } catch (FileNotFoundException $fnfe) {
                echo $fnfe->getMessage();
                return false;
            }

            return false;
        }
        **/

       // Pass om loading to the next autoloader
       return false;
    }

    /**
     * Autoloads Molly-library classes.
     * @param $classname - Classname of the class that should be loaded, for debugging purposes as we use the classname
     * defined by our namespace.
     * @param $namespace - Full namespace.
     * @return bool|mixed returns false on fail, or the inclusion.
     */
    private function automollyload($classname, $namespace) {

        // Glue this together with directory seperators.
        $guessed_location = implode(DIRECTORY_SEPARATOR, $namespace);

        // Make it a file by adding .php
        $guessed_location = rtrim($guessed_location, DIRECTORY_SEPARATOR) . ".php";

        // Our Library should be in the include paths.
        return include_once($guessed_location);
    }
}

spl_autoload_register(array(Classloader::getInstance(), 'autoload'));
