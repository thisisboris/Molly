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

use \Molly\library\utils\collection\MollyArray as MollyArray;

use \Molly\library\utils\xml\node\XMLNode as XMLNode;
use \Molly\library\utils\xml\XMLFile as XMLFile;

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
        require_once(getcwd() . "/library/utils/html/simple_html_dom.php");
    }

    public function setFile(File $file) {
        if (!$file instanceof File) {
            throw new IllegalArgumentException("Argument must be instance of File");
        }

        $this->file = $file;
    }

    public function setFileloader(FileLoader $fileloader)
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
        $this->template = str_get_html($this->file->getContent());

        $vars = array(
            "logo" =>
                array("attributes" => array("href" => "molly.thisisboris.be"),
                    "website_name" => "Thisisboris.be"
                ),
            "navigation" =>
                array(
                    "navigation-item" => array(
                        array("content" => "home", "attributes" => array("href" => "/home")),
                        array("content" => "info", "attributes" => array("href" => "/info")),
                        array("content" => "blog", "attributes" => array("href" => "/blog")),
                        array("content" => "contact", "attributes" => array("href" => "/contact"))
                    )
                )
        );
    }

     private function parseElement(\DOMNode $node, $rootElement = null, $elementVariable = null) {

         if ($node instanceof \DOMElement) {
             echo "START DOMElement:" . $node->nodeName . "<br/>";

             if ($node->hasAttribute("theme")) {
                 echo "Node theme tag: " . $node->getAttribute("theme") . "<br/>";
                 $themeAttribute = $node->getAttribute("theme");
                 if (isset($elementVariable[$themeAttribute])) {
                    if (is_array($elementVariable[$themeAttribute])) {

                    } else if (is_string($elementVariable[$themeAttribute])) {

                    }
                 }

             } else {
                 foreach($node->childNodes as $childnode) {
                     $this->parseElement($childnode, $node, $elementVariable);
                 }
             }

             echo "STOP DOMElement:" . $node->nodeName . "<br/>";
         } else if ($node instanceof \DOMText && !$node->isElementContentWhitespace()) {
             echo "parsing DOMText:" . $node->nodeName . "<br/>";
         }

         return $node;
     }
}