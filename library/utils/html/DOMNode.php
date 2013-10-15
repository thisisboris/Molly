<?php

namespace Molly\library\utils\html;

/**
 * I cleaned this class up and added a few methods that I found useful. All credit for the the parsing and main
 * methods goes to contributors listed below.
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
     * @return string
     * The __toString() method recreates the node a html-string.
     */
    function __toString() {
        $returnvalue = '<';
        $returnvalue .= $this->tag . " ";

        foreach ($this->attributes as $identifier => $value) {
            $returnvalue .= $identifier . '="';
            if (is_array($value)) {
                foreach ($value as $element) {
                    $returnvalue .= $element . " ";
                }
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
     * Destroy function.
     * @see destroyNode();
     */
    function __destruct() {
        $this->destroyNode();
    }

    /**
     * Destroy the node completely, removes all references and cleans up memory.
     *
     * @return bool
     */
    function destroyNode() {

        return true;
    }

    /**
     * Alias for the toString method.
     * @return string
     */
    function render() {
        return $this->__toString();
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

    function hasNodeID() {
        return $this->hasAttribute("id");
    }

    function getNodeID() {
        return $this->attributes['id'];
    }

    function hasNodeClasses() {
        return isset($this->attributes['class']) && is_array($this->attributes['class']) && count($this->attributes['class']) > 0;
    }

    function getNodeClasses() {
        return $this->attributes['class'];
    }

    function addNodeClass($class) {
        if (is_string($class)) {
            $classes = explode(" ", $class);
            $this->setAttribute('class', $classes);
        } else {
            throw new IllegalArgumentException($class, "String");
        }

    }

    function getNodeClass() {
        return implode(" ", $this->attributes["class"]);
    }

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

    public $tag_start = 0;

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
     * Debug of a single node.
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

    function innerHTML() {
        if ($this->getInfo(HDOM_INFO_INNER) != null) return $this->getInfo(HDOM_INFO_INNER);
        if ($this->getInfo(HDOM_INFO_TEXT) != null) return $this->getInfo(HDOM_INFO_TEXT);

        if (isset($this->_[HDOM_INFO_INNER])) return $this->_[HDOM_INFO_INNER];
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        $ret = '';
        foreach ($this->nodes as $n)
            $ret .= $n->outertext();
        return $ret;
    }

    // get dom node's outer text (with tag)
    function outertext()
    {
        global $debugObject;
        if (is_object($debugObject))
        {
            $text = '';
            if ($this->tag == 'text')
            {
                if (!empty($this->text))
                {
                    $text = " with text: " . $this->text;
                }
            }
            $debugObject->debugLog(1, 'Innertext of tag: ' . $this->tag . $text);
        }

        if ($this->tag==='root') return $this->innertext();

        // trigger callback
        if ($this->dom && $this->dom->callback!==null)
        {
            call_user_func_array($this->dom->callback, array($this));
        }

        if (isset($this->_[HDOM_INFO_OUTER])) return $this->_[HDOM_INFO_OUTER];
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        // render begin tag
        if ($this->dom && $this->dom->nodes[$this->_[HDOM_INFO_BEGIN]])
        {
            $ret = $this->dom->nodes[$this->_[HDOM_INFO_BEGIN]]->makeup();
        } else {
            $ret = "";
        }

        // render inner text
        if (isset($this->_[HDOM_INFO_INNER]))
        {
            // If it's a br tag...  don't return the HDOM_INNER_INFO that we may or may not have added.
            if ($this->tag != "br")
            {
                $ret .= $this->_[HDOM_INFO_INNER];
            }
        } else {
            if ($this->nodes)
            {
                foreach ($this->nodes as $n)
                {
                    $ret .= $this->convert_text($n->outertext());
                }
            }
        }

        // render end tag
        if (isset($this->_[HDOM_INFO_END]) && $this->_[HDOM_INFO_END]!=0)
            $ret .= '</'.$this->tag.'>';
        return $ret;
    }

    // get dom node's plain text
    function text()
    {
        if (isset($this->_[HDOM_INFO_INNER])) return $this->_[HDOM_INFO_INNER];
        switch ($this->nodetype)
        {
            case HDOM_TYPE_TEXT: return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);
            case HDOM_TYPE_COMMENT: return '';
            case HDOM_TYPE_UNKNOWN: return '';
        }
        if (strcasecmp($this->tag, 'script')===0) return '';
        if (strcasecmp($this->tag, 'style')===0) return '';

        $ret = '';
        // In rare cases, (always node type 1 or HDOM_TYPE_ELEMENT - observed for some span tags, and some p tags) $this->nodes is set to NULL.
        // NOTE: This indicates that there is a problem where it's set to NULL without a clear happening.
        // WHY is this happening?
        if (!is_null($this->nodes))
        {
            foreach ($this->nodes as $n)
            {
                $ret .= $this->convert_text($n->text());
            }

            // If this node is a span... add a space at the end of it so multiple spans don't run into each other.  This is plaintext after all.
            if ($this->tag == "span")
            {
                $ret .= $this->dom->default_span_text;
            }


        }
        return $ret;
    }

    function xmltext()
    {
        $ret = $this->innertext();
        $ret = str_ireplace('<![CDATA[', '', $ret);
        $ret = str_replace(']]>', '', $ret);
        return $ret;
    }

    // build node's text with tag
    function makeup()
    {
        // text, comment, unknown
        if (isset($this->_[HDOM_INFO_TEXT])) return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);

        $ret = '<'.$this->tag;
        $i = -1;

        foreach ($this->attr as $key=>$val)
        {
            ++$i;

            // skip removed attribute
            if ($val===null || $val===false)
                continue;

            $ret .= $this->_[HDOM_INFO_SPACE][$i][0];
            //no value attr: nowrap, checked selected...
            if ($val===true)
                $ret .= $key;
            else {
                switch ($this->_[HDOM_INFO_QUOTE][$i])
                {
                    case HDOM_QUOTE_DOUBLE: $quote = '"'; break;
                    case HDOM_QUOTE_SINGLE: $quote = '\''; break;
                    default: $quote = '';
                }
                $ret .= $key.$this->_[HDOM_INFO_SPACE][$i][1].'='.$this->_[HDOM_INFO_SPACE][$i][2].$quote.$val.$quote;
            }
        }
        $ret = $this->dom->restore_noise($ret);
        return $ret . $this->_[HDOM_INFO_ENDSPACE] . '>';
    }

    // find elements by css selector
    //PaperG - added ability for find to lowercase the value of the selector.
    function find($selector, $idx=null, $lowercase=false)
    {
        $selectors = $this->parse_selector($selector);
        if (($count=count($selectors))===0) return array();
        $found_keys = array();

        // find each selector
        for ($c=0; $c<$count; ++$c)
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
                    $n = ($k===-1) ? $this->dom->getRootNode : $this->dom->nodes[$k];
                    //PaperG - Pass this optional parameter on to the seek function.
                    $n->seek($selectors[$c][$l], $ret, $lowercase);
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
            $found[] = $this->dom->nodes[$k];

        // return nth-element or array
        if (is_null($idx)) return $found;
        else if ($idx<0) $idx = count($found) + $idx;
        return (isset($found[$idx])) ? $found[$idx] : null;
    }

    // seek for given conditions
    // PaperG - added parameter to allow for case insensitive testing of the value of a selector.
    protected function seek($selector, &$ret, $lowercase=false)
    {
        global $debugObject;
        if (is_object($debugObject)) { $debugObject->debugLogEntry(1); }

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
                if (is_object($debugObject)) {$debugObject->debugLog(2, "testing node: " . $node->tag . " for attribute: " . $key . $exp . $val . " where nodes value is: " . $nodeKeyValue);}

                //PaperG - If lowercase is set, do a case insensitive test of the value of the selector.
                if ($lowercase) {
                    $check = $this->match($exp, strtolower($val), strtolower($nodeKeyValue));
                } else {
                    $check = $this->match($exp, $val, $nodeKeyValue);
                }
                if (is_object($debugObject)) {$debugObject->debugLog(2, "after match: " . ($check ? "true" : "false"));}

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
        // It's passed by reference so this is actually what this function returns.
        if (is_object($debugObject)) {$debugObject->debugLog(1, "EXIT - ret: ", $ret);}
    }

    protected function match($exp, $pattern, $value) {
        global $debugObject;
        if (is_object($debugObject)) {$debugObject->debugLogEntry(1);}

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
        global $debugObject;
        if (is_object($debugObject)) {$debugObject->debugLogEntry(1);}

        // pattern of CSS selectors, modified from mootools
        // Paperg: Add the colon to the attrbute, so that it properly finds <tag attr:ibute="something" > like google does.
        // Note: if you try to look at this attribute, yo MUST use getAttribute since $dom->x:y will fail the php syntax check.
        // Notice the \[ starting the attbute?  and the @? following?  This implies that an attribute can begin with an @ sign that is not captured.
        // This implies that an html attribute specifier may start with an @ sign that is NOT captured by the expression.
        // farther study is required to determine of this should be documented or removed.
        // $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
        $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
        preg_match_all($pattern, trim($selector_string).' ', $matches, PREG_SET_ORDER);
        if (is_object($debugObject)) {$debugObject->debugLog(2, "Matches Array: ", $matches);}

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
    function convert_text($text)
    {
        global $debugObject;
        if (is_object($debugObject)) {$debugObject->debugLogEntry(1);}

        $converted_text = $text;

        $sourceCharset = "";
        $targetCharset = "";

        if ($this->dom)
        {
            $sourceCharset = strtoupper($this->dom->_charset);
            $targetCharset = strtoupper($this->dom->_target_charset);
        }
        if (is_object($debugObject)) {$debugObject->debugLog(3, "source charset: " . $sourceCharset . " target charaset: " . $targetCharset);}

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
    /*
    function is_utf8($string)
    {
        //this is buggy
        return (utf8_encode(utf8_decode($string)) == $string);
    }
    */

    /**
     * Function to try a few tricks to determine the displayed size of an img on the page.
     * NOTE: This will ONLY work on an IMG tag. Returns FALSE on all other tag types.
     *
     * @author John Schlick
     * @version April 19 2012
     * @return array an array containing the 'height' and 'width' of the image on the page or -1 if we can't figure it out.
     */
    function get_display_size()
    {
        global $debugObject;

        $width = -1;
        $height = -1;

        if ($this->tag !== 'img')
        {
            return false;
        }

        // See if there is aheight or width attribute in the tag itself.
        if (isset($this->attr['width']))
        {
            $width = $this->attr['width'];
        }

        if (isset($this->attr['height']))
        {
            $height = $this->attr['height'];
        }

        // Now look for an inline style.
        if (isset($this->attr['style']))
        {
            // Thanks to user gnarf from stackoverflow for this regular expression.
            $attributes = array();
            preg_match_all("/([\w-]+)\s*:\s*([^;]+)\s*;?/", $this->attr['style'], $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $attributes[$match[1]] = $match[2];
            }

            // If there is a width in the style attributes:
            if (isset($attributes['width']) && $width == -1)
            {
                // check that the last two characters are px (pixels)
                if (strtolower(substr($attributes['width'], -2)) == 'px')
                {
                    $proposed_width = substr($attributes['width'], 0, -2);
                    // Now make sure that it's an integer and not something stupid.
                    if (filter_var($proposed_width, FILTER_VALIDATE_INT))
                    {
                        $width = $proposed_width;
                    }
                }
            }

            // If there is a width in the style attributes:
            if (isset($attributes['height']) && $height == -1)
            {
                // check that the last two characters are px (pixels)
                if (strtolower(substr($attributes['height'], -2)) == 'px')
                {
                    $proposed_height = substr($attributes['height'], 0, -2);
                    // Now make sure that it's an integer and not something stupid.
                    if (filter_var($proposed_height, FILTER_VALIDATE_INT))
                    {
                        $height = $proposed_height;
                    }
                }
            }

        }

        // Future enhancement:
        // Look in the tag to see if there is a class or id specified that has a height or width attribute to it.

        // Far future enhancement
        // Look at all the parent tags of this image to see if they specify a class or id that has an img selector that specifies a height or width
        // Note that in this case, the class or id will have the img subselector for it to apply to the image.

        // ridiculously far future development
        // If the class or id is specified in a SEPARATE css file thats not on the page, go get it and do what we were just doing for the ones on the page.

        $result = array('height' => $height,
            'width' => $width);
        return $result;
    }

    // camel naming conventions



    function getElementById($id) {return $this->find("#$id", 0);}
    function getElementsById($id, $idx=null) {return $this->find("#$id", $idx);}
    function getElementByTagName($name) {return $this->find($name, 0);}
    function getElementsByTagName($name, $idx=null) {return $this->find($name, $idx);}
    function parentNode() {return $this->parent();}
    function childNodes($idx=-1) {return $this->children($idx);}
    function firstChild() {return $this->first_child();}
    function lastChild() {return $this->last_child();}
    function nextSibling() {return $this->next_sibling();}
    function previousSibling() {return $this->prev_sibling();}
    function hasChildNodes() {return $this->has_child();}
    function nodeName() {return $this->tag;}
    function appendChild($node) {$node->parent($this); return $node;}
}
