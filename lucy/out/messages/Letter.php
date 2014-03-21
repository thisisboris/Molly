<?php
/**
 * This file is part of molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * molly CMS - Written by Boris Wintein
 */


namespace Lucy\out\messages;


use Lucy\http\html\DOM;
use Lucy\http\html\DOMNode;
use Lucy\io\dataloaders\files\File;
use Lucy\out\messages\abstracts\AbstractMessage;

class Letter extends AbstractMessage {

    public function printLetter() {
        $domdoc = new DOM(new File('Letter.html'));
        $node = new DOMNode($domdoc);
        $node->setTag('div');
        $node->addNodeClass('letter');
        $node->addNodeClass($this->getLevel(true));
        $node->setNodeType(DOMNode::TYPE_DEFAULT);

        $domdoc->setRootNode($node);

        $header = new DOMNode($domdoc, $node);
        $header->setTag('h2');
        $header->addNodeClass('letterhead');
        $header->setNodeType(DOMNode::TYPE_DEFAULT);

        $node->addChildNode($header);

        $headertext = new DOMNode($domdoc, $header);
        $headertext->setContent($this->getHead());

        $header->addChildNode($headertext);

        $content = new DOMNode($domdoc, $node);
        $content->setTag('p');
        $content->addNodeClass('lettercontent');
        $content->setNodeType(DOMNode::TYPE_DEFAULT);

        $node->addChildNode($content);

        $contenttext = new DOMNode($domdoc, $node);
        $contenttext->setContent($this->getContents());

        $content->addChildNode($contenttext);

        echo $domdoc;
    }
} 