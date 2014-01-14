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

use Molly\library\utils\html\abstracts\AbstractDOMElement;
use \Molly\library\utils\html\exceptions\HTMLStructureException;
use \Molly\library\utils\html\interfaces\DOMElement;

use \Molly\library\io\dataloaders\files\File;

use \Molly\library\exceptions\InvalidConstructorException;

class DOM extends AbstractDOMElement implements DOMElement
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
     * @param \Molly\library\io\dataloaders\files\File $file
     * This class can only be created by accessing the DOMFactory static functions.
     */
    public function __construct(File &$file) {
        $this->setFile($file);

        $this->setContent($file->getContent());
        $this->setRawHTML($file->getContent());
    }

    /**
     * @param \Molly\library\io\dataloaders\files\File $file
     * Sets the file property. This is used for saving the generated HTML to a file. Caching made easy!
     */
    private function setFile(File &$file) {
        $this->file = $file;
    }

    private function updateFile() {
        $this->file->setContent($this->getRootNode()->__toString());
    }

    public function getFile() {
        return $this->file;
    }

    /**
     * @param $targetCharset
     * Sets the targetCharset we should try to convert to.
     */
    public function setTargetCharset($targetCharset) {
        $this->targetCharset = $targetCharset;
    }

    /**
     * @return mixed
     * Gets the target charset.
     */
    public function getTargetCharset() {
        return $this->targetCharset;
    }

    public function setCharset($charset) {
        return $this->charset = $charset;
    }

    public function getCharset() {
        return $this->charset;
    }

    /**
     * @param $stripRN
     * @return mixed
     * Sets the behaviour for stripping linebreaks. Default is true but can be changed with this function.
     */
    public function setStripLineBreaks($stripRN) {
        $this->interrupt();
        return $this->stripLineBreaks = $stripRN;
    }

    /**
     * @param $defaultBRText
     * @return mixed
     * Sets the default BR text.
     */
    public function setDefaultBRtext($defaultBRText) {
        $this->interrupt();
        return $this->defaultBR = $defaultBRText;
    }

    /**
     * @param $defaultSpanText
     * @return mixed
     * Sets the default span text.
     */
    public function setDefaultSpanText($defaultSpanText) {
        $this->interrupt();
        return $this->defaultSpan = $defaultSpanText;
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
    function setRootNode(DOMNode &$node) {
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
     * Sets the content of this DOM-file to a certain string.
     */
    protected function setContent($html) {
        // Set initial size.
        $this->size = $this->original_size = strlen($html);
        $this->setRawHTML($html);
    }

    /**
     * @return DOMNode
     */
    function &getParent()
    {
        return $this->getRootNode()->getParent();
    }

    /**
     * @param DOMElement $node
     * @return mixed
     */
    function setParent(DOMElement &$node)
    {
        return $this->getRootNode()->setParent($node);
    }

    function &getRoot() {
        return $this;
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

    /**
     * @param DOMElement $node
     * @return bool
     */
    function addChildNode(DOMElement &$node)
    {
        return $this->getRootNode()->addChildNode($node);
    }

    /**
     * @param DOMNode $node
     * @return bool|DOMNode
     */
    function addLinkedNode(DOMNode &$node) {
        return $this->getRootNode()->addLinkedNode($node);
    }

    /**
     * @return array
     */
    function &getLinkedNodes()
    {
        return $this->getRootNode()->getLinkedNodes();
    }

    function removeLinkedNode(DOMNode &$node)
    {
        return $this->getRootNode()->removeLinkedNode($node);
    }

    function removeChildNode(DOMElement &$node)
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

    /**
     * @param $element
     * @return mixed
     * Reload the element using the data in $element.
     */
    function reloadElement($element)
    {
        // TODO: Implement reloadElement() method.
    }


}
