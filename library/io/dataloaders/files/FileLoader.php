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
    private static $efl = array();

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

    public function addExpectedFileLocation($efl) {
        if (is_string($efl)) {
            self::$efl[] = $efl;
        } else {
            throw new IllegalArgumentException("Expected a string as filelocation, got " . gettype($efl));
        }
    }

    public function getExpectedFileLocations() {
        return self::$efl;
    }

    public function locate($file) {
        $efl = $this->getExpectedFileLocations();
        $found = false;
        foreach ($efl as $key => $location) {
            if (is_dir($location)) {
                if (($found = $this->search($file, $location)) !== false) {
                    return $found;
                } else{
                    continue;
                }
            } else {
                self::$efl[$key] = null;
                unset(self::$efl[$key]);
            }
        }

        if (!$found) {
            throw new FileNotFoundException();
        } else {
            return $found;
        }
    }

    private function search($file, $location) {
        $dh = opendir($location);
        while (($entry = readdir($dh)) !== false) {
            if ($entry != "." && $entry != "..") {
                if (!is_dir($entry)) {
                    if ($entry === $file) {
                        // Trim end of string so that only 1 slash remains.
                        return rtrim($location, '/') . '/';
                    } else {
                        continue;
                    }
                } else {
                    if (($found = $this->search($file, $entry)) !== false) {
                        return rtrim($location, '/') . '/' . $found;
                    } else {
                        continue;
                    }
                }
            }
        }

        return false;
    }
}