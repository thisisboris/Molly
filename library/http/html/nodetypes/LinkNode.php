<?php
/**
 * This file is part of Molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * Molly CMS - Written by Boris Wintein
 */


namespace Molly\library\http\html\nodetypes;

use Molly\library\events\Event;
use Molly\library\http\html\DOMNode;
use Molly\library\http\html\exceptions\HTMLAttributeException;
use Molly\library\io\dataloaders\files\File;
use Molly\library\io\dataloaders\files\FileLoader;
use Molly\library\utils\collection\MollyArray;

class LinkNode extends DOMNode {

    private $stylesheet;

    private $allowedRelations = array(
        "alternate","archives","author",
        "bookmark",
        "external",
        "first",
        "help",
        "icon",
        "last", "license",
        "next", "nofollow", "noreferrer",
        "pingback", "prefetch", "prev",
        "search", "sidebar", "stylesheet",
        "tag",
        "up"
    );

    function startParse() {
        // Send event that the metanode was created
        $this->dispatchEvent(new Event('LINKNode-created', 'LinkNode was created', $this, $this, self::EVENT_LINKNODE_CREATED));

        if ($this->getRelation() === 'stylesheet' && $this->getHref() !== false) {

            // Check if we can load the stylesheet.
            $temp = explode('/', $this->getHref());

            $filename = $temp[count($temp) - 1];
            $domfile = $this->getDOMDocument()->getFile();

            $fileloader = FileLoader::getInstance();
            $fileloader->addExpectedFileLocation($domfile->getLocation());

            $this->stylesheet = $fileloader->load(new File($filename));
        }

        // Return the node
        return $this;
    }

    function setRelation($relation) {
        if (in_array($relation, $this->allowedRelations)) {
            $this->setAttribute('rel', $relation);
        } else {
            throw new HTMLAttributeException("The relation-attribute of a link-tag cannot be set to " . $relation . ". Relation must be one of the following: " . implode(', ', $this->allowedRelations));
        }

    }

    function setRel($relation) {
        $this->setRelation($relation);
    }

    function getRel() {
        return $this->getRelation();
    }

    function getRelation() {
        return $this->getAttribute('rel');
    }

    /**
     * @param mixed $charset
     */
    public function setCharset($charset)
    {
        $this->setAttribute('charset', $charset);
    }

    /**
     * @return mixed
     */
    public function getCharset()
    {
        return $this->getAttribute('charset');
    }

    /**
     * @param mixed $href
     */
    public function setHref($href)
    {
        $this->setAttribute('href', $href);
    }

    /**
     * @return mixed
     */
    public function getHref()
    {
        return $this->getAttribute('href');
    }

    /**
     * @param mixed $hreflang
     */
    public function setHreflang($hreflang)
    {
        $this->setAttribute('hreflang', $hreflang);
    }

    /**
     * @return mixed
     */
    public function getHreflang()
    {
        return $this->getAttribute('hreflang');
    }

    /**
     * @param mixed $media
     */
    public function setMedia($media)
    {
        $this->setAttribute('media', $media);
    }

    /**
     * @return mixed
     */
    public function getMedia()
    {
        return $this->getAttribute('media');
    }

    /**
     * @param mixed $sizes
     */
    public function setSizes($sizes)
    {
        $this->setAttribute('sizes', $sizes);
    }

    /**
     * @return mixed
     */
    public function getSizes()
    {
        return $this->getAttribute('sizes');
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->setAttribute('type', $type);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->getAttribute('type');
    }

    protected function parse() {
        // The function parse should not be called as the parent node can completly parse a selfclosed tag
        return $this;
    }

    function __toString() {
        $link = '<link';

        foreach ($this->getAttributes() as $attribute => $value) {
            $link .= ' ' . $attribute . '="' . $value . '"';
        }

        $link .= '>';

        return $link;
    }
} 