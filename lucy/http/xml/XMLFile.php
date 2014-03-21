<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */

namespace Lucy\utils\xml;

use \Lucy\io\dataloaders\files\File as File;
use \Lucy\io\dataloaders\files\FileLoader as FileLoader;
use \Lucy\utils\xml\node\XMLNode as XMLNode;

class XMLFile
{
    private $simplexmlelement;
    private $file;

    private $rootnode;

    public function __construct(File &$file) {
        $this->file = &$file;
        $contents = $this->file->getContent();
        if (empty($contents) || !isset($contents) || $contents == "") {
            $fileloader = FileLoader::getInstance();

            $this->file = $fileloader->locate($this->file);
            $this->file = $fileloader->load($this->file);
        }

        $this->simplexmlelement = new \SimpleXMLElement($file->getContent());
        $this->build();
    }

    public function getName() {
        return $this->file->getFilename();
    }

    public function getLocation() {
        return $this->file->getLocation();
    }

    public function &getRootNode() {
        return $this->rootnode;
    }

    /**
     * Builds the XMLFile class so that it can be used to manipulate the xmlfile.
     */
    private function build() {
        $this->rootnode = new XMLNode($this->simplexmlelement);


    }

    public function output() {
        return $this->simplexmlelement->asXML();
    }
}
