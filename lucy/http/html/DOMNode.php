<?php

namespace Lucy\http\html;

/**
 * @class DOMNode
 * @author Boris Wintein - <hello@thisisboris.be>;
 * @description
 *
 *
 */

use \Lucy\http\html\abstracts\AbstractDOMElement;
use \Lucy\exceptions\IllegalArgumentException as IllegalArgumentException;

use \Lucy\utils\collection\MollyArray;
use Lucy\http\html\exceptions\HTMLoadException;
use Lucy\http\html\interfaces\DOMElement;

class DOMNode extends AbstractDOMElement
{
    /**
     * @var DOM $domdocument;
     * @description Reference to the original DOMDocument
     */
    protected $domdocument;
    /**
     * @var String $tag
     * The tag of this node
     */
    protected $tag;

    /**
     * @var array $attributes
     * Contains all attributes that this node has
     */
    protected $attributes = array();

    /**
     * @var array $nodeinfo
     * Contains extra nodeinfo that can be looked up using the constant defined in the interface DOMConstants.
     * @see DOMConstants
     */
    protected $nodeInfo = array();

    /**
     * @var int $nodetype
     * The type HTMLnode
     */
    protected $nodetype;

    /**
     * @var bool $selfClosing
     * Is this a selfclosing tag (like input, br, etc.)
     */
    protected $selfClosing = false;

    /**
     * @var bool $rootnode
     * Is this the original rootnode of the DOMClass.
     */
    protected $rootnode = false;


    /**
     * @param DOMElement $domdocument
     * @param DOMElement $parent
     * Constructs this node. Every node needs a reference to the original DOM-class, to interact.
     * If the $parent is optional, if the parent is null, the node will act as a rootnode.
     */
    public function __construct(DOMElement &$domdocument, DOMElement $parent = null) {
        $this->domdocument = $domdocument;

        if (is_null($parent)) {
            $this->rootnode = true;
            $this->setParent($domdocument);
        } else {
            $this->setParent($parent);
        }
    }

    /**
     * When a node is cloned (which can happen) this function is called by the interpreter. We must make sure
     * that all children are clones, linked nodes are updated (this node should be added to their linked nodes)
     * and the parent (if set) must be updated too.
     */
    function __clone() {
        // Child nodes are references. So we need to clone each child, and overwrite their original reference with the
        // new reference.
        foreach ($this->getChildNodes() as $key => $node) {
            if ($node instanceof DOMNode) {
                $newnode = clone $node;

                // Change the parent.
                $newnode->setParent($this);

                // So we simply override them with their clones.
                $this->children[$key] = &$newnode;
            }
        }

        // Update the linked nodes by adding this node as a new node to them.
        foreach ($this->getLinkedNodes() as $node) {
            if ($node instanceof DOMNode) {
                $node->addLinkedNode($this);
            }
        }
    }

    /**
     * @return string
     * The __toString() method recreates the node a html-string.
     */
    function __toString() {
        $returnval = "";
        switch ($this->getNodeType()) {
            default:
            case AbstractDOMElement::TYPE_DEFAULT:
                $returnval = '<';
                $returnval .= $this->getTag();

                // Now at all attributes
                foreach ($this->getAttributes() as $identifier => $value) {
                    $returnval .= ' ' . $identifier . '="';
                    if (is_array($value)) {
                        $temp = new MollyArray($value);
                        $returnval .= $temp->flatten();
                        unset($temp);
                    } elseif (is_string($value)) {
                        $returnval .= $value;
                    } elseif (is_object($value)) {
                        $returnval .= $value->__toString();
                    }
                    $returnval .= '"';

                }
                $returnval .= '>';

                // Loop all kids!
                if ($this->hasChildNodes()) {
                    foreach ($this as $child) {
                        $returnval .= $child;
                    }
                }

                $returnval .= '</';
                $returnval .= $this->getTag() . '>';

            break;

            case AbstractDOMElement::TYPE_SELFCLOSING:
                $returnval .= '<';
                $returnval .= $this->getTag();
                // Now at all attributes
                foreach ($this->getAttributes() as $identifier => $value) {
                    $returnval .= ' ' . $identifier . '="';
                    if (is_array($value)) {
                        $temp = new MollyArray($value);
                        $returnval .= $temp->flatten();
                        unset($temp);
                    } elseif (is_string($value)) {
                        $returnval .= $value;
                    } elseif (is_object($value)) {
                        $returnval .= $value->__toString();
                    }
                    $returnval .= '"';

                }
                $returnval .= "/>";
            break;

            case AbstractDOMElement::TYPE_PLAINTEXT:
                $returnval .= $this->rawHTML;
            break;

            case AbstractDOMElement::TYPE_COMMENT:
                $returnval .= "<!---";
                $returnval .= $this->rawHTML;
                $returnval .= "--->";

            break;

            case AbstractDOMElement::TYPE_DOCTYPE:

            break;
        }

        return $returnval;
    }

