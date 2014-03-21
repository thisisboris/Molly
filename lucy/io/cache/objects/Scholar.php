<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */
namespace Lucy\io\cache;
use \Lucy\io\dataloaders\files\FileLoader;

class Scholar extends FileLoader {

    public function __construct(Archive $archive) {
        $this->addExpectedFileLocation(Archive::CACHE_LOCATION);
    }

    public function knows($data) {

        return $this->locate($data);
    }

    public function load($data) {
        return $data;
    }
}