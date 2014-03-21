<?php
namespace Lucy\http\html;

/**
 * @class DOM
 * @author Boris Wintein - <hello@thisisboris.be>;
 * @description
 *
 *
 */

use Lucy\events\Event;
use Lucy\exceptions\OverwriteException;
use Lucy\http\html\abstracts\AbstractDOMElement;
use \Lucy\http\html\exceptions\HTMLStructureException;
use \Lucy\http\html\interfaces\DOMElement;

use Lucy\http\html\nodetypes\LinkNode;
use Lucy\http\html\nodetypes\MetaNode;
use \Lucy\io\dataloaders\files\File;

class DOM extends AbstractDOMElement
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

    /*
     * @var Array
     * Contains all the metanodes for easy editing values.
     */
    private $metanodes;

    /**
     * @param \Lucy\io\dataloaders\files\File $file
     * This class can only be created by accessing the DOMFactory static functions.
     */
    public function __construct(File &$file) {
        $this->setFile($file);

        $this->setContent($file->getContent());
    }

    function startParse() {
        // Send out an event to inform everyone we're going to start parsing data
        $this->dispatchEvent(new Event('DOMElement-preload', 'About to start parsing data', $this, $this, self::EVENT_PARSING_START), $this->rawHTML);

        // Set the original size
        $this->original_size = strlen($this->rawHTML);

        // Reset the cursor
        $this->cursor = 0;

        // Starts parsing, false on completion. Throws HTML-exceptions when html is invalid.
        while ($this->parse());

        // Update our internal file
        $this->updateFile();

        // Return this object, so that the parent parser may change his internal cursor.
        return $this;
    }

    /**
     * @param \Lucy\io\dataloaders\files\File $file
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

    function &getLinkNodes() {
        return $this->linknodes;
    }

    function hasLinkNodes() {
        return isset($this->linkNodes) && !empty($this->linkNodes);
    }

    function addLinkNode(LinkNode &$node) {
        $this->linkedNodes[] = $node;
        // Get the <head>-tag so that we can add the meta tag as a child
        if ($headtag = $this->getHeadNode()) {
            if ($headtag->hasChildNode($node) === false) {
                $headtag->addChildNode($node);
            }
        }
    }

    /*
     * @return array
     * Returns an array with all the META-tags as nodes
     */
    function &getMetaNodes() {
        return $this->metanodes;
    }

    function hasMetaNode(&$name) {
        return isset($this->metanodes[$name]) && is_null($this->metanodes[$name]);
    }

    function &getMetaNode($name) {
        if ($this->hasMetaNode($name)) {
            return $this->metanodes[$name];
        } else {
            return false;
        }

    }

    function &addMetaNode(MetaNode &$node, $overwrite = false) {
        if (!$overwrite && $this->hasMetaNode($node->getName())){
            throw new OverwriteException('metanode ' . $node->getName(), $this->getMetaNode($node->getName())->getContent(), $node->getContent());
        } else {
            $this->metanodes[$node->getName()] = $node;
            // Get the <head>-tag so that we can add the meta tag as a child
            if ($headtag = $this->getHeadNode()) {
                if ($headtag->hasChildNode($node) === false) {
                    $headtag->addChildNode($node);
                }
            }
        }

        return $node;
    }

    function &getHeadNode() {
        $children = $this->getChildNodes();
        foreach ($children as $child) {
            if ($child->getTag() === 'head') {
                return $child;
            }
        }

        return false;
    }

    function &getDOMDocument() {
        return $this;
    }

    /**
     * @param DOMElement $node
     * @return DOMNode;
     * Sets the rootnode to a specific DOMNode.
     */
    function &setRootNode(DOMElement &$node) {
        return $this->rootNode = $node;
    }

    /**
     * @return string
     * Printing or echo'ing this class will call upon this function. It will print the DOMNode to string, which is
     * the same as calling the render function.
     */

    function __toString() {
        return $this->getRootNode()->__toString();
    }

    /**
     * @param $html
     * Sets the content of this DOM-file to a certain string.
     */
    public function setContent($html) {
        // Set initial size.
        $this->parse_size = $this->original_size = strlen($html);
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
