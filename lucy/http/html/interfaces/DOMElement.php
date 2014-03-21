<?php
/**
 * This file is part of molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * molly CMS - Written by Boris Wintein
 */

namespace Lucy\http\html\interfaces;

use Lucy\http\html\nodetypes\FormNode;

interface DOMElement
{
    function getTag();
    function setTag($tag);

    function getNodeType();
    function setNodeType($nodetype);

    function &getParent();
    function setParent(DOMElement &$node);

    function setChildId($id);
    function getChildId();

    function addChildNode(DOMElement &$node);
    function removeChildNode(DOMElement &$node);

    function hasChildNodes();

    function &getChildNodes();
    function &getChildNode($id = -1);

    function &getFirstChild();
    function &getLastChild();

    function &setRootNode(DOMElement &$node);
    function &getRootNode();
    function &getDomDocument();

    /**
     * @return FormNode
     * Added to make it possible for inputnodes to directly fetch their formnode upon parse.
     */
    function &getForm();
}
