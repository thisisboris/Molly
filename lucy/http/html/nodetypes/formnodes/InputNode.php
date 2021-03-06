<?php
/**
 * This file is part of Molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * Molly CMS - Written by Boris Wintein
 */


namespace Lucy\http\html\nodetypes\formnodes;


use Lucy\http\html\DOMNode;
use Lucy\http\html\nodetypes\FormNode;

class InputNode extends DOMNode {
    public function __construct(FormNode $form, $domdocument, $parent) {
        parent::__construct($domdocument, $parent);

        $this->form = $form;
        $form->addInputNode($this);
    }

    public function getName() {
        return $this->getAttribute('name');
    }

    public function setName($name) {
        return $this->setAttribute('name', $name);
    }

    public function getType() {
        return $this->getAttribute('type');
    }

    public function setType($type) {

    }

    public function getValue() {

    }

    public function setValue($value) {

    }
} 