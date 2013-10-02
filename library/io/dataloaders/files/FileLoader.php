<?php
/**
 * FileLoader.php
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */


namespace Molly\library\io\dataloaders\files;

use Molly\library\io\dataloaders\files\exceptions\FileNotFoundException;
use Molly\Library\io\dataloaders\files\exceptions\ExpectedFileLocationsNotSetException;
use Molly\library\io\dataloaders\files\exceptions\NotAFolderException;
use Molly\library\io\dataloaders\Handler;
use Molly\library\exceptions\IllegalArgumentException;

class FileLoader extends Handler {

    const FILE_READ_BUFFER = 64;

    protected static $singleton;
    protected static $efl = array();

    public static function getInstance() {
        if (!isset($singleton)) {
            self::$singleton = new static();
        }

        return self::$singleton;
    }

    protected function __construct() {}

    public function load($file) {

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

    public function write($overwrite = true) {

    }

    public function append($data) {

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

        if (empty($efl)) {
            throw new ExpectedFileLocationsNotSetException();
        }

        $found = false;
        foreach ($efl as $key => $location) {
            $location = getcwd() . $location;

            if (is_dir($location)) {
                if (($found = $this->search($file, $location)) !== false) {
                    return $found;
                }
            } else {
                self::$efl[$key] = null;
                unset(self::$efl[$key]);
            }
        }

        if (!$found) {
            throw new FileNotFoundException("I was unable to locate the file " . $file . " in my expected locations");
        } else {
            return $found;
        }
    }

    private function search($file, $location) {
        if (is_dir($location)) {
            $dh = opendir($location);
            while (($entry = readdir($dh)) !== false) {
                if ($entry != "." && $entry != "..") {
                    if (!is_dir($location . DIRECTORY_SEPARATOR . $entry)) {
                        if ($entry === $file) {
                            // Trim end of string so that only 1 slash remains.
                            return rtrim($location, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                        }
                    } else {
                        if (($found = $this->search($file, $location . DIRECTORY_SEPARATOR . $entry)) !== false) {
                            return rtrim($found, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                        }
                    }
                }
            }
        } else {
            throw new NotAFolderException("Searching for files is only possible in folders. The given location '" . $location . "' is not a folder");
        }

        return false;
    }
}