<?php
/**
 * @author Boris Wintein
 * @project molly
 */

namespace Lucy\io\dataloaders\files\interfaces;


interface File {
    /**
     * @param mixed $filename
     */


    public function setContent($content);

    public function getContent();

    public function getFilename();

    public function setFiletype($filetype);

    public function getFiletype();

    public function getLocation();

    public function getFilePath();
} 