<?php
/**
 * This file is part of Molly, an open-source content manager.
 *
 * This application is licensed under the Apache License, found in LICENSE.TXT
 *
 * Molly CMS - Written by Boris Wintein
 */

namespace Molly\library\utils\html\interfaces;

use \Molly\library\utils\html\DOMNode as DOMNode;

interface DOMElement
{
    function dump();

    function createElement($tag, $contents);
    function deleteElement(DOMNode &$node);

    function &getParent();
    function setParent(DOMNode &$node);

    function setChildId($id);
    function getChildId();

    function addChildNode(DOMNode &$node);
    function removeChildNode(DOMNode &$node);

    function hasChildNodes();

    function &getChildNodes();
    function &getChildNode($id = -1);

    function &getFirstChild();
    function &getLastChild();

    function getElementById($id);
    function getElementsById($id, $idx = null);

    function getElementByTagName($name);
    function getElementsByTagName($name, $idx = null);
}
