<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\utils\html;
use \Molly\library\utils\html\interfaces\DOMConstants;

use \Molly\library\io\dataloaders\files\FileLoader;
use \Molly\library\io\dataloaders\files\File;

class DOMFactory implements DOMConstants
{
    private static $singleton;

    /**
     * @param $file
     * @param $fileLocation
     * @param null $context
     * @param string $target_charset
     * @param bool $stripRN
     * @param string $defaultBRText
     * @param string $defaultSpanText
     * @return bool|DOM
     */
    public static function constructFromFile($file, $fileLocation, $context = null, $target_charset = self::DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = self::DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
        if (!isset(self::$singleton)) self::$singleton = new static();

        $loader = FileLoader::getInstance();
        $loader->addExpectedFileLocation($fileLocation);
        $file = $loader->load($file);

        $dom = new DOM($file, self::$singleton);
        $dom->setContext($context);
        $dom->setTargetCharset($target_charset);
        $dom->setStripLineBreaks($stripRN);
        $dom->setDefaultBRText($defaultBRText);
        $dom->setDefaultSpanText($defaultSpanText);
        $dom->parse();
        return $dom;
    }

    /**
     * Create DOM from a string.
     *
     * @param $html
     * @param null $context
     * @param string $target_charset
     * @param bool $stripRN
     * @param string $defaultBRText
     * @param string $defaultSpanText
     * @return DOM
     */
    public static function constructFromString($html, $context = null, $target_charset = self::DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText = self::DEFAULT_BR_TEXT, $defaultSpanText = self::DEFAULT_SPAN_TEXT) {

        if (!isset(self::$singleton)) self::$singleton = new static();

        $file = new File("template");
        $file->setLocation(rtrim(getcwd(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "templates");
        $file->setContent($html);

        $dom = new DOM($file, self::$singleton);
        $dom->setContext($context);
        $dom->setTargetCharset($target_charset);
        $dom->setStripLineBreaks($stripRN);
        $dom->setDefaultBRText($defaultBRText);
        $dom->setDefaultSpanText($defaultSpanText);
        $dom->parse();
        return $dom;
    }


    /**
     * Dumps the HTML-tree of a node in a readable format.
     *
     * @param DOMNode $node
     * @param bool $attributes
     */
    public static function dumpHTMLTree(DOMNode $node, $attributes = true) {
        $node->dump($attributes);
    }

    private function __construct(){}
}
