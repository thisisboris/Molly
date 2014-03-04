<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */
namespace Molly\library\http\html\abstracts;

use Molly\library\events\abstracts\AbstractEventDispatcher;
use Molly\library\events\Event;
use \Molly\library\exceptions\IllegalArgumentException;
use Molly\library\http\html\DOM;
use Molly\library\http\html\exceptions\HTMLStructureException;
use \Molly\library\http\html\interfaces\DOMElement;

use \Molly\library\http\html\DOMNode;
use Molly\library\http\html\nodetypes\FormNode;
use Molly\library\http\html\nodetypes\formnodes\InputNode;
use Molly\library\http\html\nodetypes\LinkNode;
use Molly\library\http\html\nodetypes\MetaNode;
use Molly\library\utils\collection\MollyArray;

abstract class AbstractDOMElement extends AbstractEventDispatcher implements DOMElement, \Iterator
{
    // Events
    const EVENT_PARSING_START = 'EVENT_START_PARSE';
    const EVENT_PARSING_END = 'EVENT_STOP_PARSE';
    const EVENT_PARSING_ERROR = 'EVENT_PARSE_ERROR';

    const EVENT_PARSING_NEW_NODE = 'EVENT_PARSE_NEW_NODE';

    const EVENT_METANODE_CREATED = 'EVENT_METANODE_CREATED';
    const EVENT_LINKNODE_CREATED = 'EVENT_LINKNODE_CREATED';

    // http://www.w3schools.com/tags/
    const TYPE_COMMENT = 0;
    const TYPE_DOCTYPE = 1;
    const TYPE_SELFCLOSING = 2;
    const TYPE_DEFAULT = 3;
    const TYPE_PLAINTEXT = 4;

    public static $allowed_tags = array(
        'a', 'abbr', 'address', 'area', 'article', 'aside', 'audio',
        'b', 'base', 'bdi', 'bdo', 'blockquote', 'body', 'br', 'button',
        'canvas', 'caption', 'cite', 'code', 'col', 'colgroup', 'command',
        'datalist', 'dd', 'del', 'details', 'dfn', 'dialog', 'div', 'dl', 'dt',
        'em', 'embed',
        'fieldset', 'figcaption', 'figure', 'footer', 'form',
        'head', 'header', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'html',
        'h', 'iframe', 'img', 'input', 'ins',
        'kbd', 'keygen',
        'label', 'legend', 'li', 'link',
        'map', 'mark', 'menu', 'meta', 'meter',
        'nav', 'noscript',
        'object', 'ol', 'optgroup', 'option',' output',
        'p', 'param', 'pre', 'progress',
        'q',
        'rp', 'rt', 'ruby',
        's', 'samp', 'script', 'section', 'select', 'small', 'source', 'span', 'strong', 'style', 'sub', 'summary', 'sup',
        'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'time', 'title', 'tr', 'track',
        'u', 'ul',
        'var', 'video',
        'wbr'
    );

    // Defaults
    const DEFAULT_TARGET_CHARSET = 'UTF-8';

    /**
     * @var DOMNode $parent
     * @description Contains reference to a possible parent DomNode element.
     */
    protected $parent;

    /**
     * @var
     */
    protected $tag;

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
     * @var bool $interupted
     * bool to check whether something happened between the function call 'start-parse' and the actual parsing.
     */
    private $interupted = false;


    /**
     * @var string $serialized
     * Contains serialized self after complete parsing. If this is filled in before parsing,
     * this object is unserialized and used in stead of the parsed object.
     */
    protected $serialized = null;

    /**
     * @var $lowercase boolean
     * Should all tags be coverted to lowercase or not?
     * @note: Changed this from false to true, because I dislike uppercase tags. HTML doesn't yell.
     */
    protected $lowercase = true;

    /**
     * @var $rawHTML String
     * Contains original HTML-string.
     */
    protected $rawHTML;

    /**
     * @var $size int
     * Contains current size of the HTMLString. (Post parse)
     */
    protected $parse_size;

    /**
     * @var $original_size int
     * Contains original size of the HTMLString (Pre parse)
     */
    protected $original_size;

    /**
     * @var $cursor int
     * Current location of the parsing.
     */
    protected $cursor;

    /**
     * @var $defaultSpan
     * Contains the default span content.
     */
    protected $defaultSpan;

    /**
     * @var boolean
     * Should we strip linebreaks from the document (default: true)
     */
    protected  $stripLineBreaks = true;

