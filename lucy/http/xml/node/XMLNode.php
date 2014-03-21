<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */

namespace Lucy\utils\xml\node;

use \Lucy\exceptions\InvalidConstructorException as InvalidConstructorException;
use \Lucy\exceptions\IllegalArgumentException as IllegalArgumentException;

class XMLNode
{
    private $attributes;
    private $children = array();

    private $name;
    private $parent;

    public function __construct(\SimpleXMLElement &$element) {
        $this->element = $element;
        if (!is_null($this->element)) {
            $this->name = $element->getName();
            $this->setAttributes($this->element->attributes());
            $this->setChildren($this->element->children());

        } else {
            throw new InvalidConstructorException("Element must be of instance of SimpleXMLElement, got " . get_class($element));
        }
    }

    public function getName() {
        return $this->getName();
    }

    public function getContent() {
        return (string) $this->element;
    }

    public function setParent(XMLNode $parent){
        $this->parent = $parent;
    }

    private function setChildren($children) {
        foreach ($children as $value) {
            $this->children[$value->getName()] = new XMLNode($value);
            $this->children[$value->getName()]->setParent($this);
        }
    }

    public function getChildren() {
        return $this->children;
    }

    public function clearChildren() {
        $this->children = array();

    }

    private function setAttributes($attributes) {
        foreach($attributes as $key => $value) {
            $this->attributes[(string)$key] = (string) $value;
        }
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function setAttribute($attribute_name, $value) {
        $this->attributes[$attribute_name] = $value;
        $this->element->addAttribute($attribute_name, $value);
    }

    public function removeAttribute($attribute_name) {
        if (isset($this->attributes[$attribute_name])) {
            unset($this->attributes[$attribute_name]);
            $attributes = $this->element->attributes();
            unset($attributes[$attribute_name]);
        }
    }

    public function getAttribute($attribute) {
        if (is_string($attribute)) {
            return $this->attributes[$attribute];
        } else {
            throw new IllegalArgumentException($attribute, 'string');
        }
    }

}
