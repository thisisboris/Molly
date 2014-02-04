<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\http\html\interfaces;

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
}
