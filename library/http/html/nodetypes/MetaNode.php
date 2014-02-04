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

class MetaNode extends DOMNode {


    public function getName() {
        return $this->attributes['name'];
    }

    public function getContent() {
        return $this->attributes['content'];
    }

    public function setName($name) {
        $this->setAttribute('name', $name);
    }

    public function setContent($content) {
        $this->setAttribute('content', $content);
    }

    function startParse() {
        // Send event that the metanode was created
        $this->dispatchEvent(new Event('METANode-created', 'Metanode was created', $this, $this, self::EVENT_METANODE_CREATED), $this->getName() . '::' . $this->getContent());

        // Return the node
        return $this;
    }

    function parse() {
        return $this;
    }

    function __toString() {
        return '<meta name="' . $this->getName() . '" content="' . $this->getContent() . '">';
    }
} 