<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
namespace Molly\library\out\templating;

use \Molly\library\io\dataloaders\files\FileLoader;
use \Molly\library\io\dataloaders\files\File;

use \Molly\library\exceptions\IllegalArgumentException;

use \Molly\library\utils\collection\MollyArray;

use \Molly\library\utils\html\DOMFactory;
use \Molly\library\utils\html\DOM;
use \Molly\library\utils\html\DOMNode;
use \Molly\library\utils\html\interfaces\DOMElement;

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

    private function __construct() {}

    public function setFile(File $file) {
        if (!$file instanceof File) {
            throw new IllegalArgumentException($file, "File");
        }

        $this->file = $file;
    }

    public function setFileloader(FileLoader $fileloader){
        if (!$fileloader instanceof FileLoader) {
            throw new IllegalArgumentException($fileloader, "FileLoader");
        }

        $this->fileloader = $fileloader;
    }

    public function render() {
        $this->fileloader->addExpectedFileLocation($this->file->getLocation());
        $this->file = $this->fileloader->load($this->file);

        $this->template = new DOM($this->file);
        $this->template->startParse();

        // Run the parser.
        $this->parse();

        return $this->template;
    }

    private function parse() {
        $vars = array(
            "logo" =>
                array("attributes" => array("href" => "molly.thisisboris.be"),
                    "website_name" => array("content" => "Thisisboris.be")
                ),
            "navigation" =>
                array(
                    "navigation-item" => array(
                        array("page" => array("content" => "home", "attributes" => array("href" => "/home")))                    ,
                        array("page" => array("content" => "info", "attributes" => array("href" => "/info"))),
                        array("page" => array("content" => "blog", "attributes" => array("href" => "/blog"))),
                        array("page" => array("content" => "contact", "attributes" => array("href" => "/contact")))
                    )
                )
        );

        // Call our nifty to string method
        echo $this->template->getRootNode();
        die();
    }

    private function parseElement(DOMElement $node, DOMElement $parentElement = null, $elementVariables = null) {
        if ($node instanceof DOM) {
            // Gets root node of the dom-document, and starts the loop.
            $this->parseElement($node->getRootNode(), null, $elementVariables);
        } else if ($node instanceof DOMNode) {
            // Check if "theme"-attribute is set.
            if ($node->hasAttribute(self::HTML_ATTRIBUTE) && isset($elementVariables[$node->getAttribute(self::HTML_ATTRIBUTE)])) {

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

            foreach ($node->getChildNodes() as $childnode) {
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

    private function buildElementWithArray(DOMNode $node,DOMElement $parent, $nodeVariable) {
        $temp = new MollyArray($nodeVariable);
        if ($temp->is_assoc()) {
            if (isset($nodeVariable['attributes']) && is_array($nodeVariable['attributes'])) {
                foreach ($nodeVariable['attributes'] as $key => $value) {
                    $node->setAttribute($key, $value);
                }
                unset($nodeVariable['attributes']);
            }

            if (isset($nodeVariable['content']) && is_string($nodeVariable['content'])) {
                $node->setAttribute("innertext", $nodeVariable['content']);
                unset($nodeVariable['content']);
            }

        } else {
            //Copy the node
            foreach ($nodeVariable as $iterationVariable) {
                $copy = $node->copy();
                $copy = $copy->firstChild();
                $copy = $this->parseElement($copy, $parent, $iterationVariable);
                $parent->appendChild($copy);
            }

            // Delete the original node we copied.
            $node->clear();
        }

        return $node;
    }

}