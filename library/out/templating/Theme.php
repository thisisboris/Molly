<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
namespace Molly\library\out\templating;

require_once(getcwd() . "/library/utils/html/simple_html_dom.php");

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

    const HTML_ATTRIBUTE = "theme";

    public static function &getInstance() {
        if (!isset(self::$singleton)) {
            self::$singleton = new Theme();
        }
        return self::$singleton;
    }

    private function __construct() {

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
                    "website_name" => array("content" => "Thisisboris.be")
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

        $this->template = $this->parseElement($this->template, null, $vars);
        echo $this->template;
    }

     private function parseElement($node, $parentElement = null, $elementVariables = null) {
         if ($node instanceof \simple_html_dom) {
             foreach ($node->childNodes() as $childnode) {
                 $nodeVariables = $elementVariables;

                 if ($childnode->hasAttribute(self::HTML_ATTRIBUTE)) {
                     $childNodeAttributeValue = $childnode->getAttribute(self::HTML_ATTRIBUTE);
                    if (isset($elementVariables[$childNodeAttributeValue])) {
                        $nodeVariables = array($childNodeAttributeValue => $elementVariables[$childNodeAttributeValue]);
                    }
                 }

                 $this->parseElement($childnode, $node, $nodeVariables);
             }
         } else if ($node instanceof \simple_html_dom_node) {
             // Check if "theme"-attribute is set.
             if ($node->hasAttribute("theme") && isset($elementVariables[$node->getAttribute(self::HTML_ATTRIBUTE)])) {

                 $nodeAttributeValue = $node->getAttribute(self::HTML_ATTRIBUTE);
                 $nodeVariable = $elementVariables[$nodeAttributeValue];

                 if (is_array($nodeVariable)) {
                     $node = $this->buildElementWithArray($node, $parentElement, $nodeVariable);
                     $elementVariables = $nodeVariable;
                 } else if (is_string($nodeVariable)) {
                     $node = $this->buildElementWithString($node, $parentElement, $nodeVariable);
                     $elementVariables = null;
                 }

                 $node->removeAttribute(self::HTML_ATTRIBUTE);
             }

             foreach ($node->childNodes() as $childnode) {
                 $nodeVariables = $elementVariables;

                 if ($childnode->hasAttribute(self::HTML_ATTRIBUTE)) {
                     $childNodeAttributeValue = $childnode->getAttribute(self::HTML_ATTRIBUTE);
                     if (isset($elementVariables[$childNodeAttributeValue])) {
                         $nodeVariables = array($childNodeAttributeValue => $elementVariables[$childNodeAttributeValue]);
                     }
                 }

                 $this->parseElement($childnode, $node, $nodeVariables);
             }
         }

         return $node;
     }

    private function buildElementWithArray(\simple_html_dom_node $node, $parent, $nodeVariable) {
        $temp = new MollyArray($nodeVariable);
        if ($temp->is_assoc()) {
            if (isset($nodeVariable['attributes']) && is_array($nodeVariable['attributes'])) {
                foreach ($nodeVariable['attributes'] as $key => $value) {
                    $node->setAttribute($key, $value);
                }
                unset($nodeVariable['attributes']);
            }
        } else {
            // This node must repeated in the parent node.

        }

        return $node;
    }

    private function buildElementWithString(\simple_html_dom_node $node, \simple_html_dom_node $parent, $nodeVariable) {
        $node->setAttribute("innertext", $nodeVariable);
        return $node;
    }


}