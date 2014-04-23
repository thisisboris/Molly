<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Lucy\io\dataloaders\files;

use Lucy\io\dataloaders\abstracts\AbstractHandler;

class FileHandler extends AbstractHandler
{
    private $writer, $loader;

    // Must be public for references to work
    public $file;

    protected function __construct() {
        $this->writer = FileWriter::getInstance();
        $this->loader = FileLoader::getInstance();
    }

    function setFile(&$file) {
        $this->file = &$file;
    }

    function &getFile() {
        return $this->file;
    }

    function load(&$file)
    {
        return $this->file = &$this->loader->load($file);
    }

    function locate(&$file)
    {
        return $this->loader->locate($file);
    }

    function write(&$file, $overwrite = true)
    {
        return $this->writer->write($file, $overwrite);
    }

    function append(&$file, $data)
    {
        return $this->writer->append($file, $data);
    }
}
