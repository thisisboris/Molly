<?php
/**
 * FileLoader.php
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */


namespace Lucy\io\dataloaders\files;

use Lucy\io\dataloaders\files\exceptions\FileNotFoundException;
use Lucy\io\dataloaders\files\exceptions\ExpectedFileLocationsNotSetException;
use Lucy\io\dataloaders\files\exceptions\NotAFolderException;
use Lucy\exceptions\IllegalArgumentException;
use Lucy\io\dataloaders\files\interfaces\File;
use Lucy\io\streams\FileInputStream;

class FileLoader {

    protected static $efl = array();

    private $stream;
    private $file;

    public function __construct(File &$file) {
        $this->stream = new FileInputStream($this->file);
    }

    public function load() {
        $fileContents = "";
        while ($this->stream->valid()) {
            $fileContents .= $this->stream->read();
        }

        $this->file->setContent($fileContents);
        return $this->file;
    }

    public function addExpectedFileLocation($efl) {
        if (is_string($efl)) {
            self::$efl[] = $efl;
        } else {
            throw new IllegalArgumentException($efl, "String");
        }
    }

    public function getExpectedFileLocations() {
        return self::$efl;
    }

    public function locate(&$file) {
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