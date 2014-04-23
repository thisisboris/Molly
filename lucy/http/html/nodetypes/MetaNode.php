<?php
/**
 * This file is part of Molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * Molly CMS - Written by Boris Wintein
 */


namespace Lucy\http\html\nodetypes;


use Lucy\events\Event;
use Lucy\http\html\DOMNode;

class MetaNode extends DOMNode {
    public function getName() {
        // Check for meta-tag
        return $this->getAttribute('name');
    }

    public function getContent() {
        return $this->getAttribute('content');
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