<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
namespace Molly\library\io\cache;
use \Molly\library\io\dataloaders\files\FileLoader;

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