<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Lucy\io\dataloaders\files;

use Lucy\exceptions\IllegalArgumentException;

use Lucy\io\dataloaders\abstracts\AbstractWriter;

class FileWriter extends AbstractWriter
{

    public function write(&$file, $overwrite = true) {
        if ($file instanceof File) {
            if (is_null($file->getLocation())) {
                $file->setLocation(rtrim(getcwd(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "files");
            }

            if (file_exists($file->getFilePath()) && !$overwrite) {
                return false;
            } else {
                $fh = fopen($file->getFilePath(), 'w+');
                fwrite($fh, $file->getContent());
                fclose($fh);
                return true;
            }
        } else {
            throw new IllegalArgumentException($file, "File");
        }
    }

    public function append(&$file, $data) {
        if ($file instanceof File) {
            if (is_string($data)) {
                $file->setContent($file->getContent() . $data);
                $this->write($file);
            } else {
                throw new IllegalArgumentException($data, "String");
            }
        } else {
            throw new IllegalArgumentException($file, "File");
        }
    }
}
