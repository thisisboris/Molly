<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\io\cache;

use \Molly\library\io\dataloaders\files\FileWriter;
use \Molly\library\io\dataloaders\files\File;

Class Scribe extends FileWriter {

    public function createCache($identifier, $data) {
        $file = new File($identifier . "-cache-" . time());
        $file->setLocation(Archive::CACHE_LOCATION);

        return $this->write($file, false);
    }

    public function write($file, $overwrite = true) {
        return false;
    }
}