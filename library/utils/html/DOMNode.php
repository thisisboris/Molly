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
    protected $nodetype = DOM::HDOM_TYPE_TEXT;

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

    public $tag_start = 0;

    /**
     * @param DOM $domdocument
     * @param null $parent
     * Constructs this node. Every node needs a reference to the original DOM-class, to interact.
     * If the $parent is optional, if the parent is null, the node will act as a rootnode.
     */
    public function __construct(DOM &$domdocument, $parent = null) {
        if (is_null($parent)) $this->rootnode = true;
        $this->domdocument = $domdocument;
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
        /*
        switch ($name) {
            case 'outertext': return isset($this->_[DOM::HDOM_INFO_OUTER]);
            case 'innertext': return true;
            case 'plaintext': return true;
            default:
                return $this->hasAttribute($name);
            break;
        }*/
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
        foreach ($this->getChildNodes() as $node) {
            if ($node instanceof DOMNode) {
                $node->destroyNode();
            }
        }

        // Remove all linked nodes.
        foreach ($this->getLinkedNodes() as $node) {
            if ($node instanceof DOMNode) {
                $this->removeLinkedNode($node);
                $node->destroyNode();
            }
        }

        // Remove itself from the parent.
        $this->getParent()->removeChildNode($this);

        // Remove parent reference from this object.
        $this->setParent(null);

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
     * @param null $specific
     * @return array|mixed|bool
     *
     * Gets either the full info array or a specific value out of the info array. Returns false when the value isn't set.
     */
    public function getInfo($specific = null) {
        if (is_null($specific)) {
            return $this->nodeInfo;
        } else if (isset($this->nodeInfo[$specific])) {
            return $this->nodeInfo[$specific];
        } else {
            return false;
        }
    }

    public function addInfo($key, $value) {
        $this->nodeInfo[$key] = $value;
    }


    /**
     * @param String $attribute
     * @return bool
     * @throws \Molly\library\exceptions\IllegalArgumentException
     *
     * Checks whether this node has a certain attribute defined by the string $attribut
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

                // @TODO check importance of the innertext/outertext/plaintext vars, and if they should be implemented here.

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
        if (isset($return['class'])) {
            $return['class'] = implode(" ", $return['class']);
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




    // find elements by css selector
    //PaperG - added ability for find to lowercase the value of the selector.
    function find($selector, $idx = null, $lowercase = false)
    {
        $selectors = $this->parse_selector($selector);
        if (($count=count($selectors)) === 0) return array();
        $found_keys = array();

        // find each selector
        for ($c = 0; $c < $count; ++$c)
        {
            // The change on the below line was documented on the sourceforge code tracker id 2788009
            // used to be: if (($levle=count($selectors[0]))===0) return array();
            if (($levle=count($selectors[$c]))===0) return array();
            if (!isset($this->_[HDOM_INFO_BEGIN])) return array();

            $head = array($this->_[HDOM_INFO_BEGIN]=>1);

            // handle descendant selectors, no recursive!
            for ($l=0; $l<$levle; ++$l)
            {
                $ret = array();
                foreach ($head as $k=>$v)
                {
                    $node = ($k===-1) ? $this->domdocument->getRootNode() : $this->domdocument->getRootNode()->getChildNode($k);
                    if ($node instanceof DOMNode) {
                        $node->seek($selectors[$c][$l], $ret, $lowercase);
                    }
                }
                $head = $ret;
            }

            foreach ($head as $k=>$v)
            {
                if (!isset($found_keys[$k]))
                    $found_keys[$k] = 1;
            }
        }

        // sort keys
        ksort($found_keys);

        $found = array();
        foreach ($found_keys as $k=>$v)
            $found[] = $this->domdocument->getRootNode()->getChildNodes($k);

        // return nth-element or array
        if (is_null($idx)) return $found;
        else if ($idx<0) $idx = count($found) + $idx;
        return (isset($found[$idx])) ? $found[$idx] : null;
    }

    // seek for given conditions
    // PaperG - added parameter to allow for case insensitive testing of the value of a selector.
    // @TODO check these functions to see what should be changed so that they still work.
    protected function seek($selector, &$ret, $lowercase=false)
    {
        list($tag, $key, $val, $exp, $no_key) = $selector;

        // xpath index
        if ($tag && $key && is_numeric($key))
        {
            $count = 0;
            foreach ($this->children as $c)
            {
                if ($tag==='*' || $tag===$c->tag) {
                    if (++$count==$key) {
                        $ret[$c->_[HDOM_INFO_BEGIN]] = 1;
                        return;
                    }
                }
            }
            return;
        }

        $end = (!empty($this->_[HDOM_INFO_END])) ? $this->_[HDOM_INFO_END] : 0;
        if ($end==0) {
            $parent = $this->parent;
            while (!isset($parent->_[HDOM_INFO_END]) && $parent!==null) {
                $end -= 1;
                $parent = $parent->parent;
            }
            $end += $parent->_[HDOM_INFO_END];
        }

        for ($i=$this->_[HDOM_INFO_BEGIN]+1; $i<$end; ++$i) {
            $node = $this->dom->nodes[$i];

            $pass = true;

            if ($tag==='*' && !$key) {
                if (in_array($node, $this->children, true))
                    $ret[$i] = 1;
                continue;
            }

            // compare tag
            if ($tag && $tag!=$node->tag && $tag!=='*') {$pass=false;}
            // compare key
            if ($pass && $key) {
                if ($no_key) {
                    if (isset($node->attr[$key])) $pass=false;
                } else {
                    if (($key != "plaintext") && !isset($node->attr[$key])) $pass=false;
                }
            }
            // compare value
            if ($pass && $key && $val  && $val!=='*') {
                // If they have told us that this is a "plaintext" search then we want the plaintext of the node - right?
                if ($key == "plaintext") {
                    // $node->plaintext actually returns $node->text();
                    $nodeKeyValue = $node->text();
                } else {
                    // this is a normal search, we want the value of that attribute of the tag.
                    $nodeKeyValue = $node->attr[$key];
                }

                //PaperG - If lowercase is set, do a case insensitive test of the value of the selector.
                if ($lowercase) {
                    $check = $this->match($exp, strtolower($val), strtolower($nodeKeyValue));
                } else {
                    $check = $this->match($exp, $val, $nodeKeyValue);
                }

                // handle multiple class
                if (!$check && strcasecmp($key, 'class')===0) {
                    foreach (explode(' ',$node->attr[$key]) as $k) {
                        // Without this, there were cases where leading, trailing, or double spaces lead to our comparing blanks - bad form.
                        if (!empty($k)) {
                            if ($lowercase) {
                                $check = $this->match($exp, strtolower($val), strtolower($k));
                            } else {
                                $check = $this->match($exp, $val, $k);
                            }
                            if ($check) break;
                        }
                    }
                }
                if (!$check) $pass = false;
            }
            if ($pass) $ret[$i] = 1;
            unset($node);
        }
    }

    protected function match($exp, $pattern, $value) {
        switch ($exp) {
            case '=':
                return ($value===$pattern);
            case '!=':
                return ($value!==$pattern);
            case '^=':
                return preg_match("/^".preg_quote($pattern,'/')."/", $value);
            case '$=':
                return preg_match("/".preg_quote($pattern,'/')."$/", $value);
            case '*=':
                if ($pattern[0]=='/') {
                    return preg_match($pattern, $value);
                }
                return preg_match("/".$pattern."/i", $value);
        }
        return false;
    }

    protected function parse_selector($selector_string) {

        // pattern of CSS selectors, modified from mootools
        // Paperg: Add the colon to the attrbute, so that it properly finds <tag attr:ibute="something" > like google does.
        // Note: if you try to look at this attribute, yo MUST use getAttribute since $dom->x:y will fail the php syntax check.
        // Notice the \[ starting the attbute?  and the @? following?  This implies that an attribute can begin with an @ sign that is not captured.
        // This implies that an html attribute specifier may start with an @ sign that is NOT captured by the expression.
        // farther study is required to determine of this should be documented or removed.
        // $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
        $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
        preg_match_all($pattern, trim($selector_string).' ', $matches, PREG_SET_ORDER);

        $selectors = array();
        $result = array();
        //print_r($matches);

        foreach ($matches as $m) {
            $m[0] = trim($m[0]);
            if ($m[0]==='' || $m[0]==='/' || $m[0]==='//') continue;
            // for browser generated xpath
            if ($m[1]==='tbody') continue;

            list($tag, $key, $val, $exp, $no_key) = array($m[1], null, null, '=', false);
            if (!empty($m[2])) {$key='id'; $val=$m[2];}
            if (!empty($m[3])) {$key='class'; $val=$m[3];}
            if (!empty($m[4])) {$key=$m[4];}
            if (!empty($m[5])) {$exp=$m[5];}
            if (!empty($m[6])) {$val=$m[6];}

            // convert to lowercase
            if ($this->dom->lowercase) {$tag=strtolower($tag); $key=strtolower($key);}
            //elements that do NOT have the specified attribute
            if (isset($key[0]) && $key[0]==='!') {$key=substr($key, 1); $no_key=true;}

            $result[] = array($tag, $key, $val, $exp, $no_key);
            if (trim($m[7])===',') {
                $selectors[] = $result;
                $result = array();
            }
        }
        if (count($result)>0)
            $selectors[] = $result;
        return $selectors;
    }



    // PaperG - Function to convert the text from one character set to another if the two sets are not the same.
    function convert_text($text) {
        $converted_text = $text;

        $sourceCharset = "";
        $targetCharset = "";

        if ($this->dom) {
            $sourceCharset = strtoupper($this->domdocument->_charset);
            $targetCharset = strtoupper($this->domdocument->_target_charset);
        }

        if (!empty($sourceCharset) && !empty($targetCharset) && (strcasecmp($sourceCharset, $targetCharset) != 0))
        {
            // Check if the reported encoding could have been incorrect and the text is actually already UTF-8
            if ((strcasecmp($targetCharset, 'UTF-8') == 0) && ($this->is_utf8($text)))
            {
                $converted_text = $text;
            }
            else
            {
                $converted_text = iconv($sourceCharset, $targetCharset, $text);
            }
        }

        // Lets make sure that we don't have that silly BOM issue with any of the utf-8 text we output.
        if ($targetCharset == 'UTF-8')
        {
            if (substr($converted_text, 0, 3) == "\xef\xbb\xbf")
            {
                $converted_text = substr($converted_text, 3);
            }
            if (substr($converted_text, -3) == "\xef\xbb\xbf")
            {
                $converted_text = substr($converted_text, 0, -3);
            }
        }

        return $converted_text;
    }

    /**
     * Returns true if $string is valid UTF-8 and false otherwise.
     *
     * @param mixed $str String to be tested
     * @return boolean
     */
    static function is_utf8($str)
    {
        $c=0; $b=0;
        $bits=0;
        $len=strlen($str);
        for($i=0; $i<$len; $i++)
        {
            $c=ord($str[$i]);
            if($c > 128)
            {
                if(($c >= 254)) return false;
                elseif($c >= 252) $bits=6;
                elseif($c >= 248) $bits=5;
                elseif($c >= 240) $bits=4;
                elseif($c >= 224) $bits=3;
                elseif($c >= 192) $bits=2;
                else return false;
                if(($i+$bits) > $len) return false;
                while($bits > 1)
                {
                    $i++;
                    $b=ord($str[$i]);
                    if($b < 128 || $b > 191) return false;
                    $bits--;
                }
            }
        }
        return true;
    }

    function getElementById($id) {return $this->find("#$id", 0);}
    function getElementsById($id, $idx = null) { return $this->find("#$id", $idx); }
    function getElementByTagName($name) {return $this->find($name, 0);}
    function getElementsByTagName($name, $idx=null) {return $this->find($name, $idx);}
}
