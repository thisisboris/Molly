<?php
/**
 * FileLoader.php
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */


namespace Molly\library\dataloaders\files;

use Molly\library\dataloaders\files\exceptions\FileNotFoundException;
use Molly\library\dataloaders\Loader;
use Molly\library\exceptions\IllegalArgumentException;

class FileLoader extends Loader {

    const FILE_READ_BUFFER = 64;
    private static $singleton;

    public static function getInstance() {
        if (!isset($singleton)) {
            self::$singleton = new FileLoader();
        }

        return self::$singleton;
    }

    private function __construct() {}

    public function load($file) {
        try {
            if ($file instanceof File) {
                if ( is_null($file->getLocation()) ) {
                    $file->setLocation($this->locate($file->getFilename()));
                } else if (!file_exists($file->getLocation() . $file->getFilename())) {
                    $file->setLocation($this->locate($file->getFilename()));
                }
            } else if (is_string($file)) {
                $file = new File($file);
                $file->setLocation($this->locate($file->getFilename()));

            } else {
                throw new IllegalArgumentException("Expected String or File, got " . gettype($file) . " - " . get_class($file));
            }
        } catch (FileNotFoundException $e) {
            // File is nowhere to be found
            return false;
        }

        // Open the file, read everything, close the file.
        $fh = fopen($file->getLocation() . $file->getFilename(), 'r');
        $fileContents = "";
        while (!feof($fh)) {
            $fileContents .= fread($fh, self::FILE_READ_BUFFER);
        }
        fclose($fh);

        $file->setContent($fileContents);
        return $file;
    }

    public function locate($file) {

        return "";
    }
}