    /**
     * @var string character at position $this->rawHTML[$this->cursor++];
     */
    private $character;

    /**
     * @var int $nodeType
     * The type of node.
     */
    protected $nodeType;

    /*
     * @var array $attributes
     * An array with attributes
     */
    protected $attributes = array();

    /**
     * @param $element
     * @return mixed
     * Reload the element using the data in $element.
     */
    abstract function reloadElement($element);

    /**
     *
     */
    function getTag()
    {
        switch ($this->getNodeType()) {
            case AbstractDOMElement::TYPE_SELFCLOSING:
            default:
                return $this->tag;
            break;

            case AbstractDOMElement::TYPE_PLAINTEXT:
            case AbstractDOMElement::TYPE_COMMENT:
            case AbstractDOMElement::TYPE_DOCTYPE:
                return '';
            break;
        }
    }

    function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @param String $attribute
     * @return bool
     * @throws \Molly\library\exceptions\IllegalArgumentException
     *
     * Checks whether this node has a certain attribute defined by the string $attribute
     */
    function hasAttribute($attribute) {
        if (is_string($attribute)) {
            return isset($this->attributes[$attribute]) && !empty($this->attributes[$attribute]);
        } else {
            throw new IllegalArgumentException($attribute, "String");
        }
    }

    /**
     * @param $attribute
     * @return bool|mixed
     * @throws \Molly\library\exceptions\IllegalArgumentException
     *
     * If this node has a certain attribute, defined by the string $attribute, this returns the value of that attribute.
     */
    function getAttribute($attribute) {
        if (is_string($attribute)) {
            if ($this->hasAttribute($attribute)) {
                if ($attribute == 'class') {
                    return implode(' ', $this->attributes['class']);
                } else {
                    return $this->attributes[$attribute];
                }
            } else {
                return false;
            }
        } else {
            throw new IllegalArgumentException($attribute, "String");
        }
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     * @throws \Molly\library\exceptions\IllegalArgumentException
     *
     * Sets the value of a certain attribute defined by the string $attribute to the value $value.
     *
     * @TODO: Implementing the stripping of styles (when something is added to the style tag) and the stripping of
     * @TODO: javascript functions. By defining a strict css-relation, we could use jQuery to replace the javascript
     * @TODO: and put it in the head tag. Cleaning up HTML drastically. This however, is just an idea. I have yet to
     * @TODO: check what the actual implementation would be, and how it could be done. (And if it's possible at all)
     */
    function setAttribute($attribute, $value) {
        if (is_string($attribute)) {
            switch ($attribute) {
                case 'style':
                    $this->attributes['style'] = $value;
                    return true;
                    break;

                case 'class':
                    if (!isset($this->attributes['class']) || empty($this->attributes['class'])) $this->attributes['class'] = array();

                    if (is_array($value)) {
                        $this->attributes['class'] = array_merge($this->attributes['class'], $value);
                    } else if (is_string($value)) {
                        $this->attributes['class'][] = $value;
                    } else {
                        throw new IllegalArgumentException($value, "String|Array - When setting a class, always use a string or array");
                    }

                    return true;
                    break;

                default:
                    $this->attributes[$attribute] = $value;
                    return true;
                    break;
            }
        } else {
            throw new IllegalArgumentException($attribute, "String");
        }
    }

    /**
     * @return bool
     * Checks whether this nodes has any attributes at all.
     */
    function hasAttributes() {
        return isset($this->attributes) && !empty($this->attributes);
    }

    /**
     * @return array
     * Returns all the attributes. While classes are stored internally as an array, this returns them as a string.
     */
    function getAttributes() {
        $return = $this->attributes;
        foreach ($return as $attribute => $value) {
            if (is_array($value)) {
                $temp = new MollyArray($value);
                $return[$attribute] = $temp->flatten();
            }
        }
        return $return;
    }

    /**
     * @param $attribute
     * @return bool
     * @throws \Molly\library\exceptions\IllegalArgumentException
     *
     * Removes a certain attribute defined bu the string $attribute.
     */
    function removeAttribute($attribute) {
        if (is_string($attribute)) {
            unset($this->attributes[$attribute]);
            return true;
        } else {
            throw new IllegalArgumentException($attribute, "String");
        }
    }

    /**
     * Following all __functions relate to attributes to this node. To set specific settings, or get nodes you should
     * always use the functions that were written for these parameters.
     *
     * @param $name
     * @param $value
     * @return bool
     *
     * Set a node attribute to a certain value. True when succesful, false otherwise.
     */
    function __set($name, $value) {
        return $this->setAttribute($name, $value);
    }

    /**
     * @param $name
     * @return bool
     *
     * Gets the value of the attribute specified by $name. False if not set.
     * @see _set();
     */
    function __get($name) {
        return $this->getAttribute($name);
    }

    /**
     * @param $name
     * @return bool
     *
     * Checks whether a certain attribute is set.
     * @see _set();
     */
    function __isset($name) {
        return $this->hasAttribute($name);
    }

    /**
     * @param $name
     * @return bool
     *
     * Removes a certain attribute.
     * @see _set();
     */
    function __unset($name) {
        return $this->removeAttribute($name);
    }

    function getNodeType() {
        return $this->nodeType;
    }

    function setNodeType($nodeType = self::TYPE_PLAINTEXT) {
        $this->nodeType = $nodeType;
    }

    function &getRootNode() {
        return $this->getParent()->getRootNode();
    }

    function &getDOMDocument() {
        return $this->getParent()->getDOMDocument();
    }

    function &setRootNode(DOMElement &$node) {
        return $this->getParent()->setRootNode($node);
    }

    function &getForm() {
        if (isset($this->parent)) {
            return $this->getParent()->getForm();
        } else {
            return false;
        }
    }

    /**
     * Returns the reference to the parent domnode.
     * @return \Molly\library\http\html\interfaces\DOMElement
     */
    function &getParent() {
        return $this->parent;
    }

    /**
     * Sets the parent of this node to the referenced node.
     * @param \Molly\library\http\html\interfaces\DOMElement
     */
    function setParent(DOMElement &$node) {
        $this->parent = $node;
    }

    /**
     * Adds a linked node to this node, also updates the supplied node to contain this node.
     * @param \Molly\library\http\html\DOMNode &$node
     * @return bool|\Molly\library\http\html\DOMNode
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
     * @param \Molly\library\http\html\DOMNode $node
     * @return bool|\Molly\library\http\html\DOMNode
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
     * @param \Molly\library\http\html\interfaces\DOMElement $node
     * @return bool
     * @throws HTMLStructureException
     */
    function addChildNode(DOMElement &$node) {
        if ($this->hasChildNode($node) === true) {
            throw new HTMLStructureException("DOMNode is already a child of this parent. You should clone your nodes to add copies");
        } else if ($this->getNodeType() === self::TYPE_SELFCLOSING) {
            // Selfclosing tags are unable to have children, as they close themselves.
            return false;
        } else if ($node->getParent() == null) {
            $node->setParent($this);

            $this->children[] = $node;
            $id = count($this->children);
            $node->setChildId($id);

            return true;
        } else if ($node->getParent() instanceof DOMElement) {
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
     * @param \Molly\library\http\html\interfaces\DOMElement $node
     * @return bool
     */
    function removeChildNode(DOMElement &$node) {
        if ($this->getNodeType() === self::TYPE_SELFCLOSING) {
            // Selfclosing tags are unable to have children, as they close themselves.
            return false;
        } else if ($node->getParent() == $this && in_array($node, $this->children) && isset($this->children[$node->getChildId()])) {
            unset($this->children[$node->getChildId()]);
            return true;
        } else if ($node->getParent() == null) {
            /**
             * Since we use methods to change the parent, we can assume that a node with parent == null is not related
             * by blood or anything else to this node. Unless he's a bastard.
             * if($node->getName() == 'Jon Snow') die('You know nothing Jon Snooooooow');
             */
            return true;
        } else {
            return false;
        }
    }

    function hasChildNode(DOMElement &$node) {
        return in_array($node, $this->children);
    }

    /**
     * Check if this node has children.
     * @return bool
     */
    function hasChildNodes() {
        return (!($this->getNodeType() === self::TYPE_SELFCLOSING) && isset($this->children) && !empty($this->children) && count($this->children) > 0);
    }

    /**
     * Get a reference to all referenced child nodes.
     * @return array
     */
    function &getChildNodes() {
        if ($this->getNodeType() === self::TYPE_SELFCLOSING) {
            return false;
        } else {
            return $this->children;
        }
    }

    /**
     * Gets a childnode from the child-array on position $id, otherwise returns current loop-node.
     * @param $id
     * @return mixed
     */
    function &getChildNode($id = -1) {
        if ($this->getNodeType() === self::TYPE_SELFCLOSING) {
            // Selfclosing tags are unable to have children, as they close themselves.
            return false;
        } else if (!is_null($id) && $id !== -1 && is_int($id) && isset($this->children[$id])) {
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
        if ($this->getNodeType() === self::TYPE_SELFCLOSING) {
            // Selfclosing tags are unable to have children, as they close themselves.
            return false;
        } else if (isset($this->children[0]) && !empty($this->children[0])) {
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
        if ($this->getNodeType() === self::TYPE_SELFCLOSING) {
            // Selfclosing tags are unable to have children, as they close themselves.
            return false;
        } else if (isset($this->children[count($this->children) -1]) && !empty($this->children[count($this->children) -1])) {
            return $this->children[count($this->children) -1];
        } else {
            return false;
        }
    }

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
            throw new IllegalArgumentException($id, "int");
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

    /**
     * Parsing functions
     */
    public function setContent($string) {
        $this->rawHTML = $string;
        $this->setNodeType(self::TYPE_PLAINTEXT);
    }

    protected function setRawHTML($string) {
        $this->rawHTML = $string;
        $this->original_size = strlen($string);
    }

    public function setDefaultSpanText($spantext) {
        $this->defaultSpan = $spantext;
    }


    public function getCursor() {
        return $this->cursor;
}

    /**
     * Forces an 'interruption' to the parsing, making the code recheck itself before it wrecks itself.
     * Functions that should call this: All data-injecting functions omg.
     */
    protected function interrupt() {
        $this->interupted = true;
    }

    /**
     * Checks if code execution was interrupted
     * @return bool
     */
    function isInterupted() {
        return $this->interupted;
    }

    /**
     * Restarts the parsing process. Used internally to undo faulty interruptions.
     */
    private function restart() {
        $this->interupted = false;
    }

    function startParse() {
        // Plaintext nodes can't be parsed.
        if ($this->getNodeType == self::TYPE_PLAINTEXT) return $this;

        // Send out an event to inform everyone we're going to start parsing data
        $this->dispatchEvent(new Event('DOMElement-preload', 'About to start parsing data', $this, $this, self::EVENT_PARSING_START), $this->rawHTML);

        // Set the original size
        $this->original_size = strlen($this->rawHTML);

        // Reset the cursor
        $this->cursor = 0;

        // Starts parsing, false on completion. Throws HTML-exceptions when html is invalid.
        while ($this->parse());

        // Return this object, so that the parent parser may change his internal cursor.
        return $this;
    }

    /**
     * @throws HTMLStructureException
     */
    protected function parse() {
        /**
         * This function is called for every possible element contained within this DOMElement.
         * The cursor is thrown ahead to each next character of importance, after which we check
         * if the following characters form a tag.
         *
         * If they do form a tag, a new
         */

        if ($this->stripLineBreaks) {
            $this->rawHTML = str_replace(array('\n\r', '\n', '\r'), '', $this->rawHTML);
        }

        // Check if we should interrupt
        if ($this->isInterupted()) {
            if (!is_null($this->serialized)) {
                $this->reloadElement(unserialize($this->serialized));
            } else {
                $this->restart();
            }
        }

        $node = null;

        while ($this->cursor < $this->original_size) {
            if ($this->rawHTML[$this->cursor] === '<') {

                // Get next char
                $this->character = (isset($this->rawHTML[$this->cursor + 1]) ? $this->rawHTML[$this->cursor + 1] : null);
                switch ($this->character) {
                    case '!':
                        // Check for comment tag,
                        if ($this->rawHTML[$this->cursor + 2] == '-' && $this->rawHTML[$this->cursor + 3] == '-') {

                            $node = new DOMNode($this->getDOMDocument(), $this);

                            $node->setNodeType(AbstractDOMElement::TYPE_COMMENT);
                            $node->setRawHTML(substr($this->rawHTML, $this->cursor, strpos($this->rawHTML, '>', $this->cursor)));
                            $node->setTag($node->rawHTML);

                            $this->addChildNode($node);

                            // Update the cursor
                            $this->cursor = strpos($this->rawHTML, '>', $this->cursor) + 1;
                        }
                    break;

                    case '/':
                        // Check if there is only 1 word between the cursor and the closest '>'
                        $rt = strpos($this->rawHTML, '>', $this->cursor);
                        $possible_closing_tag = substr($this->rawHTML, $this->cursor + 2, $rt - $this->cursor - 2);

                        if ( $rt !== false && !preg_match('$[\/][\w]+$', $possible_closing_tag)) {
                            if (strtolower($possible_closing_tag) == $this->getTag()) {
                                $this->cursor = $rt + 1;
                                $this->setRawHTML(substr($this->rawHTML, $this->cursor, strlen($possible_closing_tag)));
                                return false;
                            } else {
                                throw new HTMLStructureException("Malformed HTML. Found closing tag: " . $possible_closing_tag . " expected: " . $this->getTag());
                            }
                        }
                    break;

                    default:
                        $rt = strpos($this->rawHTML, '>', $this->cursor);
                        $suggested_tag = substr($this->rawHTML, $this->cursor + 1,  min($rt, strpos($this->rawHTML, ' ', $this->cursor)) - 1 - $this->cursor);
                        $full_html_tag = substr($this->rawHTML, $this->cursor, $rt + 1 - $this->cursor);

                        switch ($suggested_tag) {
                            /**
                             * Check for metatags, they have their own class.
                             */
                            case 'meta':

                                $node = new MetaNode($this->getDOMDocument(), $this);
                                $tagcontents = substr($this->rawHTML, $this->cursor + 1 + strlen($suggested_tag), $rt - $this->cursor);

                                $node->setTag('meta');
                                $node->setNodeType(AbstractDOMElement::TYPE_SELFCLOSING);

                                $this->addChildNode($node);
                                $this->getDOMDocument()->addMetaNode($node);

                                $attributes = $this->parseAttributes($tagcontents);

                                foreach ($attributes as $attribute_name => $attribute_value){
                                    $node->setAttribute($attribute_name, str_replace(array('\"','\''), '', $attribute_value));
                                }

                                $this->cursor += strlen($full_html_tag);
                                return true;

                            break;

                            case 'link':
                                $node = new LinkNode($this->getDOMDocument(), $this);
                                $tagcontents = substr($this->rawHTML, $this->cursor + 1 + strlen($suggested_tag), $rt - $this->cursor);
                                $node->setTag('link');
                                $node->setNodeType(AbstractDOMElement::TYPE_SELFCLOSING);

                                $this->addChildNode($node);
                                $this->getDOMDocument()->addLinkNode($node);

                                $attributes = $this->parseAttributes($tagcontents);

                                foreach ($attributes as $attribute_name => $attribute_value){
                                    $node->setAttribute($attribute_name, str_replace(array('\"','\''), '', $attribute_value));
                                }

                                $this->cursor += strlen($full_html_tag);
                                return true;
                            break;

                            case 'form':
                                $node = new FormNode($this->getDOMDocument(), $this);
                                $tagcontents = substr($this->rawHTML, $this->cursor + 1 + strlen($suggested_tag), $rt - $this->cursor);
                                $node->setTag('form');
                                $node->setNodeType(self::TYPE_DEFAULT);

                                $attributes = $this->parseAttributes($tagcontents);
                                foreach ($attributes as $attribute_name => $attribute_value){
                                    $node->setAttribute($attribute_name, str_replace(array('\"','\''), '', $attribute_value));
                                }

                                // Update the cursor so it's set at the end of the current started tag.
                                $this->cursor += strlen($full_html_tag);
                                // Pass along all leftover data.
                                $node->setRawHTML(substr($this->rawHTML, $this->cursor));
                                $node->startParse();

                                $this->cursor += $node->getCursor();
                                return true;
                            break;

                            case 'option':
                                if ($this->getParent()->getTag() != 'select' || $this->getParent()->getTag() != 'datalist') {
                                    throw new HTMLStructureException('Invalid Nesting, an option tag should ultimately be nested in a select or datalist tag');
                                }

                            case 'textarea':
                            case 'fieldset':
                            case 'label':
                            case 'select':
                                if ($this->getForm() === false) {
                                    throw new HTMLStructureException('Invalid Nesting, any type of forminput-tag should ultimately be nested in a form tag');
                                }
                            break;

                            case 'input':
                                if ($this->getForm() === false) {
                                    throw new HTMLStructureException('Invalid Nesting, any type of forminput-tag should ultimately be nested in a form tag');
                                }

                                $node = new InputNode($this->getForm(), $this->getDOMDocument(), $this);
                                $node->setNodeType(AbstractDOMElement::TYPE_SELFCLOSING);
                                $node->setTag(rtrim($suggested_tag, '/'));
                                $this->addChildNode($node);

                                $tagcontents = substr($this->rawHTML, $this->cursor + 1 + strlen($suggested_tag), $rt - $this->cursor);

                                $attributes = $this->parseAttributes($tagcontents);
                                foreach ($attributes as $attribute_name => $attribute_value){
                                    $node->setAttribute($attribute_name, str_replace(array('\"','\''), '', $attribute_value));
                                }

                                $node->startParse();
                                // Update the cursor so it's set at the end of the current started tag.
                                $this->cursor += strlen($full_html_tag);
                                return true;
                            break;

                            default:

                                if ($full_html_tag[strlen($full_html_tag) - 2] == '/') {
                                    if ($this instanceof DOM) {
                                        throw new HTMLStructureException("Selfclosing tags aren't allowed on the same level as the rootnode");
                                    }

                                    $node = new DOMNode($this->getDOMDocument(), $this);
                                    $node->setNodeType(AbstractDOMElement::TYPE_SELFCLOSING);
                                    $node->setTag(rtrim($suggested_tag, '/'));
                                    $this->addChildNode($node);

                                    $node->startParse();
                                    $this->cursor += strlen($full_html_tag);
                                    return true;

                                }  else if (in_array(strtolower($suggested_tag), self::$allowed_tags)) {

                                    $node = new DOMNode($this->getDOMDocument(), $this);
                                    $tagcontents = substr($this->rawHTML, $this->cursor + 1 + strlen($suggested_tag), $rt - $this->cursor);

                                    if ($this instanceof DOM) {
                                        $this->setRootNode($node);
                                    } else if ($this instanceof DOMNode) {
                                        $this->addChildNode($node);
                                    }

                                    $node->setTag($suggested_tag);
                                    $node->setNodeType(AbstractDOMElement::TYPE_DEFAULT);

                                    // Catch all attribute=value pairs.
                                    $attributes = $this->parseAttributes($tagcontents);

                                    foreach ($attributes as $attribute_name => $attribute_value){
                                        $node->setAttribute($attribute_name, str_replace(array('\"','\''), '', $attribute_value));
                                    }

                                    // Update the cursor so it's set at the end of the current started tag.
                                    $this->cursor += strlen($full_html_tag);
                                    // Pass along all leftover data.
                                    $node->setRawHTML(substr($this->rawHTML, $this->cursor));
                                    $node->startParse();

                                    if ($this->defaultSpan !== null && $node->getTag() === 'span' && !$node->hasChildNodes()) {
                                        $child = new DOMNode($this->getDOMDocument(), $node);
                                        $child->setNodeType(AbstractDOMElement::TYPE_PLAINTEXT);
                                        $child->setRawHTML($this->defaultSpan);
                                        $node->addChildNode($child);
                                    }

                                    $this->cursor += $node->getCursor();
                                    return true;
                                }

                                // Break inner default clause
                            break;
                        }

                        // Break outer default clause.
                        break;
                }

                // Treat like noise, not a tag. Move the cursor up one and return a true so the loop continues..
                $this->cursor++;
                return true;

            }  else {
                // This is plaintext between the nodes.

                $node = new DOMNode($this->getDOMDocument(), $this);
                $node->setNodeType(AbstractDOMElement::TYPE_PLAINTEXT);

                // Find closest '<'.
                $lt = strpos($this->rawHTML, '<', $this->cursor);
                $node->setRawHTML(substr($this->rawHTML, $this->cursor, $lt - $this->cursor));
                $this->addChildNode($node);
                $this->cursor = $lt;
                return true;
            }

            // To make sure we do not have never-ending loops, increment our cursor here.
            $this->cursor++;
        }

        return false;
    }

    private function parseAttributes($attributes) {
        $cursor = 0;

        $attributeName = true;
        $firstQuote = false;

        $key = "";
        $value ="";

        $result = array();

        while ($cursor < strlen($attributes)) {
            if ($attributeName) {
                if ($attributes[$cursor] == "=") {
                    $key = trim($key);
                    $attributeName = false;
                } else {
                    $key .= $attributes[$cursor];
                }
            } else {
                if (($attributes[$cursor] == "\"" || $attributes[$cursor] == "\'") && $firstQuote) {
                    $firstQuote = false;
                    $result[$key] = $value;

                    // Reset the key/value
                    $key = "";
                    $value = "";
                    $attributeName = true;

                } else if ($attributes[$cursor] == "\"" || $attributes[$cursor] == "\'") {
                    $firstQuote = true;
                } else {
                    $value .= $attributes[$cursor];
                }
            }

            $cursor++;
        }

        return $result;
    }
}