    /**
     * Alias for the toString method.
     * @return string
     * @see __toString();
     */
    function render() {
        return $this->__toString();
    }

    function __destroy() {
        if (!is_null($this->children)) {
            $this->destroyNode();
        }
    }

    /**
     * Destroy the node completely, removes all references and cleans up memory.
     *
     * @return DOMNode $this; (Unset it).
     */
    function destroyNode() {
        // Remove all children.
        if ($this->hasChildNodes()){
            foreach ($this->getChildNodes() as $node) {
                if ($node instanceof DOMNode) {
                    $node->destroyNode();
                }
            }
        }

        // Remove all linked nodes.
        if ($this->getLinkedNodes() != null) {
            foreach ($this->getLinkedNodes() as $node) {
                if ($node instanceof DOMNode) {
                    $this->removeLinkedNode($node);
                    $node->destroyNode();
                }
            }
        }

        // Remove itself from the parent.
        if ($this->getParent() != null) {
            $this->getParent()->removeChildNode($this);
        }

        // Remove reference to DOMDocument
        $this->domdocument = null;

        // Unset all attributes
        $this->attributes = null;

        // Unset all children
        $this->children = null;

        // Unset all linked nodes
        $this->linkedNodes = null;

        return $this;
    }

    /**
     * @return bool
     * Checks whether the ID attribute is set.
     */
    function hasNodeID() {
        return $this->hasAttribute('id');
    }

    /**
     * @return bool|mixed
     * Gets the value of the ID-attribute. False if it's not set.
     */
    function getNodeID() {
        if ($this->hasAttribute('id')) {
            return $this->attributes['id'];
        } else {
            return false;
        }
    }

    /**
     * @return bool
     * Checks whether this node has any classes.
     */
    function hasNodeClasses() {
        return isset($this->attributes['class']) && is_array($this->attributes['class']) && count($this->attributes['class']) > 0;
    }

    /**
     * @return array
     * Gets an array of all classes.
     */
    function getNodeClasses() {
        return $this->attributes['class'];
    }

    /**
     * @param $class string
     * @throws \Lucy\exceptions\IllegalArgumentException
     *
     * Adds a (string of) class(es) to this node.
     */
    function addNodeClass($class) {
        if (is_string($class)) {
            $classes = explode(" ", $class);
            $this->setAttribute('class', $classes);
        } else {
            throw new IllegalArgumentException($class, "String");
        }

    }

    /**
     * @return string
     * Gets the class attribute as a string.
     */
    function getNodeClass() {
        return implode(" ", $this->attributes["class"]);
    }

    /**
     * @param $array
     * @throws \Lucy\exceptions\IllegalArgumentException
     *
     * Sets the class to this array of classes. This function completely overwrites the previous classes.
     */
    function setNodeClasses($array)  {
        if (is_array($array)) {
            $this->removeAttribute("class");
            foreach ($array as $value) {
                if (is_string($value)) {
                    $this->setAttribute("class", $value);
                }
            }
        } else {
            throw new IllegalArgumentException($array, "Array");
        }
    }

    /**
     * Dump this node's and it's childnodes tree.
     *
     * @param bool $show_attributes
     * @param int $depth
     */
    function dump($show_attributes = true, $depth = 0)
    {
        $lead = str_repeat('    ', $depth);

        echo $lead . $this->tag;
        if ($show_attributes && $this->hasAttributes()){
            echo 'Attributes:  ';
            foreach ($this->getAttributes() as $attribute => $value) {
                echo "[$attribute] =>\"". $value .'", ';
            }
        }
        echo "\n";

        if ($this->hasChildNodes()) {
            foreach ($this->getChildNodes() as $childNode) {
                if ($childNode instanceof DOMNode) {
                    $childNode->dump($show_attributes, ++$depth);
                }
            }
        }
    }


    /**
     * @return DOMNode|bool
     * Returns a reference to the next sibling node, by getting it from the parentreference.
     */
    function &getNextSibling()
    {
        return $this->getParent()->getChildNode($this->getChildId() + 1);
    }

    /**
     * @return DOMNode|bool
     * Returns a reference to the previous sibling node, by getting it from the parentreference.
     */
    function &getPreviousSibling()
    {
        return $this->getParent()->getChildNode($this->getChildId() - 1);
    }

    function reloadElement($element)
    {
        if ($element instanceof DOMNode) {

        } else {
            throw new HTMLoadException($element);
        }
    }
}
