<?php
namespace Molly\library\utils\html;

/**
 * @class DOM
 * @author Boris Wintein - <hello@thisisboris.be>;
 * @description
 * This is the DOMDocument class containing the rootnode, and all information about the DOMDocument itself.
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

use \Molly\library\utils\html\exceptions\HTMLStructureException;
use \Molly\library\utils\html\interfaces\DOMConstants;
use \Molly\library\utils\html\interfaces\DOMElement;

use \Molly\library\io\dataloaders\files\File;
use \Molly\library\io\dataloaders\files\FileWriter;

use \Molly\library\exceptions\InvalidConstructorException;

class DOM extends FileWriter implements DOMConstants, DOMElement
{
    /**
     * @var File $file.
     * This contains the file-object we're parsing. This is used so we can easily save, load, cache or do whatever with
     * the parsed content on disk.
     */
    private $file;

    /**
     * @var DOMNode
     * Contains the rootnode for this domdocument. Childnodes reference this.
     */
    public $rootNode;

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
    protected $size;

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
     * @var $defaultBR
     * Contains the default BR text, invalid BR tags are replaced with this.
     */
    private $defaultBR;

    /**
     * @var $defaultSpan
     * Contains the default span content.
     */
    private $defaultSpan;

    /**
     * @var boolean
     * Should we strip linebreaks from the document (default: true)
     */
    private $stripLineBreaks = true;

    /**
     * @var $charset
     * The charset of the document we're parsing. Public because it is referenced in childnodes.
     */
    public $charset;

    /**
     * @var $targetCharset
     * The charset we wish to convert to. Public because it is referenced in childnodes.
     */
    public $targetCharset;


    private $context;

    /**
     * @var bool $parsing
     * Bool to check whether we're currently parsing.
     */
    private $parsing = false;

    /**
     * @param \Molly\library\io\dataloaders\files\File $file
     * @param DOMFactory $factory
     *
     * @throws InvalidConstructorException;
     *
     * This class can only be created by accessing the DOMFactory static functions.
     */
    public function __construct(File &$file, DOMFactory $factory) {
        if (is_null($factory)) {
            throw new InvalidConstructorException("DOMObjects can only be created by the DOMFactory.");
        }

        $this->setFile($file);
    }

    /**
     * @param \Molly\library\io\dataloaders\files\File $file
     * Sets the file property. This is used for saving the generated HTML to a file. Caching made easy!
     */
    private function setFile(File &$file) {
        $this->file = $file;
    }

    /**
     * @param bool $parsing
     * Sets the parsing property.
     */
    protected function setParsing($parsing = true) {
        $this->parsing = $parsing;
    }

    /**
     * @return bool
     * Checks whether we're currently parsing a node.
     */
    public function isParsing() {
        return $this->parsing;
    }

    /**
     * @param $context
     * Sets the context. Only available when not parsing.
     */
    public function setContext($context) {
        if (!$this->isParsing()) {
            $this->context = $context;
        }
    }

    /**
     * @param $targetCharset
     * Sets the targetCharset we should try to convert to. Only available when not parsing.
     */
    public function setTargetCharset($targetCharset) {
        if (!$this->isParsing()) {
            $this->targetCharset = $targetCharset;
        }
    }

    /**
     * @return mixed
     * Gets the target charset.
     */
    public function getTargetCharset() {
        return $this->targetCharset;
    }

    public function setCharset($charset) {
        if (!$this->isParsing()) {
            return $this->charset = $charset;
        } else {
            return false;
        }
    }

    public function getCharset() {
        return $this->charset;
    }

    /**
     * @param $stripRN
     * @return mixed
     * Sets the behaviour for stripping linebreaks. Default is true but can be changed with this function.
     * Only available when not parsing.
     */
    public function setStripLineBreaks($stripRN) {
        if ($this->isParsing()) {
            return $this->stripLineBreaks = $stripRN;
        } else {
            return false;
        }
    }

    /**
     * @param $defaultBRText
     * @return mixed
     * Sets the default BR text. Only available when not parsing.
     */
    public function setDefaultBRtext($defaultBRText) {
        if ($this->isParsing()) {
            return $this->defaultBR = $defaultBRText;
        } else {
            return false;
        }
    }

    /**
     * @param $defaultSpanText
     * @return mixed
     * Sets the default span text. Only available when not parsing.
     */
    public function setDefaultSpanText($defaultSpanText) {
        if ($this->isParsing()) {
            return $this->defaultSpan = $defaultSpanText;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     * @throws exceptions\HTMLStructureException
     *
     * Gets a reference to the rootnode.
     */
    function &getRootNode() {
        if ($this->rootNode === null) {
            throw new HTMLStructureException("Rootnode not set");
        } else {
            return $this->rootNode;
        }
    }

    /**
     * @param DOMNode $node
     * @return DOMNode;
     * Sets the rootnode to a specific DOMNode.
     */
    protected function setRootNode(DOMNode &$node) {
        return $this->rootNode = $node;
    }

    /**
     * @return string
     * Printing or echo'ing this class will call upon this function. It will print the DOMNode to string, which is
     * the same as calling the render function.
     */
    function _toString() {
        return $this->getRootNode()->_toString();
    }

    /**
     * @param $html
     * Sets the content of this domclass to a certain string.
     */
    protected function setContent($html) {
        // Set initial size.
        $this->size = $this->original_size = strlen($html);
        $this->rawHTML = $html;
    }

    /**
     * Starts the parsing of the $rawHTML content.
     */
    public function parse() {
        if (empty($this->file)) {
            throw new HTMLStructureException("Failed to parse: Missing required File-object with content");
        }

        $this->setContent($this->file->getContent());
        $this->load();
    }

    protected $pos;
    protected $doc;
    protected $char;

    protected $noise = array();
    protected $token_blank = " \t\r\n";
    protected $token_equal = ' =/>';
    protected $token_slash = " />\r\n\t";
    protected $token_attr = ' >';

    // Note that this is referenced by a child node, and so it needs to be public for that node to see this information.
    public $_charset = '';
    public $_target_charset = '';

    protected $self_closing_tags = array('img'=>1, 'br'=>1, 'input'=>1, 'meta'=>1, 'link'=>1, 'hr'=>1, 'base'=>1, 'embed'=>1, 'spacer'=>1);
    protected $block_tags = array('root'=>1, 'body'=>1, 'form'=>1, 'div'=>1, 'span'=>1, 'table'=>1);
    protected $optional_closing_tags = array(
        'tr'=>array('tr'=>1, 'td'=>1, 'th'=>1),
        'th'=>array('th'=>1),
        'td'=>array('td'=>1),
        'li'=>array('li'=>1),
        'dt'=>array('dt'=>1, 'dd'=>1),
        'dd'=>array('dd'=>1, 'dt'=>1),
        'dl'=>array('dd'=>1, 'dt'=>1),
        'p'=>array('p'=>1),
        'nobr'=>array('nobr'=>1),
        'b'=>array('b'=>1),
        'option'=>array('option'=>1)
    );

    /**
     * On clearing/deleting this class, we should call the clear function. Since we're using references, our memory is
     * only cleared when all references to the original object are gone. So we need to go through all our nodes and
     * make sure they're properly unset. Memory leaks aren't cool things.
     */
    function __destruct()
    {
        $this->clear();
    }

    // load html from string
    function load()
    {
        // prepare
        $this->prepare();
        // strip out comments
        $this->remove_noise("'<!--(.*?)-->'is");
        // strip out cdata
        $this->remove_noise("'<!\[CDATA\[(.*?)\]\]>'is", true);

        // Per sourceforge http://sourceforge.net/tracker/?func=detail&aid=2949097&group_id=218559&atid=1044037
        // Script tags removal now preceeds style tag removal.
        // strip out <script> tags
        $this->remove_noise("'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is");
        $this->remove_noise("'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is");
        // strip out <style> tags
        $this->remove_noise("'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is");
        $this->remove_noise("'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is");
        // strip out preformatted tags
        $this->remove_noise("'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is");
        // strip out server side scripts
        $this->remove_noise("'(<\?)(.*?)(\?>)'s", true);
        // strip smarty scripts
        $this->remove_noise("'(\{\w)(.*?)(\})'s", true);

        // parsing
        while ($this->parse_html());
        // end
        $this->getRootNode()->addInfo(HDOM_INFO_END, $this->cursor);
        $this->parse_charset();

        // make load function chainable
        return $this;
    }

    // find dom node by css selector
    // Paperg - allow us to specify that we want case insensitive testing of the value of the selector.
    function find($selector, $idx=null, $lowercase=false)
    {
        return $this->getRootNode()->find($selector, $idx, $lowercase);
    }

    // clean up memory due to php5 circular references memory leak...
    function clear()
    {
        foreach ($this->getChildNodes() as $node) {
            if ($node instanceof DOMNode) {
                $node->destroyNode();
                $node = null;
            }
        }

        foreach ($this->getLinkedNodes() as $node) {
            if ($node instanceof DOMNode) {
                $node->destroyNode();
                $node = null;
            }
        }

        unset($this->rootNode);
        unset($this->doc);
        unset($this->noise);
    }

    function dump($show_attr=true)
    {
        return $this->getRootNode()->dump($show_attr);
    }

    // prepare HTML data and init everything
    protected function prepare()
    {
        // Not using the getrootnode, as that may throw an exception.
        if (isset($this->rootNode)) {
            $this->clear();
        }

        //before we save the string as the doc...  strip out the \r \n's if we are told to.
        if ($this->stripLineBreaks) {
            $this->rawHTML = str_replace("\r", " ", $this->rawHTML);
            $this->rawHTML = str_replace("\n", " ", $this->rawHTML);

            // set the length of content since we have changed it.
            $this->size = strlen($this->rawHTML);
        }

        $this->doc = $this->rawHTML;
        $this->pos = 0;
        $this->cursor = 1;
        $this->noise = array();

        $this->setRootNode(new DOMNode($this));
        $this->getRootNode()->setTag('root');
        $this->getRootNode()->addInfo(self::HDOM_INFO_BEGIN, -1);
        $this->getRootNode()->setNodeType(self::HDOM_TYPE_ROOT);

        if ($this->size>0) $this->char = $this->doc[0];
    }

    // parse html content
    protected function parse_html()
    {
        if (($s = $this->copy_until_char('<'))==='')
        {
            return $this->read_tag();
        }

        // text
        $node = new DOMNode($this);
        ++$this->cursor;
        $node->addInfo(self::HDOM_INFO_TEXT, $s);
        $this->link_nodes($node, false);
        return true;
    }

    // PAPERG - dkchou - added this to try to identify the character set of the page we have just parsed so we know better how to spit it out later.
    // NOTE:  IF you provide a routine called get_last_retrieve_url_contents_content_type which returns the CURLINFO_CONTENT_TYPE from the last curl_exec
    // (or the content_type header from the last transfer), we will parse THAT, and if a charset is specified, we will use it over any other mechanism.
    protected function parse_charset()
    {
        $charset = null;

        if (function_exists('get_last_retrieve_url_contents_content_type'))
        {
            $contentTypeHeader = get_last_retrieve_url_contents_content_type();
            $success = preg_match('/charset=(.+)/', $contentTypeHeader, $matches);
            if ($success)
            {
                $charset = $matches[1];
            }

        }

        if (empty($charset))
        {
            $el = $this->getRootNode()->find('meta[http-equiv=Content-Type]',0);
            if (!empty($el))
            {
                $fullvalue = $el->content;

                if (!empty($fullvalue))
                {
                    $success = preg_match('/charset=(.+)/', $fullvalue, $matches);
                    if ($success)
                    {
                        $charset = $matches[1];
                    }
                    else
                    {
                        // If there is a meta tag, and they don't specify the character set, research says that it's typically ISO-8859-1
                        $charset = 'ISO-8859-1';
                    }
                }
            }
        }

        // If we couldn't find a charset above, then lets try to detect one based on the text we got...
        if (empty($charset))
        {
            // Have php try to detect the encoding from the text given to us.
            $charset = mb_detect_encoding($this->getRootNode()->plaintext . "ascii", $encoding_list = array( "UTF-8", "CP1252" ) );
            // and if this doesn't work...  then we need to just wrongheadedly assume it's UTF-8 so that we can move on - cause this will usually give us most of what we need...
            if ($charset === false)
            {
                $charset = 'UTF-8';
            }
        }

        // Since CP1252 is a superset, if we get one of it's subsets, we want it instead.
        if ((strtolower($charset) == strtolower('ISO-8859-1')) || (strtolower($charset) == strtolower('Latin1')) || (strtolower($charset) == strtolower('Latin-1')))
        {
            $charset = 'CP1252';
        }

        return $this->setCharset($charset);
    }

    // read tag info
    protected function read_tag()
    {
        if ($this->char!=='<')
        {
            $this->getRootNode()->addInfo(self::HDOM_INFO_END, $this->cursor);
            return false;
        }

        $begin_tag_pos = $this->pos;
        $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next

        // end tag
        if ($this->char==='/')
        {
            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            // This represents the change in the simple_html_dom trunk from revision 180 to 181.
            // $this->skip($this->token_blank_t);
            $this->skip($this->token_blank);
            $tag = $this->copy_until_char('>');

            // skip attributes in end tag
            if (($pos = strpos($tag, ' '))!==false)
                $tag = substr($tag, 0, $pos);

            $parent_lower = strtolower($this->getParent()->getTag());
            $tag_lower = strtolower($tag);

            if ($parent_lower !== $tag_lower)
            {
                if (isset($this->optional_closing_tags[$parent_lower]) && isset($this->block_tags[$tag_lower]))
                {
                    $this->getParent()->addInfo(self::HDOM_INFO_END, 0);
                    $org_parent = $this->getParent();

                    while (($this->getParent()->getParent()) && strtolower($this->getParent()->getTag())!== $tag_lower)
                        $this->setParent($this->getParent()->getParent());

                    if (strtolower($this->getParent()->getTag())!== $tag_lower) {
                        $this->setParent($org_parent); // restore original parent
                        if ($this->getParent()->getParent()) $this->setParent($this->getParent()->getParent());
                        $this->getParent()->addInfo(self::HDOM_INFO_END, $this->cursor);
                        return $this->as_text_node($tag);
                    }
                }
                else if (($this->getParent()->getParent()) && isset($this->block_tags[$tag_lower]))
                {
                    $this->getParent()->addInfo(self::HDOM_INFO_END, 0);
                    $org_parent = $this->getParent();

                    while (($this->getParent()->getParent()) && strtolower($this->getParent()->getTag()) !== $tag_lower)
                        $this->setParent($this->getParent()->getParent());

                    if (strtolower($this->getParent()->getTag()) !== $tag_lower)
                    {
                        $this->setParent($org_parent); // restore original parent
                        $this->getParent()->addInfo(self::HDOM_INFO_END, $this->cursor);
                        return $this->as_text_node($tag);
                    }
                }
                else if (($this->getParent()->getParent()) && strtolower($this->getParent()->getParent()->getTag() )=== $tag_lower)
                {
                    $this->getParent()->addInfo(self::HDOM_INFO_END, 0);
                    $this->setParent($this->getParent()->getParent());
                }
                else
                    return $this->as_text_node($tag);
            }

            $this->getParent()->addInfo(self::HDOM_INFO_END, $this->cursor);
            if ($this->getParent()->getParent()) $this->setParent($this->getParent()->getParent());

            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            return true;
        }

        $node = new DOMNode($this);
        $node->addInfo(self::HDOM_INFO_BEGIN, $this->cursor);
        ++$this->cursor;
        $tag = $this->copy_until($this->token_slash);
        $node->tag_start = $begin_tag_pos;

        // doctype, cdata & comments...
        if (isset($tag[0]) && $tag[0]==='!') {
            $node->addInfo(self::HDOM_INFO_TEXT, '<' . $tag . $this->copy_until_char('>'));

            if (isset($tag[2]) && $tag[1]==='-' && $tag[2]==='-') {
                $node->setNodeType(self::HDOM_TYPE_COMMENT);
                $node->setTag('comment');
            } else {
                $node->setNodeType(self::HDOM_TYPE_UNKNOWN);
                $node->setTag('unknown');
            }
            if ($this->char==='>') $node->addInfo(self::HDOM_INFO_TEXT, $node->getInfo(self::HDOM_INFO_TEXT) . '>');
            $this->link_nodes($node, true);
            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            return true;
        }

        // text
        if ($pos=strpos($tag, '<')!==false) {
            $tag = '<' . substr($tag, 0, -1);
            $node->addInfo(self::HDOM_INFO_TEXT, $tag);
            $this->link_nodes($node, false);
            $this->char = $this->doc[--$this->pos]; // prev
            return true;
        }

        if (!preg_match("/^[\w-:]+$/", $tag)) {
            $node->addInfo(self::HDOM_INFO_TEXT, '<' . $tag . $this->copy_until('<>'));
            if ($this->char==='<') {
                $this->link_nodes($node, false);
                return true;
            }

            if ($this->char==='>') $node->addInfo(self::HDOM_INFO_TEXT, $node->getInfo(self::HDOM_INFO_TEXT) . '>');
            $this->link_nodes($node, false);
            $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
            return true;
        }

        // begin tag
        $node->setNodeType(self::HDOM_TYPE_ELEMENT);
        $tag = strtolower($tag);
        $node->setTag($tag);

        // handle optional closing tags
        if (isset($this->optional_closing_tags[$tag]) )
        {
            while (isset($this->optional_closing_tags[$tag][strtolower($this->getParent()->getTag())]))
            {
                $this->getParent()->addInfo(self::HDOM_INFO_END, 0);
                $this->setParent($this->getParent()->getParent());
            }
            $node->setParent($this->getParent());
        }

        $guard = 0; // prevent infinity loop
        $space = array($this->copy_skip($this->token_blank), '', '');

        // attributes
        do
        {
            if ($this->char!==null && $space[0]==='')
            {
                break;
            }
            $name = $this->copy_until($this->token_equal);
            if ($guard===$this->pos)
            {
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                continue;
            }
            $guard = $this->pos;

            // handle endless '<'
            if ($this->pos>=$this->size-1 && $this->char!=='>') {
                $node->setNodeType(self::HDOM_TYPE_TEXT);
                $node->addInfo(self::HDOM_INFO_END, 0);
                $node->addInfo(self::HDOM_INFO_TEXT, '<'.$tag . $space[0] . $name);
                $node->setTag('text');
                $this->link_nodes($node, false);
                return true;
            }

            // handle mismatch '<'
            if ($this->doc[$this->pos-1] == '<') {
                $node->setNodeType(self::HDOM_TYPE_TEXT);
                $node->setTag('text');
                $node->addInfo(self::HDOM_INFO_END, 0);
                $node->addInfo(self::HDOM_INFO_TEXT, substr($this->doc, $begin_tag_pos, $this->pos-$begin_tag_pos-1));
                $this->pos -= 2;
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                $this->link_nodes($node, false);
                return true;
            }

            if ($name!=='/' && $name!=='') {
                $space[1] = $this->copy_skip($this->token_blank);
                $name = $this->restore_noise($name);
                if ($this->lowercase) $name = strtolower($name);
                if ($this->char==='=') {
                    $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                    $this->parse_attr($node, $name, $space);
                }
                else {
                    //no value attr: nowrap, checked selected...
                    $node->addInfo(self::HDOM_INFO_QUOTE, array(HDOM_QUOTE_NO));
                    $node->setAttribute($name, true);
                    if ($this->char!='>') $this->char = $this->doc[--$this->pos]; // prev
                }
                $node->addInfo(self::HDOM_INFO_SPACE, $space);
                $space = array($this->copy_skip($this->token_blank), '', '');
            }
            else
                break;
        } while ($this->char!=='>' && $this->char!=='/');

        $this->link_nodes($node, true);
        $node->addInfo(self::HDOM_INFO_ENDSPACE, $space[0]);

        // check self closing
        if ($this->copy_until_char_escape('>')==='/')
        {
            $node->addInfo(self::HDOM_INFO_ENDSPACE, $node->getInfo(self::HDOM_INFO_ENDSPACE) . '/');
            $node->addInfo(self::HDOM_INFO_END, 0);
        }
        else
        {
            // reset parent
            if (!isset($this->self_closing_tags[strtolower($node->getTag())])) $this->setParent($node);
        }

        $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next

        // If it's a BR tag, we need to set it's text to the default text.
        // This way when we see it in plaintext, we can generate formatting that the user wants.
        // since a br tag never has sub nodes, this works well.
        if ($node->getTag() == "br")
        {
            $node->addInfo(self::HDOM_INFO_INNER, $this->defaultBR);
        }

        return true;
    }

    // parse attributes
    protected function parse_attr(DOMNode $node, $name, &$space)
    {
        // Per sourceforge: http://sourceforge.net/tracker/?func=detail&aid=3061408&group_id=218559&atid=1044037
        // If the attribute is already defined inside a tag, only pay attention to the first one as opposed to the last one.
        $atr = $node->getAttribute($name);
        if (isset($atr))
        {
            return;
        }

        $space[2] = $this->copy_skip($this->token_blank);
        switch ($this->char) {
            case '"':
                $node->addInfo(self::HDOM_INFO_QUOTE, self::HDOM_QUOTE_DOUBLE);
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                $node->setAttribute($name, $this->restore_noise($this->copy_until_char_escape('"')));
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                break;
            case '\'':
                $node->addInfo(self::HDOM_INFO_QUOTE, self::HDOM_QUOTE_SINGLE);
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                $node->setAttribute($name, $this->restore_noise($this->copy_until_char_escape('\'')));
                $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
                break;
            default:
                $node->addInfo(HDOM_INFO_QUOTE, HDOM_QUOTE_NO);
                $node->setAttribute($name, $this->restore_noise($this->copy_until($this->token_attr)));
        }
        // PaperG: Attributes should not have \r or \n in them, that counts as html whitespace.
        $node->setAttribute($name, str_replace("\r", "", $node->getAttribute($name)));
        $node->setAttribute($name, str_replace("\n", "", $node->getAttribute($name)));
        // PaperG: If this is a "class" selector, lets get rid of the preceeding and trailing space since some people leave it in the multi class case.
        if ($name == "class") {
            $node->setAttribute($name, trim($node->getAttribute($name)));
        }
    }

    // link node's parent
    protected function link_nodes(DOMNode &$node, $is_child)    {
        if ($is_child) {
            $node->setParent($this->getRootNode());
            $this->addChildNode($node);
        } else {
            // We override this function so that it links the node  to the rootnode
            $this->addLinkedNode($node);
        }
        return $node;
    }

    // as a text node
    protected function as_text_node($tag)
    {
        $node = new DOMNode($this);
        ++$this->cursor;
        $node->addInfo(self::HDOM_INFO_TEXT, '</' . $tag . '>');
        $this->link_nodes($node, false);
        $this->char = (++$this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
        return true;
    }

    protected function skip($chars)
    {
        $this->pos += strspn($this->doc, $chars, $this->pos);
        $this->char = ($this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
    }

    protected function copy_skip($chars)
    {
        $pos = $this->pos;
        $len = strspn($this->doc, $chars, $pos);
        $this->pos += $len;
        $this->char = ($this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
        if ($len===0) return '';
        return substr($this->doc, $pos, $len);
    }

    protected function copy_until($chars)
    {
        $pos = $this->pos;
        $len = strcspn($this->doc, $chars, $pos);
        $this->pos += $len;
        $this->char = ($this->pos<$this->size) ? $this->doc[$this->pos] : null; // next
        return substr($this->doc, $pos, $len);
    }

    protected function copy_until_char($char)
    {
        if ($this->char===null) return '';

        if (($pos = strpos($this->doc, $char, $this->pos))===false) {
            $ret = substr($this->doc, $this->pos, $this->size-$this->pos);
            $this->char = null;
            $this->pos = $this->size;
            return $ret;
        }

        if ($pos===$this->pos) return '';
        $pos_old = $this->pos;
        $this->char = $this->doc[$pos];
        $this->pos = $pos;
        return substr($this->doc, $pos_old, $pos-$pos_old);
    }

    protected function copy_until_char_escape($char)
    {
        if ($this->char===null) return '';

        $start = $this->pos;
        while (1)
        {
            if (($pos = strpos($this->doc, $char, $start))===false)
            {
                $ret = substr($this->doc, $this->pos, $this->size-$this->pos);
                $this->char = null;
                $this->pos = $this->size;
                return $ret;
            }

            if ($pos===$this->pos) return '';

            if ($this->doc[$pos-1]==='\\') {
                $start = $pos+1;
                continue;
            }

            $pos_old = $this->pos;
            $this->char = $this->doc[$pos];
            $this->pos = $pos;
            return substr($this->doc, $pos_old, $pos-$pos_old);
        }
    }

    // remove noise from html content
    // save the noise in the $this->noise array.
    protected function remove_noise($pattern, $remove_tag = false)
    {
        $count = preg_match_all($pattern, $this->doc, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);

        for ($i=$count-1; $i>-1; --$i)
        {
            $key = '___noise___'.sprintf('% 5d', count($this->noise)+1000);
            $idx = ($remove_tag) ? 0 : 1;
            $this->noise[$key] = $matches[$i][$idx][0];
            $this->doc = substr_replace($this->doc, $key, $matches[$i][$idx][1], strlen($matches[$i][$idx][0]));
        }

        // reset the length of content
        $this->size = strlen($this->doc);
        if ($this->size>0)
        {
            $this->char = $this->doc[0];
        }
    }

    // restore noise to html content
    function restore_noise($text)
    {
        while (($pos=strpos($text, '___noise___'))!==false)
        {
            // Sometimes there is a broken piece of markup, and we don't GET the pos+11 etc... token which indicates a problem outside of us...
            if (strlen($text) > $pos+15)
            {
                $key = '___noise___'.$text[$pos+11].$text[$pos+12].$text[$pos+13].$text[$pos+14].$text[$pos+15];
                if (isset($this->noise[$key]))
                {
                    $text = substr($text, 0, $pos).$this->noise[$key].substr($text, $pos+16);
                }
                else
                {
                    // do this to prevent an infinite loop.
                    $text = substr($text, 0, $pos).'UNDEFINED NOISE FOR KEY: '.$key . substr($text, $pos+16);
                }
            }
            else
            {
                // There is no valid key being given back to us... We must get rid of the ___noise___ or we will have a problem.
                $text = substr($text, 0, $pos).'NO NUMERIC NOISE KEY' . substr($text, $pos+11);
            }
        }
        return $text;
    }

    // Sometimes we NEED one of the noise elements.
    function search_noise($text)
    {
        foreach($this->noise as $noiseElement)
        {
            if (strpos($noiseElement, $text)!==false)
            {
                return $noiseElement;
            }
        }

        return false;
    }

    function __toString()
    {
        return (string) $this->getRootNode();
    }

    function __get($name)
    {
        switch ($name)
        {
            case 'outertext':
                return $this->getRootNode()->innertext();
            case 'innertext':
                return $this->getRootNode()->innertext();
            case 'plaintext':
                return $this->getRootNode()->text();
            case 'charset':
                return $this->getCharset();
            case 'target_charset':
                return $this->getTargetCharset();
        }
    }

    // camel naming conventions
    function childNodes($idx=-1) {return ($idx == -1 ? $this->getChildNodes() : $this->getChildNode($idx)); }
    function firstChild() {return $this->getFirstChild();}
    function lastChild() {return $this->getLastChild();}

    function createTextNode($value) {return @end(DOMFactory::constructFromString($value)->getChildNodes());}
    function getElementById($id) {return $this->find("#$id", 0);}
    function getElementsById($id, $idx=null) {return $this->find("#$id", $idx);}
    function getElementByTagName($name) {return $this->find($name, 0);}
    function getElementsByTagName($name, $idx=-1) {return $this->find($name, $idx);}
    function loadFile() {$args = func_get_args();$this->load_file($args);}

    /**
     * @return DOMNode
     */
    function &getParent()
    {
        return $this->getRootNode()->getParent();
    }

    /**
     * @param DOMNode $node
     * @return mixed
     */
    function setParent(DOMNode &$node)
    {
        return $this->getRootNode()->setParent($node);
    }

    /**
     * @param $id
     * @return mixed
     */
    function setChildId($id)
    {
        return $this->getRootNode()->setChildId($id);
    }

    /**
     * @return int
     */
    function getChildId()
    {
        return $this->getRootNode()->getChildId();
    }

    function addChildNode(DOMNode &$node)
    {
        return $this->getRootNode()->addChildNode($node);
    }

    function addLinkedNode(DOMNode &$node) {
        return $this->getRootNode()->addLinkedNode($node);
    }

    function &getLinkedNodes()
    {
        return $this->getRootNode()->getLinkedNodes();
    }

    function removeLinkedNode(DOMNode &$node)
    {
        return $this->getRootNode()->removeLinkedNode($node);
    }

    function removeChildNode(DOMNode &$node)
    {
        return $this->getRootNode()->removeChildNode($node);
    }

    function hasChildNodes()
    {
        return $this->getRootNode()->hasChildNodes();
    }

    function &getChildNodes()
    {
        return $this->getRootNode()->getChildNodes();
    }

    function &getChildNode($id = -1)
    {
        return $this->getRootNode()->getChildNode($id);
    }

    function &getFirstChild()
    {
        return $this->getRootNode()->getFirstChild();
    }

    function &getLastChild()
    {
        return $this->getRootNode()->getLastChild();
    }

    function createElement($tag, $contents)
    {
        // TODO: Implement createElement() method.
    }

    function deleteElement(DOMNode &$node)
    {
        // TODO: Implement deleteElement() method.
    }


}
