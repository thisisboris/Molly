<?php

namespace Molly\library\utils\html;

/**
 * @class DOMNode
 * @author Boris Wintein - <hello@thisisboris.be>;
 * @description
 * This is a nice DOMNode class. It does everything you could want from a single node. Adding classes, removing classes,
 * adding attributes, removing them. I fixed some issues (I think) and made this classes more programming friendly in
 * general.
 *
 * I cleaned up these classes. Original allowed for incorrectly closed HTML-tags, I do not allow it. Forcing
 * frontenders to write correct HTML both helps them and helps us in general. Don't allow for small mistakes,
 * and they'll learn not to make them. These people are grown ups, they should deal with it.
 *
 * Other changes are the extra functions and possibility to easily loop through childnodes (since nodes
 * implement the iterator-interface) and manipulate them. Overal these classes have been tweaked to work
 * seamless with the templating engine provided by our library.
 *
 * All credit for the original parsing algorithm (which I slightly changed, so that it would work with the new
 * structure) goes to the contributors listed below. I couldn't have written anything better, so I didn't
 * reinvent the wheel. Just yet.

 *
 * Website: http://sourceforge.net/projects/simplehtmldom/
 * Acknowledge: Jose Solorzano (https://sourceforge.net/projects/php-html/)
 * Contributions by:
 *     Yousuke Kumakura (Attribute filters)
 *     Vadim Voituk (Negative indexes supports of "find" method)
 *     Antcs (Constructor with automatically load contents either text or file/url)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author S.C. Chen <me578022@gmail.com>
 * @author John Schlick
 * @author Rus Carroll
 * @version 1.5 ($Rev: 196 $)
 *
 */

use \Molly\library\utils\html\abstracts\AbstractDOMElement;
use \Molly\library\exceptions\IllegalArgumentException as IllegalArgumentException;

use \Molly\library\utils\collection\MollyArray;
use Molly\library\utils\html\exceptions\HTMLoadException;
use Molly\library\utils\html\interfaces\DOMElement;

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
        if (is_null($parent)) $this->rootnode = true;
        $this->domdocument = $domdocument;
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
        if ($this->getNodeType() == AbstractDOMElement::TYPE_PLAINTEXT) {
            return $this->rawHTML;
        } else {
            $returnvalue = '<';
            $returnvalue .= $this->tag;

            foreach ($this->attributes as $identifier => $value) {
                $returnvalue .= ' ' . $identifier . '="';
                if (is_array($value)) {
                    $temp = new MollyArray($value);
                    $returnvalue .= $temp->flatten();
                    unset($temp);
                } elseif (is_string($value)) {
                    $returnvalue .= $value;
                } elseif (is_object($value)) {
                    $returnvalue .= $value->__toString();
                }
                $returnvalue .= '"';
            }

            /**
             * @TODO Implement javascript actions the way they are implemented in Java's JSF/JSP/Expression Lang.
             */

            if (!$this->selfClosing) {
                $returnvalue .= ">";

                foreach ($this as $child) {
                    if ($child instanceof DOMNode) {
                        $returnvalue .= $child->__toString();
                    }
                }

                $returnvalue .= "</" . $this->tag . ">";

            } else {
                $returnvalue .=  "/>";
            }

            return $returnvalue;
        }
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
     * @throws \Molly\library\exceptions\IllegalArgumentException
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
     * @throws \Molly\library\exceptions\IllegalArgumentException
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

    public function setNodeType($type) {
        $this->nodetype = $type;
    }

    public function getNodeType() {
        return $this->nodetype;
    }

    public function setTag($tag) {
        $this->tag = $tag;
    }

    public function getTag() {
        return $this->tag;
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
     * Dump of a single node.
     */
    function dump_node()
    {
        echo $this->tag;
        if ($this->hasAttributes()){
            echo 'Attributes:  ';
            foreach ($this->getAttributes() as $attribute => $value) {
                echo "[$attribute] =>\"". $value .'", ';
            }
        }
        echo "\n";

        if (count($this->nodeInfo) > 0)
        {
            $array = new MollyArray($this->nodeInfo);
            echo 'Nodeinfo (';
            echo $array;
            echo ')';
        }

        if (isset($this->text))
        {
            echo " text: (" . $this->text . ")";
        }

        echo " children: " . count($this->getChildNodes());
        echo " nodes: " . count($this->getLinkedNodes());
        echo " tag_start: " . $this->tag_start;
        echo "\n";
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

    function getElementById($id) {return $this->find("#$id", 0);}
    function getElementsById($id, $idx = null) { return $this->find("#$id", $idx); }
    function getElementByTagName($name) {return $this->find($name, 0);}
    function getElementsByTagName($name, $idx=null) {return $this->find($name, $idx);}


}
