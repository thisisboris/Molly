<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
namespace Molly\library\out\templating;

use \Molly\library\io\dataloaders\files\FileLoader as FileLoader;
use \Molly\library\io\dataloaders\files\File as File;
use \Molly\library\exceptions\IllegalArgumentException as IllegalArgumentException;

 class Theme {
    private static $singleton;

    private $fileloader;
    private $file;

    private $template;

    public static function &getInstance() {
        if (!isset(self::$singleton)) {
            self::$singleton = new Theme();
        }
        return self::$singleton;
    }

    private function __construct() {

    }

    public function setFile($file) {
        if (!$file instanceof File) {
            throw new IllegalArgumentException("Argument must be instance of File");
        }

        $this->file = $file;
    }

     public function setFileloader($fileloader)
     {
         if (!$fileloader instanceof FileLoader) {
             throw new IllegalArgumentException("Argument must be instance of FileLoader");
         }

         $this->fileloader = $fileloader;
     }

    public function render() {
        $this->fileloader->addExpectedFileLocation($this->file->getLocation());
        $this->file = $this->fileloader->load($this->file);

        // Create the template
        $this->template = new \SimpleXMLElement($this->file->getContent());

        $templatevars = array("productlist" => "testtest");
        echo "<pre>";
        print_r($templatevars);
        $this->template = $this->parseElement($this->template, $templatevars);

        echo $this->template->asXML();
    }

     private function parseElement($element, $elementVariable = null) {
         $elementAttributes = $element->attributes();
         if (isset($elementAttributes['theme'])) {
            $themeAttribute = (string)$elementAttributes['theme'];
            if (is_string($elementVariable[$themeAttribute])) {
                $element[0] = $elementVariable[$themeAttribute];
            } else if (is_array($elementVariable[$themeAttribute])) {
                // @TODO Loop elements.
                foreach($elementVariable[$themeAttribute] as $key => $subvariable) {

                }
            }
         } else {
             foreach ($element->children() as $childElement) {
                 $childElement = $this->parseElement($childElement, $elementVariable);
             }
         }

         return $element;
     }
}