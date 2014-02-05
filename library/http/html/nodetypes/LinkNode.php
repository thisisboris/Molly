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

class LinkNode extends DOMNode {
    function startParse() {
        // Send event that the metanode was created
        $this->dispatchEvent(new Event('LINKNode-created', 'LinkNode was created', $this, $this, self::EVENT_LINKNODE_CREATED));

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