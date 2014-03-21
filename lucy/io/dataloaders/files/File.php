<?php
/**
 * File.php
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */

namespace Lucy\io\dataloaders\files;
use Lucy\exceptions\IllegalArgumentException;

class File {
    private $location;
    private $filename;
    private $filetype;
    private $content;

    public function __construct($filename) {
        $this->setFilename($filename);
    }

    /**
     * @param mixed $filename
     */
    protected function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function setContent($content) {
        if (is_string($content)) {
            $this->content = $content;
        } else {
            throw new IllegalArgumentException($content, "String");
        }
    }

    public function getContent() {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filetype
     */
    public function setFiletype($filetype)
    {
        $this->filetype = $filetype;
    }

    /**
     * @return mixed
     */
    public function getFiletype()
    {
        return $this->filetype;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function getFilePath() {
        return $this->getLocation() . $this->getFilename();
    }

}