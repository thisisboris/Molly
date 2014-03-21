<?php
/**
 * This file is part of molly, an open-source content manager.
 * 
 * This application is licensed under the Apache License, found in LICENSE.TXT
 * 
 * molly CMS - Written by Boris Wintein
 */

namespace Lucy\http\html\nodetypes;

use Lucy\http\html\DOMNode;
use Lucy\http\html\nodetypes\formnodes\InputNode;

class FormNode extends DOMNode {
    private $allowed_attribute_list = array(
        'accept', 'accept-charset', 'action', 'autocomplete',
        'enctype',
        'method',
        'name', 'novalidate',
        'target'
    );

    private $inputnodes = array();

    public function getInputValues() {
        $inputvalues = array();
        foreach ($this->getInputNodes() as $child) {
            if ($child instanceof InputNode) {
               $inputvalues[$child->getName()] = $child->getValue();
            }
        }
        return $inputvalues;
    }

    public function setInputValues($valuearray) {
        foreach ($this->getInputNodes() as $child) {
            if ($child instanceof InputNode && array_key_exists($child->getName(), $valuearray) ) {
                $child->setValue($valuearray[$child->getName()]);
            }
        }
    }

    public function getInputNodes() {
        return $this->inputnodes;
    }

    public function addInputNode(InputNode $node) {
        $this->inputnodes[] = $node;
    }

    public function removeInputNode(InputNode $node) {
        if (in_array($node, $this->inputnodes)){
            $key = array_search($node, $this->inputnodes);

            if ($key !== false) {
                unset($this->inputnodes[$key]);
            }
        }
    }

    public function &getForm() {
        return $this;
    }

    public function getAccept() {
        return $this->getAttribute('accept');
    }

    public function setAccept($accept) {
        return $this->setAttribute('accept', $accept);
    }

    public function getAcceptCharset() {
        return $this->getAttribute('accept-charset');
    }

    public function setAcceptCharset($charset) {
        return $this->setAttribute('accept-charset', $charset);
    }

    public function getAction() {
        return $this->getAttribute('action');
    }

    public function setAction($action) {
        return $this->setAttribute('action', $action);
    }

    public function getAutocomplete() {
        return $this->getAttribute('autocomplete');
    }

    public function setAutocomplete($autocomplete) {
        return $this->setAttribute('autocomplete', $autocomplete);
    }

    public function getEnctype() {
        return $this->getAttribute('enctype');
    }

    public function setEnctype($enctype) {
        return $this->setAttribute('enctype', $enctype);
    }

    public function getMethod() {
        return $this->getAttribute('method');
    }

    public function setMethod($method) {
        return $this->setAttribute('method', $method);
    }

    public function getName() {
        return $this->getAttribute('name');
    }

    public function setName($name) {
        return $this->setAttribute('name', $name);
    }

    public function getNovalidate() {
        return $this->getAttribute('novalidate');
    }

    public function setNovalidate($novalidate) {
        return $this->setAttribute('novalidate', $novalidate);
    }

    public function getTarget() {
        return $this->getAttribute('target');
    }

    public function setTarget($target) {
        return $this->setAttribute('target', $target);
    }
} 