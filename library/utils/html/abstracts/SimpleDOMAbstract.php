<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
namespace Molly\library\utils\html\abstracts;

use \Molly\library\exceptions\IllegalArgumentException;

use \Molly\library\utils\html\interfaces\SimpleDOM;
use \Molly\library\utils\html\DOMNode;
use \Molly\library\utils\html\DOM;

abstract class SimpleDOMAbstract implements SimpleDOM, \Iterator
{
    /**
     * @var DOMNode $parent
     * @description Contains reference to a possible parent DomNode element.
     */
    protected $parent;

    /**
     * @var array $children
     * @description Contains all childdomnodes.
     */
    protected $children = array();

    /**
     * @var array $linkednodes
     * @description Contains references to all linked nodes
     */
    protected $linkedNodes = array();

    /**
     * @var int $child_id
     * @description Contains an int value which is the key of the node inside the parent's $children-array.
     */
    protected $child_id;

    /**
     * @var int $loop_id
     * @description pointer for iterating the dom-element.
     */
    private $loop_id = 0;

    /**
     * Set the internal child-id pointer to the int specified with $id
     *
     * @param $id
     * @throws \Molly\library\exceptions\IllegalArgumentException
     */
    function setChildId($id) {
        if (is_int($id)) {
            $this->child_id = $id;
        } else {
            throw new IllegalArgumentException("Id must be an int");
        }
    }

    /**
     * Gets the internal child-id pointer.
     * @return mixed
     */
    function getChildId() {
        return $this->child_id;
    }

    /**
     * Creates a new HTML-element from a tag (div, section, ...) and it's content.
     *
     * @param $tag
     * @param $contents
     * @return mixed
     */
    function createElement($tag, $contents) {
        return DOM::constructFromString("<$tag>$contents</$tag>")->getFirstChild();
    }

    /**
     * Completely deletes a child from the domtree.
     *
     * @param \Molly\library\utils\html\DOMNode $node
     * @return bool
     */
    function deleteElement(DOMNode &$node) {
        if ($node->getParent() == $this) {
            $node->setAttribute("outertext", "");
            $this->removeChildNode($node);
            $node->setParent(null);
            $node->child_id = null;

            foreach ($node->getLinkedNodes() as $linkednode) {
                if ($linkednode instanceof DOMNode) {
                    $linkednode->removeLinkedNode($node);
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Creates a new element from a string.
     * @param $htmlstring
     * @return bool|\Molly\library\utils\html\DOM
     */
    function createElementFromHTML($htmlstring) {
        // Create a new HTML-element from string
        return DOM::constructFromString($htmlstring)->root;
    }

    /**
     * Returns the reference to the parent domnode.
     * @return \Molly\library\utils\html\DOMNode
     */
    function &getParent() {
        return $this->parent;
    }

    /**
     * Sets the parent of this node to the referenced node.
     * @param \Molly\library\utils\html\DOMNode &$node
     */
    function setParent(DOMNode &$node) {
        $this->parent = $node;
    }

    /**
     * Adds a linked node to this node, also updates the supplied node to contain this node.
     * @param \Molly\library\utils\html\DOMNode &$node
     * @return bool|\Molly\library\utils\html\DOMNode
     */
    function addLinkedNode(DOMNode &$node) {
        if(!in_array($node, $this->linkedNodes)) {
            $this->linkedNodes[] = $node;
            $node->addLinkedNode($this);
            return $node;
        } else {
            return false;
        }
    }

    /**
     * Returns an reference to the array containing references to all linked nodes.
     * @return array
     */
    function &getLinkedNodes() {
        return $this->linkedNodes;
    }

    /**
     * Removes a linked node from the array. Also removes self from node's linked array.
     * @param \Molly\library\utils\html\DOMNode $node
     * @return bool|\Molly\library\utils\html\DOMNode
     */
    function removeLinkedNode(DOMNode &$node) {
        if (in_array($node, $this->linkedNodes)) {
            $key = array_search($node, $this->linkedNodes);

            if ($key !== false) {
                unset($this->linkedNodes[$key]);
            }

            $node->removeLinkedNode($this);
            return $node;
        } else {
            return false;
        }
    }

    /**
     * Adds a node as child to this node. Updates the node his parent.
     * @param \Molly\library\utils\html\DOMNode $node
     * @return bool
     */
    function addChildNode(DOMNode &$node) {
        if ($node->getParent() == null) {
            $node->setParent($this);

            $this->children[] = $node;
            $id = count($this->children);
            $node->setChildId($id);

            return true;
        } else if ($node->getParent() instanceof DOMNode) {
            $node->getParent()->removeChildNode($node);

            $node->setParent($this);

            $this->children[] = $node;
            $id = count($this->children);
            $node->setChildId($id);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Removes a node from this nodes children. Also updates the parent of the node.
     * @param \Molly\library\utils\html\DOMNode $node
     * @return bool
     */
    function removeChildNode(DOMNode &$node) {
        if ($node->getParent() == $this && in_array($node, $this->children) && isset($this->children[$node->getChildId()])) {
            unset($this->children[$node->getChildId()]);
            return true;
        } else if ($node->getParent() == null) {
            // We can assume the node doesn't have a parent.
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if this node has children.
     * @return bool
     */
    function hasChildNodes() {
        return (isset($this->children) && !empty($this->children) && count($this->children) > 0);
    }

    /**
     * Get a reference to all referenced child nodes.
     * @return array
     */
    function &getChildNodes() {
        return $this->children;
    }

    /**
     * Gets a childnode from the child-array on position $id, otherwise returns current loop-node.
     * @param $id
     * @return mixed
     */
    function &getChildNode($id = -1) {
        if (!is_null($id) && $id !== -1 && is_int($id) && isset($this->children[$id])) {
            return $this->children[$id];
        } else {
            return $this->children[$this->loop_id];
        }
    }

    /**
     * Returns the first child of this node, false if non-existent.
     * @return mixed
     */
    function &getFirstChild() {
        if (isset($this->children[0]) && !empty($this->children[0])) {
            return $this->children[0];
        } else {
            return false;
        }
    }

    /**
     * Returns last child of this node, false if non-existent.
     * @return mixed
     */
    function &getLastChild() {
        if (isset($this->children[count($this->children) -1]) && !empty($this->children[count($this->children) -1])) {
            return $this->children[count($this->children) -1];
        } else {
            return false;
        }
    }

    /**
     * Iterator Functions for iterating over the child nodes. Added "previous" method to be able to return to the previous element.
     */

    function current() {
        return $this->children[$this->loop_id];
    }

    function next() {
        return isset($this->children[++$this->loop_id]);
    }

    function previous() {
        return isset($this->children[--$this->loop_id]);
    }

    function key() {
        return $this->loop_id;
    }

    function valid() {
        return isset($this->children[$this->loop_id]);
    }

    function rewind() {
        $this->loop_id = 0;
    }
}
